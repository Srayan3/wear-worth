<?php

class CartController
{
    public static function view(): void
    {
        render('cart', [
            'items'    => Cart::items(),
            'subtotal' => Cart::subtotal(),
        ], ['title' => 'Your Bag — ' . setting('store_name', 'Atelier')]);
    }

    /** GET /cart/items — JSON, used to render the mini-cart drawer via AJAX. */
    public static function items(): void
    {
        $items = Cart::items();
        json_response([
            'success'  => true,
            'items'    => array_map(function ($i) {
                $i['image'] = product_image_url($i['image']);
                $i['unit_price_formatted'] = money($i['unit_price']);
                $i['line_total_formatted'] = money($i['line_total']);
                return $i;
            }, $items),
            'subtotal' => money(Cart::subtotal()),
            'count'    => Cart::count(),
        ]);
    }


    /** POST /cart/add — AJAX or standard form fallback. */
    public static function add(): void
    {
        csrf_verify();
        $productId = (int) ($_POST['product_id'] ?? 0);
        $variationId = !empty($_POST['variation_id']) ? (int) $_POST['variation_id'] : null;
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));

        if (!$productId) {
            self::respond(['success' => false, 'message' => 'Invalid product.']);
            return;
        }

        $result = Cart::add($productId, $variationId, $qty);
        $result['cart_count'] = Cart::count();
        self::respond($result);
    }

    /** POST /cart/update */
    public static function update(): void
    {
        csrf_verify();
        $itemId = (int) ($_POST['cart_item_id'] ?? 0);
        $qty = (int) ($_POST['quantity'] ?? 0);
        $result = Cart::updateQuantity($itemId, $qty);
        $result['cart_count'] = Cart::count();
        $result['subtotal'] = money(Cart::subtotal());
        self::respond($result);
    }

    /** POST /cart/remove */
    public static function remove(): void
    {
        csrf_verify();
        $itemId = (int) ($_POST['cart_item_id'] ?? 0);
        Cart::remove($itemId);
        self::respond([
            'success' => true, 'message' => 'Removed from your bag.',
            'cart_count' => Cart::count(), 'subtotal' => money(Cart::subtotal()),
        ]);
    }

    private static function respond(array $data): void
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'json')) {
            json_response($data);
        }
        if (!empty($data['message'])) {
            flash_set($data['success'] ?? true ? 'success' : 'error', $data['message']);
        }
        redirect('/cart');
    }
}
