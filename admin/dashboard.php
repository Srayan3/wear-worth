<?php
require_once __DIR__ . '/bootstrap.php';

$stats = Order::dashboardStats();
$activeNav = 'dashboard';
$pageTitle = 'Dashboard';

admin_render('dashboard', compact('stats'), $pageTitle);
