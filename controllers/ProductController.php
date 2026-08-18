<?php

class ProductController
{
    public static function show(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            ErrorController::notFound();
            return;
        }

        Product::incrementViews((int) $product['id']);
        $related = Product::related((int) $product['subcategory_id'], (int) $product['id'], 4);

        render('product', [
            'product' => $product,
            'related' => $related,
            'breadcrumbs' => [
                ['label' => $product['category_name'], 'url' => '/category/' . $product['category_slug']],
                ['label' => $product['subcategory_name'], 'url' => '/category/' . $product['category_slug'] . '/' . $product['subcategory_slug']],
                ['label' => $product['name'], 'url' => null],
            ],
        ], [
            'title'       => $product['name'] . ' — ' . setting('store_name', 'Atelier'),
            'description' => $product['short_description'] ?: setting('meta_default_description', ''),
        ]);
    }
}
