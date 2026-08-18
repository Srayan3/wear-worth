<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

if (!is_post()) {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}
csrf_verify();

$action = $_POST['action'] ?? '';
$productId = (int) ($_POST['product_id'] ?? 0);
$product = $productId ? Product::find($productId) : null;
if (!$product) {
    json_response(['success' => false, 'message' => 'Product not found.'], 404);
}

switch ($action) {
    case 'upload':
        if (empty($_FILES['image'])) {
            json_response(['success' => false, 'message' => 'No file uploaded.']);
        }
        $result = ImageUpload::handleProductImage($_FILES['image']);
        if (!$result['success']) {
            json_response($result);
        }
        $existingCount = count(Product::images($productId));
        $imageId = Product::addImage($productId, $result['path'], $existingCount === 0, $existingCount);
        json_response([
            'success' => true,
            'image'   => ['id' => $imageId, 'url' => product_image_url($result['path']), 'is_primary' => $existingCount === 0],
        ]);
        break;

    case 'delete':
        Product::deleteImage((int) ($_POST['image_id'] ?? 0));
        json_response(['success' => true]);
        break;

    case 'set_primary':
        Product::setPrimaryImage($productId, (int) ($_POST['image_id'] ?? 0));
        json_response(['success' => true]);
        break;

    case 'reorder':
        $ids = array_map('intval', $_POST['image_ids'] ?? []);
        Product::reorderImages($ids);
        json_response(['success' => true]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Unknown action.'], 400);
}
