<?php
require_once __DIR__ . '/bootstrap.php';

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    try {
        switch ($action) {
            case 'delete':
                Product::delete($id);
                flash_set('success', 'Product deleted.');
                break;
            case 'duplicate':
                Product::duplicate($id);
                flash_set('success', 'Product duplicated as a draft — edit it to publish.');
                break;
            case 'toggle_active':
                Product::toggleFlag($id, 'is_active');
                break;
            case 'toggle_featured':
                Product::toggleFlag($id, 'is_featured');
                break;
            case 'toggle_new':
                Product::toggleFlag($id, 'is_new_arrival');
                break;
            case 'toggle_popular':
                Product::toggleFlag($id, 'is_popular');
                break;
            case 'update_stock':
                Product::updateStock($id, max(0, (int) ($_POST['stock_quantity'] ?? 0)));
                flash_set('success', 'Stock updated.');
                break;
        }
    } catch (Throwable $e) {
        flash_set('error', 'Something went wrong: ' . $e->getMessage());
    }

    redirect('admin/products.php?' . http_build_query($_GET));
}

$filters = [
    'search'         => trim($_GET['search'] ?? ''),
    'subcategory_id' => $_GET['subcategory_id'] ?? '',
    'is_active'      => $_GET['is_active'] ?? '',
    'low_stock'      => $_GET['low_stock'] ?? '',
];
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = Product::allForAdmin($filters, $page, 20);

$subcategories = Subcategory::allForAdmin();

$activeNav = 'products';
admin_render('products', [
    'result' => $result, 'filters' => $filters, 'subcategories' => $subcategories, 'queryParams' => $_GET,
], 'Products');
