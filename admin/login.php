<?php
require_once __DIR__ . '/../core/bootstrap.php';

if (Auth::check()) {
    redirect('admin/dashboard.php');
}

if (is_post()) {
    csrf_verify();
    $result = Auth::attempt(clean_str($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''));
    if ($result['success']) {
        redirect('admin/dashboard.php');
    }
    flash_set('error', $result['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Sign In — <?= e(setting('store_name', 'Atelier')) ?></title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= admin_asset('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="a-login-wrap">
    <div class="a-login-card">
        <div class="brand"><?= e(setting('store_name', 'Atelier')) ?></div>
        <div class="sub">Admin Panel</div>
        <?php foreach (flash_get_all() as $type => $messages): foreach ($messages as $msg): ?>
            <div class="a-flash a-flash--<?= e($type) ?>"><?= e($msg) ?></div>
        <?php endforeach; endforeach; ?>
        <form method="post" action="<?= admin_url('login.php') ?>">
            <?= csrf_field() ?>
            <div class="a-field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="a-input" required autofocus>
            </div>
            <div class="a-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="a-input" required>
            </div>
            <button type="submit" class="a-btn a-btn-primary" style="width:100%; justify-content:center; padding:12px;">Sign In</button>
        </form>
    </div>
</div>
</body>
</html>
