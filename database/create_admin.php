<?php
/**
 * Creates an admin account with a securely hashed password.
 *
 * Run from the command line (recommended, and the only option by default —
 * the /database folder is blocked from web access by .htaccess):
 *   php database/create_admin.php
 *
 * If your host only gives you a browser (no SSH), temporarily copy this
 * file to the project root, load it in your browser, submit the form
 * once, then DELETE the copy immediately — it has no login protection.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function prompt(string $label, bool $hidden = false): string
{
    if (PHP_SAPI !== 'cli') {
        return trim((string) ($_POST[$label] ?? ($_GET[$label] ?? '')));
    }
    echo $label . ': ';
    if ($hidden && stripos(PHP_OS, 'WIN') !== 0) {
        system('stty -echo');
        $value = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $value;
    }
    return trim(fgets(STDIN));
}

$isCli = PHP_SAPI === 'cli';

if (!$isCli && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Create Admin</title>';
    echo '<body style="font-family:sans-serif; max-width:420px; margin:60px auto;">';
    echo '<h2>Create Admin Account</h2>';
    echo '<p style="color:#a3372e;"><strong>Delete this file after use.</strong> It has no login protection.</p>';
    echo '<form method="post">';
    foreach (['name' => 'Full Name', 'username' => 'Username', 'email' => 'Email', 'password' => 'Password'] as $f => $label) {
        $type = $f === 'password' ? 'password' : ($f === 'email' ? 'email' : 'text');
        echo "<p><label>{$label}<br><input style='width:100%;padding:8px;' type='{$type}' name='{$f}' required></label></p>";
    }
    echo '<button type="submit" style="padding:10px 20px;">Create Admin</button></form></body>';
    exit;
}

$name = prompt('name');
$username = prompt('username');
$email = prompt('email');
$password = prompt('password', true);

if (strlen($password) < 8) {
    die("Error: password must be at least 8 characters.\n");
}

$db = Database::connect();
$stmt = $db->prepare("SELECT id FROM admins WHERE username = :u OR email = :e LIMIT 1");
$stmt->execute(['u' => $username, 'e' => $email]);
if ($stmt->fetch()) {
    die("Error: an admin with that username or email already exists.\n");
}

$stmt = $db->prepare(
    "INSERT INTO admins (name, username, email, password_hash, role) VALUES (:name, :username, :email, :hash, 'super_admin')"
);
$stmt->execute([
    'name' => $name, 'username' => $username, 'email' => $email,
    'hash' => password_hash($password, PASSWORD_DEFAULT),
]);

$message = "Admin account created for '{$username}'. You can now sign in at /admin/login.php";
echo $isCli ? $message . "\n" : "<p>{$message}</p><p><strong>Delete this file now.</strong></p>";
