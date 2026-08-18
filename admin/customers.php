<?php
require_once __DIR__ . '/bootstrap.php';

$search = trim($_GET['search'] ?? '');
$registered = Customer::allForAdmin($search);
$guests = Customer::guestSummaries($search);

$activeNav = 'customers';
admin_render('customers', compact('registered', 'guests', 'search'), 'Customers');
