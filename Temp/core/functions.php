<?php
/**
 * Global helper functions. Kept framework-free and dependency-free
 * so the project runs on any stock PHP 8 / Apache shared host.
 */

/** Escape output for safe HTML rendering (XSS protection). */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Build an absolute URL from a site-relative path. */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . '/' . $path;
}

/** Build an absolute asset URL (cache-busted by file mtime when possible). */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $full = ROOT_PATH . '/' . $path;
    $v = is_file($full) ? filemtime($full) : time();
    return rtrim(BASE_URL, '/') . '/' . $path . '?v=' . $v;
}

/** Resolve a stored product image path to a browsable URL. */
function product_image_url(?string $path): string
{
    if (empty($path)) {
        return url('assets/images/placeholders/dress-1.svg');
    }
    // Stored paths are relative to project root (either assets/... seed images
    // or uploads/products/... admin-uploaded images).
    return url($path);
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/** Format a price using the store's configured currency symbol. */
function money(float|string $amount): string
{
    static $symbol = null;
    if ($symbol === null) {
        $symbol = function_exists('setting') ? setting('currency_symbol', '৳') : '৳';
    }
    return $symbol . number_format((float) $amount, 2);
}

function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
    $text = trim($text, '-');
    $text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    return $text === '' ? bin2hex(random_bytes(4)) : $text;
}

/** Ensure a slug is unique within a table, appending -2, -3, ... as needed. */
function unique_slug(PDO $db, string $table, string $base, ?int $excludeId = null): string
{
    $slug = slugify($base);
    $original = $slug;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = :slug" . ($excludeId ? " AND id != :id" : "");
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($excludeId) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $original . '-' . $i;
        $i++;
    }
}

/** Flash messages (one-time session notices). */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
}

function flash_get_all(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

/** Generate a unique, human-friendly order number. */
function generate_order_number(): string
{
    return 'ORD' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

/** Current request path (without query string), always starting with /.
 *  Automatically strips BASE_URL's own path component, so the app
 *  routes correctly whether it lives at the domain root or in a
 *  subdirectory (e.g. BASE_URL = http://localhost/WearWorth/Store). */
function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $basePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    if ($basePath !== '' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
    }
    return '/' . ltrim($path, '/');
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old_input'][$key] ?? $default);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Very small input sanitizer for plain text fields (trims + strips tags). */
function clean_str(mixed $value): string
{
    return trim(strip_tags((string) ($value ?? '')));
}

function paginate_range(int $current, int $total): array
{
    $start = max(1, $current - 2);
    $end = min($total, $current + 2);
    return range($start, $end);
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Renders a storefront page: header partial -> view -> footer partial.
 * $meta accepts: title, description, canonical, og_image.
 */
function render(string $view, array $data = [], array $meta = []): void
{
    extract($data);
    $pageTitle = $meta['title'] ?? setting('meta_default_title', 'Atelier');
    $pageDescription = $meta['description'] ?? setting('meta_default_description', '');
    $canonical = $meta['canonical'] ?? url(ltrim(current_path(), '/'));
    $ogImage = $meta['og_image'] ?? '';

    require ROOT_PATH . '/views/partials/header.php';
    require ROOT_PATH . '/views/' . $view . '.php';
    require ROOT_PATH . '/views/partials/footer.php';
}

/** Renders an admin page: sidebar/header partial -> view -> footer partial. */
function admin_render(string $view, array $data = [], string $pageTitle = 'Dashboard'): void
{
    extract($data);
    require ROOT_PATH . '/admin/views/partials/header.php';
    require ROOT_PATH . '/admin/views/' . $view . '.php';
    require ROOT_PATH . '/admin/views/partials/footer.php';
}

/** JSON response helper for AJAX endpoints. */
function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

function admin_asset(string $path): string
{
    $full = ROOT_PATH . '/admin/' . ltrim($path, '/');
    $v = is_file($full) ? filemtime($full) : time();
    return rtrim(BASE_URL, '/') . '/admin/' . ltrim($path, '/') . '?v=' . $v;
}
