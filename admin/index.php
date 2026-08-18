<?php
require_once __DIR__ . '/../core/bootstrap.php';
redirect(Auth::check() ? 'admin/dashboard.php' : 'admin/login.php');
