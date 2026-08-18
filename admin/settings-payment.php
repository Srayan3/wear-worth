<?php
require_once __DIR__ . '/bootstrap.php';

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    try {
        switch ($action) {
            case 'update':
                PaymentMethod::update((int) $_POST['id'], [
                    'name' => clean_str($_POST['name'] ?? ''),
                    'account_number' => clean_str($_POST['account_number'] ?? ''),
                    'instructions' => clean_str($_POST['instructions'] ?? ''),
                    'requires_reference' => isset($_POST['requires_reference']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success', 'Payment method updated.');
                break;
            case 'create':
                $code = strtolower(preg_replace('/[^a-z0-9]+/i', '_', clean_str($_POST['name'] ?? 'method')));
                PaymentMethod::create([
                    'code' => $code . '_' . substr(bin2hex(random_bytes(2)), 0, 4),
                    'name' => clean_str($_POST['name'] ?? ''),
                    'account_number' => clean_str($_POST['account_number'] ?? ''),
                    'instructions' => clean_str($_POST['instructions'] ?? ''),
                    'requires_reference' => isset($_POST['requires_reference']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'sort_order' => 99,
                ]);
                flash_set('success', 'Payment method added.');
                break;
        }
    } catch (Throwable $e) {
        flash_set('error', $e->getMessage() ?: 'Something went wrong.');
    }
    redirect('admin/settings-payment.php');
}

$methods = PaymentMethod::allForAdmin();
$activeNav = 'payment';
admin_render('settings-payment', compact('methods'), 'Payment Methods');
