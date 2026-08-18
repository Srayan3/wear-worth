<?php
require_once __DIR__ . '/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$order = Order::find($id);
if (!$order) {
    flash_set('error', 'Order not found.');
    redirect('admin/orders.php');
}

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'update_status':
                Order::updateStatus($id, (int) $_POST['status_id'], clean_str($_POST['note'] ?? ''), Auth::name());
                flash_set('success', 'Order status updated.');
                break;
            case 'update_payment_status':
                Order::updatePaymentStatus($id, in_array($_POST['payment_status'], ['unpaid', 'paid', 'refunded'], true) ? $_POST['payment_status'] : 'unpaid');
                flash_set('success', 'Payment status updated.');
                break;
            case 'update_notes':
                Order::updateAdminNotes($id, clean_str($_POST['admin_notes'] ?? ''));
                flash_set('success', 'Internal notes saved.');
                break;
        }
    } catch (Throwable $e) {
        flash_set('error', 'Something went wrong: ' . $e->getMessage());
    }

    redirect('admin/order-detail.php?id=' . $id);
}

$statuses = OrderStatus::all();
$activeNav = 'orders';
admin_render('order-detail', compact('order', 'statuses'), 'Order ' . $order['order_number']);
