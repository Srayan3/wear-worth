<?php

/**
 * Server-authoritative shopping cart. Prices and stock are always
 * re-read from the database — nothing sent by the browser is trusted.
 */
class Cart
{
    private static ?int $cartId = null;

    public static function id(): int
    {
        if (self::$cartId !== null) {
            return self::$cartId;
        }

        $db = Database::connect();
        $customerId = $_SESSION['customer_id'] ?? null;
        $sessionId = session_id();

        if ($customerId) {
            $stmt = $db->prepare("SELECT id FROM carts WHERE customer_id = :cid LIMIT 1");
            $stmt->execute(['cid' => $customerId]);
            $row = $stmt->fetch();
            if ($row) {
                self::$cartId = (int) $row['id'];
                self::mergeGuestCartIfAny($db, $customerId, $sessionId);
                return self::$cartId;
            }
        }

        $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = :sid AND customer_id IS NULL LIMIT 1");
        $stmt->execute(['sid' => $sessionId]);
        $row = $stmt->fetch();
        if ($row) {
            self::$cartId = (int) $row['id'];
            return self::$cartId;
        }

        $stmt = $db->prepare("INSERT INTO carts (customer_id, session_id) VALUES (:cid, :sid)");
        $stmt->execute(['cid' => $customerId, 'sid' => $customerId ? null : $sessionId]);
        self::$cartId = (int) $db->lastInsertId();
        return self::$cartId;
    }

    /** When a guest logs in, fold their session cart into their account cart. */
    private static function mergeGuestCartIfAny(PDO $db, int $customerId, string $sessionId): void
    {
        $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = :sid AND customer_id IS NULL LIMIT 1");
        $stmt->execute(['sid' => $sessionId]);
        $guestCart = $stmt->fetch();
        if (!$guestCart) {
            return;
        }
        $items = $db->prepare("SELECT * FROM cart_items WHERE cart_id = :cid");
        $items->execute(['cid' => $guestCart['id']]);
        foreach ($items->fetchAll() as $item) {
            self::addItemToCart(self::$cartId, (int) $item['product_id'], $item['variation_id'] ? (int) $item['variation_id'] : null, (int) $item['quantity']);
        }
        $db->prepare("DELETE FROM carts WHERE id = :id")->execute(['id' => $guestCart['id']]);
    }

    /**
     * Add an item, clamping quantity to available stock. Returns a result
     * array so the controller can show a clear message either way.
     */
    public static function add(int $productId, ?int $variationId, int $qty): array
    {
        return self::addItemToCart(self::id(), $productId, $variationId, $qty);
    }

    private static function addItemToCart(int $cartId, int $productId, ?int $variationId, int $qty): array
    {
        $db = Database::connect();
        $product = Product::find($productId);
        if (!$product || !$product['is_active']) {
            return ['success' => false, 'message' => 'This product is no longer available.'];
        }

        $availableStock = $product['stock_quantity'];
        if ($variationId) {
            $variation = Product::findVariation($variationId);
            if (!$variation || (int) $variation['product_id'] !== $productId) {
                return ['success' => false, 'message' => 'Please choose a valid size/color.'];
            }
            $availableStock = $variation['stock_quantity'];
        } elseif ($product['has_variations'] && !empty(Product::variations($productId))) {
            // Only actually require a selection when the product has real,
            // active variation rows to choose from — a product flagged as
            // "has variations" with none saved yet must stay purchasable as
            // a simple product rather than becoming impossible to buy.
            return ['success' => false, 'message' => 'Please select a size/color before adding to cart.'];
        }

        if ($product['stock_status'] !== 'in_stock' || $availableStock <= 0) {
            return ['success' => false, 'message' => 'Sorry, this item is out of stock.'];
        }

        $stmt = $db->prepare(
            "SELECT id, quantity FROM cart_items WHERE cart_id = :cid AND product_id = :pid AND
             (variation_id <=> :vid) LIMIT 1"
        );
        $stmt->execute(['cid' => $cartId, 'pid' => $productId, 'vid' => $variationId]);
        $existing = $stmt->fetch();

        $newQty = ($existing ? (int) $existing['quantity'] : 0) + $qty;
        $newQty = min($newQty, $availableStock);

        if ($existing) {
            $db->prepare("UPDATE cart_items SET quantity = :q WHERE id = :id")
                ->execute(['q' => $newQty, 'id' => $existing['id']]);
        } else {
            $db->prepare(
                "INSERT INTO cart_items (cart_id, product_id, variation_id, quantity) VALUES (:cid, :pid, :vid, :q)"
            )->execute(['cid' => $cartId, 'pid' => $productId, 'vid' => $variationId, 'q' => $newQty]);
        }

        $clamped = $newQty < (($existing['quantity'] ?? 0) + $qty);
        return [
            'success' => true,
            'message' => $clamped ? "Only {$availableStock} left in stock — added what's available." : 'Added to your bag.',
            'count'   => self::count(),
        ];
    }

    public static function updateQuantity(int $itemId, int $qty): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT ci.*, p.stock_quantity AS product_stock, p.is_active
             FROM cart_items ci JOIN products p ON p.id = ci.product_id
             WHERE ci.id = :id AND ci.cart_id = :cid LIMIT 1"
        );
        $stmt->execute(['id' => $itemId, 'cid' => self::id()]);
        $item = $stmt->fetch();
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found in your bag.'];
        }

        $availableStock = (int) $item['product_stock'];
        if ($item['variation_id']) {
            $variation = Product::findVariation((int) $item['variation_id']);
            $availableStock = $variation ? (int) $variation['stock_quantity'] : 0;
        }

        if ($qty <= 0) {
            $db->prepare("DELETE FROM cart_items WHERE id = :id")->execute(['id' => $itemId]);
            return ['success' => true, 'removed' => true, 'message' => 'Removed from your bag.'];
        }

        $qty = min($qty, max(0, $availableStock));
        if ($qty <= 0) {
            $db->prepare("DELETE FROM cart_items WHERE id = :id")->execute(['id' => $itemId]);
            return ['success' => true, 'removed' => true, 'message' => 'That item just sold out and was removed.'];
        }

        $db->prepare("UPDATE cart_items SET quantity = :q WHERE id = :id")->execute(['q' => $qty, 'id' => $itemId]);
        return ['success' => true, 'message' => 'Updated.', 'quantity' => $qty];
    }

    public static function remove(int $itemId): void
    {
        $db = Database::connect();
        $db->prepare("DELETE FROM cart_items WHERE id = :id AND cart_id = :cid")
            ->execute(['id' => $itemId, 'cid' => self::id()]);
    }

    /**
     * Full cart contents with live, server-computed prices. This is the
     * ONLY source of truth for cart totals — never trust client totals.
     */
    public static function items(): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT ci.id AS cart_item_id, ci.quantity, ci.product_id, ci.variation_id,
                    p.name, p.slug, p.price, p.discount_price, p.stock_quantity AS product_stock,
                    p.stock_status, p.is_active,
                    v.size_label, v.color_name, v.color_hex, v.price_override, v.stock_quantity AS variation_stock,
                    (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id
                        ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS image
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             LEFT JOIN product_variations v ON v.id = ci.variation_id
             WHERE ci.cart_id = :cid
             ORDER BY ci.id ASC"
        );
        $stmt->execute(['cid' => self::id()]);
        $rows = $stmt->fetchAll();

        $items = [];
        foreach ($rows as $row) {
            $unitPrice = $row['variation_id'] && $row['price_override'] !== null
                ? (float) $row['price_override']
                : (float) ($row['discount_price'] ?? $row['price']);
            $stock = $row['variation_id'] ? (int) $row['variation_stock'] : (int) $row['product_stock'];

            $items[] = [
                'cart_item_id' => (int) $row['cart_item_id'],
                'product_id'   => (int) $row['product_id'],
                'name'         => $row['name'],
                'slug'         => $row['slug'],
                'image'        => $row['image'],
                'size'         => $row['size_label'],
                'color'        => $row['color_name'],
                'color_hex'    => $row['color_hex'],
                'unit_price'   => $unitPrice,
                'quantity'     => (int) $row['quantity'],
                'line_total'   => $unitPrice * (int) $row['quantity'],
                'stock'        => $stock,
                'available'    => $row['is_active'] && $stock > 0,
            ];
        }
        return $items;
    }

    public static function subtotal(): float
    {
        return array_sum(array_column(self::items(), 'line_total'));
    }

    public static function count(): int
    {
        return (int) array_sum(array_column(self::items(), 'quantity'));
    }

    public static function clear(): void
    {
        $db = Database::connect();
        $db->prepare("DELETE FROM cart_items WHERE cart_id = :cid")->execute(['cid' => self::id()]);
    }
}
