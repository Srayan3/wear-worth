<?php
require_once __DIR__ . '/bootstrap.php';

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    try {
        switch ($action) {
            case 'create':
                OrderStatus::create(['name' => clean_str($_POST['name'] ?? ''), 'color' => $_POST['color'] ?? '#0A0A0A', 'sort_order' => (int) ($_POST['sort_order'] ?? 99)]);
                flash_set('success', 'Status added.');
                break;
            case 'update':
                OrderStatus::update((int) $_POST['id'], ['name' => clean_str($_POST['name'] ?? ''), 'color' => $_POST['color'] ?? '#0A0A0A', 'sort_order' => (int) ($_POST['sort_order'] ?? 0)]);
                flash_set('success', 'Status updated.');
                break;
            case 'delete':
                OrderStatus::delete((int) $_POST['id']);
                flash_set('success', 'Status removed.');
                break;
        }
    } catch (Throwable $e) {
        flash_set('error', 'Could not complete that action — this status may still be in use by existing orders.');
    }
    redirect('admin/settings-statuses.php');
}

$statuses = OrderStatus::all();
$activeNav = 'statuses';
admin_render('settings-statuses', compact('statuses'), 'Order Statuses');
