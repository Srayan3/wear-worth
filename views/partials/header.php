<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>

<?php if (setting('store_favicon')): ?>
<link rel="icon" href="<?= e(url(setting('store_favicon'))) ?>">
<?php endif; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= asset('assets/css/tokens.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/layout.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/shop.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/product-cart-checkout.css') ?>">
</head>
<body>

<?php if (setting('promo_heading')): ?>
<div class="promo-banner"><?= e(setting('promo_heading')) ?></div>
<?php endif; ?>

<header class="site-header">
    <div class="site-header__bar"></div>
    <div class="container nav-inner">
        <a href="<?= url('') ?>" class="brand"><?= e(setting('store_name', 'Atelier')) ?></a>

        <nav aria-label="Main">
            <ul class="nav-links">
                <?php foreach (Category::withSubcategories() as $cat): ?>
                    <li><a href="<?= url('category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="<?= url('shop?sort=newest') ?>">New Arrivals</a></li>
                <li><a href="<?= url('about') ?>">About</a></li>
                <li><a href="<?= url('contact') ?>">Contact</a></li>
            </ul>
        </nav>

        <div class="nav-actions">
            <a href="<?= url('search') ?>" class="icon-btn" aria-label="Search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </a>
            <a href="<?= url(CustomerAuth::check() ? 'account/orders' : 'account/login') ?>" class="icon-btn" aria-label="Account">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
            </a>
            <button type="button" class="icon-btn" aria-label="Open bag" data-cart-open>
                <svg viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                <span class="cart-count" style="display:<?= Cart::count() > 0 ? 'flex' : 'none' ?>"><?= (int) Cart::count() ?></span>
            </a>
            <button type="button" class="icon-btn nav-toggle" id="navToggle" aria-label="Open menu">
                <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>
        </div>
    </div>
</header>

<!-- Mobile nav drawer -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer__scrim"></div>
    <div class="mobile-drawer__panel">
        <div class="mobile-drawer__head">
            <span class="brand" style="font-size:18px;"><?= e(setting('store_name', 'Atelier')) ?></span>
            <button type="button" class="icon-btn" id="mobileDrawerClose" aria-label="Close menu">
                <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <ul class="mobile-drawer__links">
            <?php foreach (Category::withSubcategories() as $cat): ?>
                <li>
                    <details>
                        <summary><?= e($cat['name']) ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" fill="none"><path d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <ul class="mobile-drawer__sublist">
                            <li><a href="<?= url('category/' . $cat['slug']) ?>">All <?= e($cat['name']) ?></a></li>
                            <?php foreach ($cat['subcategories'] as $sub): ?>
                                <li><a href="<?= url('category/' . $cat['slug'] . '/' . $sub['slug']) ?>"><?= e($sub['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                </li>
            <?php endforeach; ?>
            <li><a href="<?= url('shop?sort=newest') ?>">New Arrivals</a></li>
            <li><a href="<?= url('about') ?>">About</a></li>
            <li><a href="<?= url('contact') ?>">Contact</a></li>
            <li><a href="<?= url(CustomerAuth::check() ? 'account/orders' : 'account/login') ?>">Account</a></li>
        </ul>
    </div>
</div>

<!-- Mini-cart drawer -->
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer__scrim" data-cart-close></div>
    <div class="cart-drawer__panel">
        <div class="cart-drawer__head">
            <strong>Your Bag</strong>
            <button type="button" class="icon-btn" data-cart-close aria-label="Close bag">
                <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="cart-drawer__items" id="cartDrawerItems"></div>
        <div class="cart-drawer__foot">
            <div class="cart-drawer__subtotal"><span>Subtotal</span><span id="cartDrawerSubtotal"><?= money(Cart::subtotal()) ?></span></div>
            <a href="<?= url('checkout') ?>" class="btn btn-primary btn-block">Checkout</a>
            <a href="<?= url('cart') ?>" class="btn btn-ghost btn-block" style="margin-top:6px;">View Bag</a>
        </div>
    </div>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
</div>

<div class="flash-stack" id="flashStack">
    <?php foreach (flash_get_all() as $type => $messages): foreach ($messages as $msg): ?>
        <div class="flash flash--<?= e($type) ?>"><?= e($msg) ?></div>
    <?php endforeach; endforeach; ?>
</div>

<script>window.SITE_BASE_URL = <?= json_encode(url('')) ?>;</script>

<main>
