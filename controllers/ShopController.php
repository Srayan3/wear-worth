<?php

class ShopController
{
    public static function index(): void
    {
        self::renderListing([], 'Shop All');
    }

    public static function category(string $slug): void
    {
        $category = Category::findBySlug($slug);
        if (!$category) {
            ErrorController::notFound();
            return;
        }
        self::renderListing(['category' => $slug], $category['name'], $category);
    }

    public static function subcategory(string $categorySlug, string $subSlug): void
    {
        $category = Category::findBySlug($categorySlug);
        $subcategory = Subcategory::findBySlug($subSlug);
        if (!$category || !$subcategory || (int) $subcategory['category_id'] !== (int) $category['id']) {
            ErrorController::notFound();
            return;
        }
        self::renderListing(
            ['category' => $categorySlug, 'subcategory' => $subSlug],
            $subcategory['name'],
            $category,
            $subcategory
        );
    }

    public static function search(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        self::renderListing(['search' => $q], $q !== '' ? 'Search: "' . $q . '"' : 'Search');
    }

    private static function renderListing(array $baseFilters, string $heading, ?array $category = null, ?array $subcategory = null): void
    {
        $filters = array_merge($baseFilters, [
            'search'        => $_GET['q'] ?? ($baseFilters['search'] ?? null),
            'min_price'     => $_GET['min_price'] ?? null,
            'max_price'     => $_GET['max_price'] ?? null,
            'sort'          => $_GET['sort'] ?? 'newest',
            'in_stock_only' => !empty($_GET['in_stock']),
        ]);
        // allow ?category= / ?subcategory= query overrides on the /shop page itself
        if (!empty($_GET['category']) && empty($baseFilters['category'])) {
            $filters['category'] = $_GET['category'];
        }
        if (!empty($_GET['subcategory']) && empty($baseFilters['subcategory'])) {
            $filters['subcategory'] = $_GET['subcategory'];
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = Product::search($filters, $page, 12);
        [$priceMin, $priceMax] = Product::priceRange();

        render('shop', [
            'heading'      => $heading,
            'products'     => $result['items'],
            'pagination'   => $result,
            'filters'      => $filters,
            'categories'   => Category::withSubcategories(),
            'currentCategory'    => $category,
            'currentSubcategory' => $subcategory,
            'priceMin'     => $priceMin,
            'priceMax'     => $priceMax,
        ], [
            'title'       => $heading . ' — ' . setting('store_name', 'Atelier'),
            'description' => setting('meta_default_description', ''),
        ]);
    }
}
