<?php
require_once __DIR__ . '/bootstrap.php';

$filters = [
    'status_id' => $_GET['status_id'] ?? '',
    'search'    => trim($_GET['search'] ?? ''),
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? '',
];
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = Order::allForAdmin($filters, $page, 25);
$statuses = OrderStatus::all();

$activeNav = 'orders';
admin_render('orders', ['result' => $result, 'filters' => $filters, 'statuses' => $statuses, 'queryParams' => $_GET], 'Orders');
