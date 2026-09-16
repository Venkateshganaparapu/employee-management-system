<?php
// includes/error_handler.php
// Production-safe error handler for MediCore.
// - In PRODUCTION: logs all errors to file, never displays them to the user.
// - In DEVELOPMENT: shows full error details (APP_ENV=development in .env).
//
// Include this at the very top of every entry point, BEFORE any output.

require_once __DIR__ . '/env.php';

$appEnv   = env('APP_ENV', 'production');
$appDebug = env('APP_DEBUG', false);
$isDev    = ($appEnv === 'development' || $appDebug === true);

// ── PHP error display ─────────────────────────────────────────────────────────
if ($isDev) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);   // Still report everything — just don't display it
}

ini_set('log_errors', '1');

// Set log path — prefer env variable, fallback to project logs/ directory
$logPath = env('PHP_ERROR_LOG', dirname(__DIR__) . '/logs/php_errors.log');
ini_set('error_log', $logPath);

// Ensure logs directory exists
$logsDir = dirname($logPath);
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0750, true);
}

// ── Custom error handler ──────────────────────────────────────────────────────
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    global $isDev;
    // Let PHP handle errors that are suppressed with @
    if (!(error_reporting() & $errno)) {
        return false;
    }
    error_log("[MediCore][PHP Error] [$errno] $errstr in $errfile on line $errline");
    if ($isDev) {
        return false; // Fall through to default PHP handler in dev
    }
    return true; // In production: suppress display, log only
});

// ── Uncaught exception handler ────────────────────────────────────────────────
set_exception_handler(function (Throwable $e) use ($isDev): void {
    error_log(
        '[MediCore][Uncaught Exception] ' . get_class($e) .
        ': ' . $e->getMessage() .
        ' in ' . $e->getFile() . ' on line ' . $e->getLine()
    );
    if ($isDev) {
        echo '<pre style="background:#fee2e2;color:#991b1b;padding:20px;margin:20px;border-radius:8px;">';
        echo '<strong>' . htmlspecialchars(get_class($e)) . '</strong>: ';
        echo htmlspecialchars($e->getMessage()) . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        _show_friendly_error();
    }
});

// ── Fatal error handler ───────────────────────────────────────────────────────
register_shutdown_function(function () use ($isDev): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log(
            '[MediCore][Fatal Error] ' . $error['message'] .
            ' in ' . $error['file'] . ' on line ' . $error['line']
        );
        if (!$isDev) {
            // Clear any partial output already buffered
            if (ob_get_length()) {
                ob_end_clean();
            }
            _show_friendly_error();
        }
    }
});

// ── Friendly error page ───────────────────────────────────────────────────────
function _show_friendly_error(): void {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong — MediCore</title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; display:flex; align-items:center;
               justify-content:center; min-height:100vh; margin:0; background:#f1f5f9; }
        .box { background:white; border-radius:16px; padding:48px 40px; max-width:480px;
               text-align:center; box-shadow:0 4px 24px rgba(0,0,0,.08); }
        .icon { font-size:3rem; margin-bottom:16px; }
        h1 { color:#1E3A8A; font-size:1.5rem; margin:0 0 12px; }
        p  { color:#64748b; margin:0 0 24px; line-height:1.6; }
        a  { display:inline-block; background:#14B8A6; color:white; text-decoration:none;
             padding:10px 24px; border-radius:8px; font-weight:600; }
        a:hover { background:#0d9488; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">⚠️</div>
        <h1>Something went wrong</h1>
        <p>An unexpected error occurred. Our team has been notified.<br>
           Please try again or return to the dashboard.</p>
        <a href="../dashboard.php">Return to Dashboard</a>
    </div>
</body>
</html>';
}
