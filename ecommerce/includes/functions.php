<?php
/**
 * includes/functions.php — shared helpers used across the app.
 *
 * Pulls in the PDO connection ($pdo) and provides: session bootstrap, output
 * escaping (XSS), money formatting, auth guards, CSRF protection, and flash
 * messages. Include this near the top of every page.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

// Start a session once, before any output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* -------------------------------------------------------------------------
 * Output / formatting
 * ---------------------------------------------------------------------- */

/**
 * Escape a value for safe HTML output (prevents XSS).
 * Accepts any scalar/null — MySQL with native prepares can return numeric
 * columns as int/float, so we cast rather than type-hint a string.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Format a numeric amount as a price string. */
function money($amount): string
{
    return '$' . number_format((float) $amount, 2);
}

/** Send a redirect and stop. */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/* -------------------------------------------------------------------------
 * Authentication / authorization
 * ---------------------------------------------------------------------- */

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/** Require a logged-in user; otherwise redirect to login (optionally remembering where to return). */
function require_login(string $returnTo = ''): void
{
    if (!is_logged_in()) {
        if ($returnTo !== '') {
            $_SESSION['redirect_after_login'] = $returnTo;
        }
        redirect('login.php');
    }
}

/** Require an admin; non-admins are sent to the storefront home. */
function require_admin(): void
{
    if (!is_admin()) {
        redirect('../index.php');
    }
}

/* -------------------------------------------------------------------------
 * CSRF protection
 * ---------------------------------------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input carrying the CSRF token, for embedding in forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Validate the submitted CSRF token; aborts the request on mismatch. */
function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        die('Invalid or expired form token. Please go back and try again.');
    }
}

/* -------------------------------------------------------------------------
 * Flash messages (one-shot notifications)
 * ---------------------------------------------------------------------- */

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Return and clear all queued flash messages. */
function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* -------------------------------------------------------------------------
 * Cart helpers
 * ---------------------------------------------------------------------- */

/** Total quantity of items in the current user's cart (0 when logged out). */
function cart_count(PDO $pdo): int
{
    if (!is_logged_in()) {
        return 0;
    }
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ?');
    $stmt->execute([current_user_id()]);
    return (int) $stmt->fetchColumn();
}
