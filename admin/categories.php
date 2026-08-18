<?php
require_once __DIR__ . '/bootstrap.php';

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'create_gender':
                $db = Database::connect();
                $name = clean_str($_POST['name'] ?? '');
                if ($name === '') throw new RuntimeException('Gender name is required.');
                $db->prepare("INSERT INTO genders (name, slug, sort_order) VALUES (:name, :slug, 0)")
                    ->execute(['name' => $name, 'slug' => unique_slug($db, 'genders', $name)]);
                flash_set('success', 'Gender added.');
                break;

            case 'create_category':
                $name = clean_str($_POST['name'] ?? '');
                if ($name === '') throw new RuntimeException('Category name is required.');
                $imagePath = null;
                if (!empty($_FILES['image']['name'])) {
                    $result = ImageUpload::handleProductImage($_FILES['image']);
                    if (!$result['success']) throw new RuntimeException($result['message']);
                    $imagePath = $result['path'];
                }
                Category::create([
                    'gender_id' => (int) $_POST['gender_id'],
                    'name' => $name,
                    'description' => clean_str($_POST['description'] ?? ''),
                    'image' => $imagePath,
                    'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success', 'Category created.');
                break;

            case 'update_category':
                $name = clean_str($_POST['name'] ?? '');
                if ($name === '') throw new RuntimeException('Category name is required.');
                $imagePath = null;
                if (!empty($_FILES['image']['name'])) {
                    $result = ImageUpload::handleProductImage($_FILES['image']);
                    if (!$result['success']) throw new RuntimeException($result['message']);
                    $imagePath = $result['path'];
                }
                Category::update((int) $_POST['id'], [
                    'gender_id' => (int) $_POST['gender_id'],
                    'name' => $name,
                    'description' => clean_str($_POST['description'] ?? ''),
                    'image' => $imagePath,
                    'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success', 'Category updated.');
                break;

            case 'delete_category':
                Category::delete((int) $_POST['id']);
                flash_set('success', 'Category deleted.');
                break;

            case 'toggle_category':
                Category::toggleActive((int) $_POST['id']);
                flash_set('success', 'Category status updated.');
                break;

            case 'create_subcategory':
                $name = clean_str($_POST['name'] ?? '');
                if ($name === '') throw new RuntimeException('Subcategory name is required.');
                Subcategory::create([
                    'category_id' => (int) $_POST['category_id'],
                    'name' => $name,
                    'description' => clean_str($_POST['description'] ?? ''),
                    'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success', 'Subcategory created.');
                break;

            case 'update_subcategory':
                $name = clean_str($_POST['name'] ?? '');
                if ($name === '') throw new RuntimeException('Subcategory name is required.');
                Subcategory::update((int) $_POST['id'], [
                    'category_id' => (int) $_POST['category_id'],
                    'name' => $name,
                    'description' => clean_str($_POST['description'] ?? ''),
                    'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success', 'Subcategory updated.');
                break;

            case 'delete_subcategory':
                Subcategory::delete((int) $_POST['id']);
                flash_set('success', 'Subcategory deleted.');
                break;

            case 'toggle_subcategory':
                Subcategory::toggleActive((int) $_POST['id']);
                flash_set('success', 'Subcategory status updated.');
                break;
        }
    } catch (Throwable $e) {
        flash_set('error', $e->getMessage() ?: 'Something went wrong.');
    }

    redirect('admin/categories.php');
}

$db = Database::connect();
$genders = $db->query("SELECT * FROM genders ORDER BY sort_order ASC, name ASC")->fetchAll();
$categories = Category::allForAdmin();
$subcategoriesByCategory = [];
foreach (Subcategory::allForAdmin() as $sub) {
    $subcategoriesByCategory[$sub['category_id']][] = $sub;
}

$activeNav = 'categories';
admin_render('categories', compact('genders', 'categories', 'subcategoriesByCategory'), 'Categories');
