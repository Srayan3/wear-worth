<?php

class HomeController
{
    public static function index(): void
    {
        $data = [
            'categories'    => Category::withSubcategories(),
            'featured'      => Product::homepageSection('featured', 8),
            'newArrivals'   => Product::homepageSection('new_arrival', 8),
            'popular'       => Product::homepageSection('popular', 8),
        ];

        render('home', $data, [
            'title'       => setting('meta_default_title', 'Atelier'),
            'description' => setting('meta_default_description', ''),
        ]);
    }
}
