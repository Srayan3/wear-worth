<?php
require_once __DIR__ . '/bootstrap.php';
Auth::requireRole('super_admin');

$db = Database::connect();

if (is_post()) {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $name = clean_str($_POST['name'] ?? '');
            $username = clean_str($_POST['username'] ?? '');
            $email = clean_str($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $role = in_array($_POST['role'] ?? '', ['super_admin', 'manager', 'staff'], true) ? $_POST['role'] : 'staff';

            if ($name === '' || $username === '' || $email === '') throw new RuntimeException('All fields are required.');
            if (strlen($password) < 8) throw new RuntimeException('Password must be at least 8 characters.');

            $stmt = $db->prepare("INSERT INTO admins (name, username, email, password_hash, role) VALUES (:name, :u, :e, :p, :r)");
            $stmt->execute(['name' => $name, 'u' => $username, 'e' => $email, 'p' => password_hash($password, PASSWORD_DEFAULT), 'r' => $role]);
            flash_set('success', 'Admin user created.');
        }

        if ($action === 'toggle') {
            $id = (int) $_POST['id'];
            if ($id === Auth::id()) throw new RuntimeException("You can't deactivate your own account.");
            $db->prepare("UPDATE admins SET is_active = NOT is_active WHERE id = :id")->execute(['id' => $id]);
            flash_set('success', 'Admin status updated.');
        }
    } catch (Throwable $e) {
        flash_set('error', $e->getMessage() ?: 'Something went wrong.');
    }

    redirect('admin/admins.php');
}

$admins = $db->query("SELECT * FROM admins ORDER BY created_at ASC")->fetchAll();
$activeNav = 'admins';
admin_render('admins', compact('admins'), 'Admin Users');
