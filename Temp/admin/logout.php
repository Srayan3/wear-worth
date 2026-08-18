<?php
require_once __DIR__ . '/../core/bootstrap.php';

if (is_post()) {
    csrf_verify();
}
Auth::logout();
redirect('admin/login.php');
