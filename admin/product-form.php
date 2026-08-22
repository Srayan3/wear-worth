<?php
require_once __DIR__ . '/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = $id ? Product::find($id) : null;
if ($id && !$product) {
    flash_set('error', 'Product not found.');
    redirect('admin/products.php');
}

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? 'save_basic';

    try {
        if ($action === 'save_basic') {
            $data = [
                'subcategory_id'    => (int) $_POST['subcategory_id'],
                'name'              => clean_str($_POST['name'] ?? ''),
                'sku'               => clean_str($_POST['sku'] ?? ''),
                'short_description' => clean_str($_POST['short_description'] ?? ''),
                'description'       => trim(strip_tags($_POST['description'] ?? '', '<p><br><ul><li><ol><strong><em>')),
                'price'             => (float) ($_POST['price'] ?? 0),
                'discount_price'    => $_POST['discount_price'] !== '' ? (float) $_POST['discount_price'] : null,
                'stock_quantity'    => max(0, (int) ($_POST['stock_quantity'] ?? 0)),
                'stock_status'      => in_array($_POST['stock_status'] ?? '', ['in_stock', 'out_of_stock'], true) ? $_POST['stock_status'] : 'in_stock',
                'has_variations'    => isset($_POST['has_variations']) ? 1 : 0,
                'is_featured'       => isset($_POST['is_featured']) ? 1 : 0,
                'is_new_arrival'    => isset($_POST['is_new_arrival']) ? 1 : 0,
                'is_popular'        => isset($_POST['is_popular']) ? 1 : 0,
                'is_active'         => isset($_POST['is_active']) ? 1 : 0,
            ];

            if ($data['name'] === '') throw new RuntimeException('Product name is required.');
            if ($data['sku'] === '') throw new RuntimeException('SKU is required.');
            if ($data['price'] <= 0) throw new RuntimeException('Price must be greater than zero.');
            if ($data['discount_price'] !== null && $data['discount_price'] >= $data['price']) {
                throw new RuntimeException('Discount price must be lower than the regular price.');
            }

            if ($id) {
                Product::update($id, $data);
                flash_set('success', 'Product updated.');
                redirect('admin/product-form.php?id=' . $id);
            }
            $newId = Product::create($data);
            flash_set('success', 'Product created — now add images and variations below.');
            redirect('admin/product-form.php?id=' . $newId);
        }

        if ($action === 'save_variations' && $id) {
            $rows = [];
            $sizes = $_POST['var_size'] ?? [];
            $colors = $_POST['var_color'] ?? [];
            $hexes = $_POST['var_hex'] ?? [];
            $skus = $_POST['var_sku'] ?? [];
            $prices = $_POST['var_price'] ?? [];
            $qtys = $_POST['var_qty'] ?? [];
            for ($i = 0; $i < count($sizes); $i++) {
                $rows[] = [
                    'size' => clean_str($sizes[$i] ?? ''), 'color' => clean_str($colors[$i] ?? ''),
                    'hex' => clean_str($hexes[$i] ?? ''), 'sku' => clean_str($skus[$i] ?? ''),
                    'price' => $prices[$i] ?? '', 'qty' => $qtys[$i] ?? 0, 'image' => null,
                ];
            }
            Product::replaceVariations($id, $rows);
            flash_set('success', 'Variations saved.');
            redirect('admin/product-form.php?id=' . $id . '#variations');
        }

        if ($action === 'save_size_chart' && $id) {
            $type = in_array($_POST['size_chart_type'] ?? '', ['clothing', 'footwear'], true) ? $_POST['size_chart_type'] : 'clothing';
            $rows = [];
            if ($type === 'footwear') {
                $sizeLabels = $_POST['sc_brand_size'] ?? [];
                for ($i = 0; $i < count($sizeLabels); $i++) {
                    $rows[] = [
                        'size' => clean_str($sizeLabels[$i] ?? ''),
                        'uk'   => clean_str($_POST['sc_uk'][$i] ?? ''),
                        'eu'   => clean_str($_POST['sc_eu'][$i] ?? ''),
                        'us'   => clean_str($_POST['sc_us'][$i] ?? ''),
                    ];
                }
            } else {
                $sizeLabels = $_POST['sc_size'] ?? [];
                for ($i = 0; $i < count($sizeLabels); $i++) {
                    $rows[] = [
                        'size'   => clean_str($sizeLabels[$i] ?? ''),
                        'chest'  => $_POST['sc_chest'][$i] ?? '',
                        'waist'  => $_POST['sc_waist'][$i] ?? '',
                        'hip'    => $_POST['sc_hip'][$i] ?? '',
                        'length' => $_POST['sc_length'][$i] ?? '',
                    ];
                }
            }
            Product::updateSizeChartType($id, $type);
            Product::replaceSizeChart($id, $rows);
            flash_set('success', 'Size chart saved.');
            redirect('admin/product-form.php?id=' . $id . '#sizechart');
        }
    } catch (Throwable $e) {
        flash_set('error', $e->getMessage() ?: 'Something went wrong.');
        redirect('admin/product-form.php' . ($id ? '?id=' . $id : ''));
    }
}

$subcategories = Subcategory::allForAdmin();
$images = $id ? Product::images($id) : [];
$variations = $id ? Product::variations($id) : [];
$sizeChart = $id ? Product::sizeChart($id) : [];

$activeNav = 'products';
admin_render('product-form', compact('product', 'subcategories', 'images', 'variations', 'sizeChart', 'id'), $id ? 'Edit Product' : 'Add Product');
