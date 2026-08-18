<?php
/**
 * Copy this file to config.php and fill in your real values.
 * config.php is (and must remain) excluded from version control.
 */

// ---------------------------------------------------------------------------
// Database
// ---------------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'wearwort_db');
define('DB_USER', 'wearwort_srayan');
define('DB_PASS', 'G48733784g');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------------
// Site
// ---------------------------------------------------------------------------
// Full base URL with NO trailing slash, e.g. https://www.yourstore.com
define('BASE_URL', 'https://wear-worth.com');

// Absolute filesystem path to the project root (auto-detected, override if needed)
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/products');
define('UPLOAD_URL', BASE_URL . '/uploads/products');

// Set to false in production
define('APP_DEBUG', true);

// A long random string, unique per install — used to key sessions/tokens.
// Generate one with: php -r "echo bin2hex(random_bytes(32));"
define('APP_KEY', 'change-this-to-a-random-64-character-string-before-deploying');

// ---------------------------------------------------------------------------
// Uploads
// ---------------------------------------------------------------------------
define('MAX_UPLOAD_BYTES', 4 * 1024 * 1024); // 4MB per image
define('ALLOWED_IMAGE_MIME', ['image/jpeg', 'image/png', 'image/webp']);

// ---------------------------------------------------------------------------
// Admin session
// ---------------------------------------------------------------------------
define('ADMIN_SESSION_TIMEOUT', 60 * 30); // 30 minutes of inactivity
define('ADMIN_LOGIN_MAX_ATTEMPTS', 5);     // per username+IP
define('ADMIN_LOGIN_LOCKOUT_SECONDS', 15 * 60);

date_default_timezone_set('Asia/Dhaka');
