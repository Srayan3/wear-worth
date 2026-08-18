<?php
require_once __DIR__ . '/bootstrap.php';

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    try {
        switch ($action) {
            case 'create':
                DeliveryZone::create([
                    'name' => clean_str($_POST['name'] ?? ''), 'charge' => (float) ($_POST['charge'] ?? 0),
                    'is_default' => isset($_POST['is_default']) ? 1 : 0, 'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success', 'Delivery zone added.');
                break;
            case 'update':
                DeliveryZone::update((int) $_POST['id'], [
                    'name' => clean_str($_POST['name'] ?? ''), 'charge' => (float) ($_POST['charge'] ?? 0),
                    'is_default' => isset($_POST['is_default']) ? 1 : 0, 'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success', 'Delivery zone updated.');
                break;
            case 'delete':
                DeliveryZone::delete((int) $_POST['id']);
                flash_set('success', 'Delivery zone removed.');
                break;
        }
    } catch (Throwable $e) {
        flash_set('error', $e->getMessage() ?: 'Something went wrong.');
    }
    redirect('admin/settings-delivery.php');
}

$zones = DeliveryZone::allForAdmin();
$activeNav = 'delivery';
admin_render('settings-delivery', compact('zones'), 'Delivery Zones');
