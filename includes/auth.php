<?php
// includes/auth.php
// Central authentication & CSRF helpers for MediCore.
// Include this file at the top of any page or API endpoint that requires login.

if (session_status() === PHP_SESSION_NONE) {
    // ── Secure session settings (applied before session_start) ──────────────
    ini_set('session.cookie_httponly', '1');   // Block JS access to session cookie
    ini_set('session.use_strict_mode', '1');   // Reject uninitialized session IDs
    ini_set('session.cookie_samesite', 'Lax'); // CSRF mitigation

    // Mark cookie Secure only when running over HTTPS
    if (
        (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

/**
 * Require an authenticated session.
 * On failure: API endpoints get a 401 JSON response; pages get a redirect.
 */
function require_auth(): void {
    if (empty($_SESSION['user_id'])) {
        if (_is_api_request()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
            exit;
        }
        header('Location: ' . _base_url() . 'login.php');
        exit;
    }
}

/**
 * Require Admin role.
 * Must be called after require_auth().
 */
function require_admin(): void {
    require_auth();
    if (($_SESSION['role'] ?? '') !== 'Admin') {
        if (_is_api_request()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Forbidden. Admin access required.']);
            exit;
        }
        header('Location: ' . _base_url() . 'dashboard.php');
        exit;
    }
}

/**
 * Generate (or retrieve) a CSRF token for the current session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the CSRF token submitted with a POST request.
 * Exits with 403 if invalid.
 */
function csrf_verify(): void {
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $submitted)) {
        if (_is_api_request()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
            exit;
        }
        http_response_code(403);
        die('Invalid CSRF token. <a href="javascript:history.back()">Go back</a>');
    }
}

/**
 * Output a hidden CSRF input field for use inside HTML forms.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// ── Private helpers ───────────────────────────────────────────────────────────

function _is_api_request(): bool {
    // Treat requests to /api/ directory or requests expecting JSON as API calls
    return (
        str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/') ||
        str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
    );
}

function _base_url(): string {
    // Build a simple base URL for redirects
    $depth = substr_count($_SERVER['SCRIPT_NAME'] ?? '', '/') - 1;
    return str_repeat('../', max(0, $depth));
}
