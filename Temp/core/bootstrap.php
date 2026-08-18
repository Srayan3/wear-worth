<?php
/**
 * Bootstraps every storefront request: session, config, error handling,
 * and every core/model class. Included once from public/index.php.
 */

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    die(
        'Configuration missing. Copy config/config.example.php to config/config.php ' .
        'and fill in your database details, then reload this page.'
    );
}
require_once $configFile;
require_once __DIR__ . '/../config/database.php';

error_reporting(APP_DEBUG ? E_ALL : 0);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// ---- Secure session configuration -----------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---- Core & model autoloading -----------------------------------------
foreach (glob(__DIR__ . '/*.php') as $file) {
    if (basename($file) !== 'bootstrap.php') {
        require_once $file;
    }
}
foreach (glob(__DIR__ . '/../models/*.php') as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . '/../controllers/*.php') as $file) {
    require_once $file;
}
