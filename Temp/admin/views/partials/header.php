<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Dashboard') ?> — Admin</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= admin_asset('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar__brand"><?= e(setting('store_name', 'Atelier')) ?><small>Admin Panel</small></div>

        <nav>
            <div class="admin-nav-group">
                <a href="<?= admin_url('dashboard.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'dashboard' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                    Dashboard
                </a>
            </div>
            <div class="admin-nav-group">
                <h6>Catalog</h6>
                <a href="<?= admin_url('categories.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'categories' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    Categories
                </a>
                <a href="<?= admin_url('products.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'products' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                    Products
                </a>
            </div>
            <div class="admin-nav-group">
                <h6>Sales</h6>
                <a href="<?= admin_url('orders.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'orders' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M4 4h16l-1.5 13a2 2 0 0 1-2 1.8H7.5a2 2 0 0 1-2-1.8L4 4z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/></svg>
                    Orders
                </a>
                <a href="<?= admin_url('customers.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'customers' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6.5 6.5-6.5s6.5 2.9 6.5 6.5"/><path d="M16 8.5a3 3 0 1 1 3.5 3M17 13.5c2 .3 3.5 2 3.5 4"/></svg>
                    Customers
                </a>
            </div>
            <div class="admin-nav-group">
                <h6>Configuration</h6>
                <a href="<?= admin_url('settings.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'settings' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
                    Store Settings
                </a>
                <a href="<?= admin_url('settings-delivery.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'delivery' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><rect x="2" y="7" width="15" height="12" rx="1"/><path d="M17 10h3l2 3v6h-5"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
                    Delivery Zones
                </a>
                <a href="<?= admin_url('settings-payment.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'payment' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                    Payment Methods
                </a>
                <a href="<?= admin_url('settings-statuses.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'statuses' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Order Statuses
                </a>
                <?php if (($_SESSION['admin_role'] ?? '') === 'super_admin'): ?>
                <a href="<?= admin_url('admins.php') ?>" class="admin-nav-link <?= ($activeNav ?? '') === 'admins' ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                    Admin Users
                </a>
                <?php endif; ?>
            </div>
            <div class="admin-nav-group">
                <a href="<?= url('') ?>" target="_blank" class="admin-nav-link">
                    <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14L21 3"/></svg>
                    View Storefront
                </a>
                <form method="post" action="<?= admin_url('logout.php') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="admin-nav-link" style="width:100%; text-align:left; background:none; border:none; cursor:pointer;">
                        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex; align-items:center; gap:12px;">
                <button type="button" class="a-btn a-btn-outline admin-mobile-toggle" id="adminSidebarToggle">☰</button>
                <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
            </div>
            <div class="admin-topbar__right">
                <div class="admin-user-chip">
                    <div class="avatar"><?= e(strtoupper(substr(Auth::name(), 0, 1))) ?></div>
                    <span><?= e(Auth::name()) ?></span>
                </div>
            </div>
        </div>
        <div class="admin-content">
            <?php foreach (flash_get_all() as $type => $messages): foreach ($messages as $msg): ?>
                <div class="a-flash a-flash--<?= e($type) ?>"><?= e($msg) ?></div>
            <?php endforeach; endforeach; ?>
