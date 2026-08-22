<?php

class CheckoutController
{
    public static function show(): void
    {
        $items = Cart::items();
        if (empty($items)) {
            flash_set('error', 'Your bag is empty — add something you love first.');
            redirect('/shop');
        }

        render('checkout', [
            'items'          => $items,
            'subtotal'       => Cart::subtotal(),
            'deliveryZones'  => DeliveryZone::allActive(),
            'paymentMethods' => PaymentMethod::allActive(),
        ], ['title' => 'Checkout — ' . setting('store_name', 'Atelier')]);
    }

    public static function submit(): void
    {
        csrf_verify();

        $fullName = clean_str($_POST['full_name'] ?? '');
        $phone = clean_str($_POST['phone'] ?? '');
        $email = clean_str($_POST['email'] ?? '');
        $district = clean_str($_POST['district'] ?? '');
        $area = clean_str($_POST['area'] ?? '');
        $address = clean_str($_POST['address'] ?? '');
        $notes = clean_str($_POST['order_notes'] ?? '');
        $paymentMethodId = (int) ($_POST['payment_method_id'] ?? 0);
        $deliveryZoneId = (int) ($_POST['delivery_zone_id'] ?? 0);
        $paymentTxnId = clean_str($_POST['payment_transaction_id'] ?? '');

        $errors = [];
        if ($fullName === '') $errors[] = 'Full name is required.';
        if (!preg_match('/^[0-9+ ]{9,17}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if ($district === '') $errors[] = 'District is required.';
        if ($area === '') $errors[] = 'Area is required.';
        if ($address === '') $errors[] = 'Delivery address is required.';
        if (!$paymentMethodId) $errors[] = 'Please choose a payment method.';
        if (!$deliveryZoneId) $errors[] = 'Please choose a delivery area.';

        $method = $paymentMethodId ? PaymentMethod::findActive($paymentMethodId) : null;
        if ($method && $method['requires_reference'] && $paymentTxnId === '') {
            $errors[] = 'Please enter your ' . $method['name'] . ' transaction ID.';
        }

        if ($errors) {
            $_SESSION['old_input'] = $_POST;
            flash_set('error', implode(' ', $errors));
            redirect('/checkout');
        }

        try {
            $order = Order::createFromCart(
                [
                    'full_name' => $fullName,
                    'phone'     => $phone,
                    'email'     => $email,
                    'district'  => $district,
                    'area'      => $area,
                    'address'   => $address,
                    'order_notes' => $notes !== '' ? ($notes . ($paymentTxnId ? " | Txn ID: {$paymentTxnId}" : '')) : ($paymentTxnId ? "Txn ID: {$paymentTxnId}" : ''),
                ],
                $paymentMethodId,
                $deliveryZoneId
            );
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
            redirect('/checkout');
        } catch (Throwable $e) {
            error_log('[CHECKOUT ERROR] ' . $e->getMessage());
            $message = 'Something went wrong placing your order. Please try again.';
            // In local/dev environments (APP_DEBUG = true in config.php), surface the
            // real error so it's actually possible to diagnose — never in production.
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $message .= ' [DEBUG: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine() . ']';
            }
            flash_set('error', $message);
            redirect('/checkout');
        }

        unset($_SESSION['old_input']);
        redirect('/order/success/' . $order['order_number']);
    }

    public static function success(string $orderNumber): void
    {
        $order = Order::findByNumber($orderNumber);
        if (!$order) {
            ErrorController::notFound();
            return;
        }
        render('order-success', ['order' => $order], ['title' => 'Order Confirmed — ' . setting('store_name', 'Atelier')]);
    }
}
