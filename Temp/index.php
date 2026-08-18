<?php
/**
 * Storefront front controller. All non-static requests are rewritten
 * here by .htaccess. Routes are matched top-to-bottom by regex.
 */

require_once __DIR__ . '/core/bootstrap.php';

$path = current_path();
$path = $path === '/' ? '/' : rtrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// [method, regex, handler]  — named capture groups are passed as controller args in order
$routes = [
    ['GET',  '#^/$#',                                            [HomeController::class, 'index']],
    ['GET',  '#^/shop$#',                                        [ShopController::class, 'index']],
    ['GET',  '#^/search$#',                                      [ShopController::class, 'search']],
    ['GET',  '#^/category/([a-z0-9\-]+)/([a-z0-9\-]+)$#',        [ShopController::class, 'subcategory']],
    ['GET',  '#^/category/([a-z0-9\-]+)$#',                      [ShopController::class, 'category']],
    ['GET',  '#^/product/([a-z0-9\-]+)$#',                       [ProductController::class, 'show']],

    ['GET',  '#^/cart$#',                                        [CartController::class, 'view']],
    ['GET',  '#^/cart/items$#',                                  [CartController::class, 'items']],
    ['POST', '#^/cart/add$#',                                    [CartController::class, 'add']],
    ['POST', '#^/cart/update$#',                                 [CartController::class, 'update']],
    ['POST', '#^/cart/remove$#',                                 [CartController::class, 'remove']],

    ['GET',  '#^/checkout$#',                                    [CheckoutController::class, 'show']],
    ['POST', '#^/checkout$#',                                    [CheckoutController::class, 'submit']],
    ['GET',  '#^/order/success/([A-Za-z0-9]+)$#',                [CheckoutController::class, 'success']],

    ['GET',  '#^/account$#',                                     [AccountController::class, 'dashboard']],
    ['GET',  '#^/account/login$#',                                [AccountController::class, 'loginForm']],
    ['POST', '#^/account/login$#',                                [AccountController::class, 'login']],
    ['GET',  '#^/account/register$#',                             [AccountController::class, 'registerForm']],
    ['POST', '#^/account/register$#',                             [AccountController::class, 'register']],
    ['POST', '#^/account/logout$#',                               [AccountController::class, 'logout']],
    ['GET',  '#^/account/orders$#',                               [AccountController::class, 'orders']],
    ['GET',  '#^/account/orders/([A-Za-z0-9]+)$#',                [AccountController::class, 'orderDetail']],
    ['GET',  '#^/track-order$#',                                  [AccountController::class, 'trackOrderForm']],
    ['POST', '#^/track-order$#',                                  [AccountController::class, 'trackOrder']],

    ['GET',  '#^/about$#',                                       [PageController::class, 'about']],
    ['GET',  '#^/contact$#',                                     [PageController::class, 'contact']],
    ['POST', '#^/newsletter$#',                                  [PageController::class, 'newsletter']],

    ['GET',  '#^/sitemap\.xml$#',                                [SitemapController::class, 'generate']],
    ['GET',  '#^/robots\.txt$#',                                 [SitemapController::class, 'robots']],
];

foreach ($routes as [$routeMethod, $pattern, $handler]) {
    if ($routeMethod !== $method) {
        continue;
    }
    if (preg_match($pattern, $path, $matches)) {
        array_shift($matches);
        call_user_func_array($handler, $matches);
        exit;
    }
}

ErrorController::notFound();
