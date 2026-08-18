<?php

class Order
{
    /**
     * Creates an order from the current server-side cart inside a DB
     * transaction. Every price is re-read from the database at this moment
     * and snapshotted into order_items — nothing from the browser is trusted.
     *
     * @throws RuntimeException on stock conflicts or invalid input
     */
    public static function createFromCart(array $customerData, int $paymentMethodId, int $deliveryZoneId): array
    {
        $db = Database::connect();
        $items = Cart::items();

        if (empty($items)) {
            throw new RuntimeException('Your bag is empty.');
        }

        $paymentMethod = PaymentMethod::findActive($paymentMethodId);
        if (!$paymentMethod) {
            throw new RuntimeException('Please choose a valid payment method.');
        }

        $zone = DeliveryZone::find($deliveryZoneId);
        if (!$zone) {
            throw new RuntimeException('Please choose a valid delivery area.');
        }

        $db->beginTransaction();
        try {
            // Re-validate stock for every line under the transaction to prevent races.
            $subtotal = 0.0;
            $lineItems = [];
            foreach ($items as $item) {
                $stmt = $db->prepare("SELECT * FROM products WHERE id = :id FOR UPDATE");
                $stmt->execute(['id' => $item['product_id']]);
                $product = $stmt->fetch();
                if (!$product || !$product['is_active']) {
                    throw new RuntimeException("\"{$item['name']}\" is no longer available. Please remove it from your bag.");
                }

                $availableStock = (int) $product['stock_quantity'];
                $variationId = null;
                if ($item['size'] || $item['color']) {
                    // Re-fetch matching variation to lock its row too
                    $vStmt = $db->prepare(
                        "SELECT * FROM product_variations WHERE product_id = :pid
                         AND (size_label <=> :size) AND (color_name <=> :color) LIMIT 1 FOR UPDATE"
                    );
                    $vStmt->execute(['pid' => $item['product_id'], 'size' => $item['size'], 'color' => $item['color']]);
                    $variation = $vStmt->fetch();
                    if ($variation) {
                        $variationId = (int) $variation['id'];
                        $availableStock = (int) $variation['stock_quantity'];
                    }
                }

                if ($product['stock_status'] !== 'in_stock' || $availableStock < $item['quantity']) {
                    throw new RuntimeException("\"{$item['name']}\" doesn't have enough stock. Only {$availableStock} left.");
                }

                $lineTotal = $item['unit_price'] * $item['quantity'];
                $subtotal += $lineTotal;

                $variationLabel = trim(implode(' / ', array_filter([$item['size'], $item['color']])));
                $lineItems[] = [
                    'product_id'   => $item['product_id'],
                    'variation_id' => $variationId,
                    'name'         => $item['name'],
                    'variation'    => $variationLabel ?: null,
                    'price'        => $item['unit_price'],
                    'quantity'     => $item['quantity'],
                    'line_total'   => $lineTotal,
                ];
            }

            $deliveryCharge = (float) $zone['charge'];
            $discountAmount = 0.0; // reserved for future coupon support
            $total = $subtotal - $discountAmount + $deliveryCharge;

            $orderNumber = generate_order_number();
            $defaultStatus = OrderStatus::defaultStatus();

            $stmt = $db->prepare(
                "INSERT INTO orders
                    (order_number, customer_id, full_name, phone, email, district, area, address, order_notes,
                     subtotal, discount_amount, delivery_charge, total, payment_method_id, payment_status, status_id)
                 VALUES
                    (:order_number, :customer_id, :full_name, :phone, :email, :district, :area, :address, :notes,
                     :subtotal, :discount, :delivery, :total, :payment_method_id, 'unpaid', :status_id)"
            );
            $stmt->execute([
                'order_number'      => $orderNumber,
                'customer_id'       => $_SESSION['customer_id'] ?? null,
                'full_name'         => $customerData['full_name'],
                'phone'             => $customerData['phone'],
                'email'             => $customerData['email'] ?: null,
                'district'          => $customerData['district'],
                'area'              => $customerData['area'],
                'address'           => $customerData['address'],
                'notes'             => $customerData['order_notes'] ?: null,
                'subtotal'          => $subtotal,
                'discount'          => $discountAmount,
                'delivery'          => $deliveryCharge,
                'total'             => $total,
                'payment_method_id' => $paymentMethodId,
                'status_id'         => $defaultStatus['id'],
            ]);
            $orderId = (int) $db->lastInsertId();

            $itemStmt = $db->prepare(
                "INSERT INTO order_items
                    (order_id, product_id, variation_id, product_name_snapshot, sku_snapshot,
                     variation_label_snapshot, price_snapshot, quantity, line_total)
                 VALUES (:order_id, :product_id, :variation_id, :name, NULL, :variation, :price, :qty, :line_total)"
            );
            foreach ($lineItems as $li) {
                $itemStmt->execute([
                    'order_id'     => $orderId,
                    'product_id'   => $li['product_id'],
                    'variation_id' => $li['variation_id'],
                    'name'         => $li['name'],
                    'variation'    => $li['variation'],
                    'price'        => $li['price'],
                    'qty'          => $li['quantity'],
                    'line_total'   => $li['line_total'],
                ]);
                Product::decrementStock($db, $li['product_id'], $li['variation_id'], $li['quantity']);
            }

            $db->prepare(
                "INSERT INTO order_status_history (order_id, status_id, note, changed_by) VALUES (:oid, :sid, 'Order placed', 'System')"
            )->execute(['oid' => $orderId, 'sid' => $defaultStatus['id']]);

            $db->commit();

            Cart::clear();

            return ['id' => $orderId, 'order_number' => $orderNumber, 'total' => $total];
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function findByNumber(string $orderNumber): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT o.*, pm.name AS payment_method_name, os.name AS status_name, os.color AS status_color
             FROM orders o
             JOIN payment_methods pm ON pm.id = o.payment_method_id
             JOIN order_statuses os ON os.id = o.status_id
             WHERE o.order_number = :num LIMIT 1"
        );
        $stmt->execute(['num' => $orderNumber]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        $order['items'] = self::items((int) $order['id']);
        return $order;
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT o.*, pm.name AS payment_method_name, os.name AS status_name, os.color AS status_color
             FROM orders o
             JOIN payment_methods pm ON pm.id = o.payment_method_id
             JOIN order_statuses os ON os.id = o.status_id
             WHERE o.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        $order['items'] = self::items($id);
        $order['history'] = self::history($id);
        return $order;
    }

    public static function items(int $orderId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :oid ORDER BY id ASC");
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function history(int $orderId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT h.*, os.name AS status_name, os.color AS status_color
             FROM order_status_history h JOIN order_statuses os ON os.id = h.status_id
             WHERE h.order_id = :oid ORDER BY h.created_at ASC"
        );
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll();
    }

    public static function byCustomer(int $customerId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT o.*, os.name AS status_name, os.color AS status_color
             FROM orders o JOIN order_statuses os ON os.id = o.status_id
             WHERE o.customer_id = :cid ORDER BY o.created_at DESC"
        );
        $stmt->execute(['cid' => $customerId]);
        return $stmt->fetchAll();
    }

    public static function byPhone(string $phone): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT o.*, os.name AS status_name, os.color AS status_color
             FROM orders o JOIN order_statuses os ON os.id = o.status_id
             WHERE o.phone = :phone ORDER BY o.created_at DESC"
        );
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetchAll();
    }

    // ---- Admin ----------------------------------------------------

    public static function allForAdmin(array $filters, int $page = 1, int $perPage = 20): array
    {
        $db = Database::connect();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status_id'])) {
            $where[] = "o.status_id = :status_id";
            $params['status_id'] = $filters['status_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE :search OR o.phone LIKE :search OR o.full_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(o.created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(o.created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $db->prepare("SELECT COUNT(*) FROM orders o WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT o.*, os.name AS status_name, os.color AS status_color, pm.name AS payment_method_name
             FROM orders o
             JOIN order_statuses os ON os.id = o.status_id
             JOIN payment_methods pm ON pm.id = o.payment_method_id
             WHERE {$whereSql}
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page,
            'per_page' => $perPage, 'total_pages' => $totalPages,
        ];
    }

    public static function updateStatus(int $orderId, int $statusId, ?string $note, string $changedBy): void
    {
        $db = Database::connect();
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE orders SET status_id = :sid WHERE id = :id")->execute(['sid' => $statusId, 'id' => $orderId]);
            $db->prepare(
                "INSERT INTO order_status_history (order_id, status_id, note, changed_by) VALUES (:oid, :sid, :note, :by)"
            )->execute(['oid' => $orderId, 'sid' => $statusId, 'note' => $note ?: null, 'by' => $changedBy]);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function updatePaymentStatus(int $orderId, string $status): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE orders SET payment_status = :status WHERE id = :id")->execute(['status' => $status, 'id' => $orderId]);
    }

    public static function updateAdminNotes(int $orderId, string $notes): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE orders SET admin_notes = :notes WHERE id = :id")->execute(['notes' => $notes, 'id' => $orderId]);
    }

    // ---- Dashboard stats ----------------------------------------------------

    public static function dashboardStats(): array
    {
        $db = Database::connect();

        $counts = $db->query(
            "SELECT os.slug, COUNT(o.id) AS c FROM order_statuses os
             LEFT JOIN orders o ON o.status_id = os.id
             GROUP BY os.id, os.slug"
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $totalOrders = (int) $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalSales = (float) $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status = 'paid' OR payment_status = 'unpaid'")->fetchColumn();
        $todaySales = (float) $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $monthSales = (float) $db->query(
            "SELECT COALESCE(SUM(total),0) FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"
        )->fetchColumn();
        $totalProducts = (int) $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $lowStock = (int) $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5 AND stock_status = 'in_stock'")->fetchColumn();

        $recentOrders = $db->query(
            "SELECT o.*, os.name AS status_name, os.color AS status_color
             FROM orders o JOIN order_statuses os ON os.id = o.status_id
             ORDER BY o.created_at DESC LIMIT 8"
        )->fetchAll();

        $salesLast7Days = $db->query(
            "SELECT DATE(created_at) AS d, COALESCE(SUM(total),0) AS total
             FROM orders WHERE created_at >= (CURDATE() - INTERVAL 6 DAY)
             GROUP BY DATE(created_at) ORDER BY d ASC"
        )->fetchAll();

        return compact('counts', 'totalOrders', 'totalSales', 'todaySales', 'monthSales', 'totalProducts', 'lowStock', 'recentOrders', 'salesLast7Days');
    }
}
