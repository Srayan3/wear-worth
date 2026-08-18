<?php

class AccountController
{
    public static function dashboard(): void
    {
        if (!CustomerAuth::check()) {
            redirect('/account/login');
        }
        redirect('/account/orders');
    }

    public static function loginForm(): void
    {
        if (CustomerAuth::check()) {
            redirect('/account/orders');
        }
        render('account/login', [], ['title' => 'Sign In — ' . setting('store_name', 'Atelier')]);
    }

    public static function login(): void
    {
        csrf_verify();
        $phone = clean_str($_POST['phone'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $customer = Customer::attemptLogin($phone, $password);
        if (!$customer) {
            flash_set('error', 'Incorrect phone number or password.');
            redirect('/account/login');
        }
        CustomerAuth::login($customer);
        flash_set('success', 'Welcome back, ' . $customer['full_name'] . '.');
        redirect('/account/orders');
    }

    public static function registerForm(): void
    {
        if (CustomerAuth::check()) {
            redirect('/account/orders');
        }
        render('account/register', [], ['title' => 'Create Account — ' . setting('store_name', 'Atelier')]);
    }

    public static function register(): void
    {
        csrf_verify();
        $fullName = clean_str($_POST['full_name'] ?? '');
        $phone = clean_str($_POST['phone'] ?? '');
        $email = clean_str($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $errors = [];
        if ($fullName === '') $errors[] = 'Full name is required.';
        if (!preg_match('/^[0-9+ ]{9,17}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

        if ($errors) {
            flash_set('error', implode(' ', $errors));
            redirect('/account/register');
        }

        $result = Customer::register($fullName, $phone, $email ?: null, $password);
        if (!$result['success']) {
            flash_set('error', $result['message']);
            redirect('/account/register');
        }

        $customer = Customer::find($result['id']);
        CustomerAuth::login($customer);
        flash_set('success', 'Account created — welcome!');
        redirect('/account/orders');
    }

    public static function logout(): void
    {
        csrf_verify();
        CustomerAuth::logout();
        redirect('/');
    }

    public static function orders(): void
    {
        if (!CustomerAuth::check()) {
            redirect('/account/login');
        }
        render('account/orders', [
            'orders' => Order::byCustomer((int) CustomerAuth::id()),
        ], ['title' => 'My Orders — ' . setting('store_name', 'Atelier')]);
    }

    public static function orderDetail(string $orderNumber): void
    {
        $order = Order::findByNumber($orderNumber);
        if (!$order) {
            ErrorController::notFound();
            return;
        }
        // Only the owning customer (or a matching guest session) may view it.
        if ($order['customer_id'] && (int) $order['customer_id'] !== (int) CustomerAuth::id()) {
            ErrorController::notFound();
            return;
        }
        render('account/order-detail', ['order' => $order], ['title' => 'Order ' . $order['order_number']]);
    }

    public static function trackOrderForm(): void
    {
        render('account/track-order', [], ['title' => 'Track Your Order — ' . setting('store_name', 'Atelier')]);
    }

    public static function trackOrder(): void
    {
        csrf_verify();
        $phone = clean_str($_POST['phone'] ?? '');
        $orderNumber = clean_str($_POST['order_number'] ?? '');

        if ($orderNumber !== '') {
            $order = Order::findByNumber($orderNumber);
            if ($order && $order['phone'] === $phone) {
                redirect('/account/orders/' . $order['order_number']);
            }
        }
        $orders = $phone !== '' ? Order::byPhone($phone) : [];
        render('account/track-order', ['orders' => $orders, 'searched' => true], ['title' => 'Track Your Order']);
    }
}
