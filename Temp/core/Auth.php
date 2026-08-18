<?php

/**
 * Admin authentication. Every admin controller must call
 * Auth::requireLogin() before doing anything else.
 */
class Auth
{
    public static function attempt(string $username, string $password): array
    {
        $db = Database::connect();
        $ip = client_ip();

        if (self::isLockedOut($username, $ip)) {
            self::logAttempt($username, $ip, false);
            return ['success' => false, 'message' => 'Too many failed attempts. Please try again in a few minutes.'];
        }

        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u AND is_active = 1 LIMIT 1");
        $stmt->execute(['u' => $username]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            self::logAttempt($username, $ip, false);
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        self::logAttempt($username, $ip, true);

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_last_activity'] = time();

        $db->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = :id")->execute(['id' => $admin['id']]);

        return ['success' => true];
    }

    private static function isLockedOut(string $username, string $ip): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM admin_login_attempts
             WHERE username = :u AND ip_address = :ip AND was_successful = 0
               AND attempted_at >= (NOW() - INTERVAL :secs SECOND)"
        );
        $stmt->execute(['u' => $username, 'ip' => $ip, 'secs' => ADMIN_LOGIN_LOCKOUT_SECONDS]);
        return (int) $stmt->fetchColumn() >= ADMIN_LOGIN_MAX_ATTEMPTS;
    }

    private static function logAttempt(string $username, string $ip, bool $success): void
    {
        $db = Database::connect();
        $db->prepare(
            "INSERT INTO admin_login_attempts (username, ip_address, was_successful) VALUES (:u, :ip, :s)"
        )->execute(['u' => $username, 'ip' => $ip, 's' => $success ? 1 : 0]);

        if ($success) {
            // Clear the slate on a successful login so old failures don't linger
            $db->prepare(
                "DELETE FROM admin_login_attempts WHERE username = :u AND ip_address = :ip"
            )->execute(['u' => $username, 'ip' => $ip]);
        }
    }

    public static function check(): bool
    {
        if (empty($_SESSION['admin_id'])) {
            return false;
        }
        $lastActivity = $_SESSION['admin_last_activity'] ?? 0;
        if (time() - $lastActivity > ADMIN_SESSION_TIMEOUT) {
            self::logout();
            return false;
        }
        $_SESSION['admin_last_activity'] = time();
        return true;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['flash']['error'][] = 'Please log in to continue.';
            header('Location: ' . rtrim(BASE_URL, '/') . '/admin/login.php');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array($_SESSION['admin_role'] ?? '', $roles, true)) {
            http_response_code(403);
            echo "You don't have permission to access this page.";
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function id(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    public static function name(): string
    {
        return $_SESSION['admin_name'] ?? 'Admin';
    }
}
