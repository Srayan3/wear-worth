<?php
/**
 * CSRF protection. Every state-changing form (POST) across the storefront
 * and admin must include csrf_field() and every handler must call
 * csrf_verify() before acting on the request.
 */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verify a submitted token; halts the request with 419 on mismatch. */
function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $expected = $_SESSION['csrf_token'] ?? '';

    if ($expected === '' || !hash_equals($expected, (string) $submitted)) {
        http_response_code(419);
        if (str_starts_with($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Your session has expired. Please refresh and try again.']);
        } else {
            echo 'Your session has expired. Please go back, refresh the page, and try again.';
        }
        exit;
    }
}
