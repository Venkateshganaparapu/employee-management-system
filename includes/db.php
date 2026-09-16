<?php
// includes/db.php
// Database connection — credentials loaded from environment variables.
// Copy .env.example to .env and fill in your values before running.

require_once __DIR__ . '/env.php';

$host     = env('DB_HOST', '127.0.0.1');
$port     = env('DB_PORT', '3306');
$dbname   = env('DB_NAME', 'pharmacy_db');
$username = env('DB_USER', 'root');
$password = env('DB_PASSWORD', '');

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false); // Use real prepared statements
} catch (PDOException $e) {
    // Log the real error — NEVER expose it to the browser
    error_log('[MediCore][DB] Connection failed: ' . $e->getMessage());

    // Check if we are an API endpoint (return JSON) or a page (show HTML)
    $isApi = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/');
    if ($isApi) {
        header('Content-Type: application/json');
        http_response_code(503);
        echo json_encode(['status' => 'error', 'message' => 'Database unavailable. Please try again later.']);
    } else {
        http_response_code(503);
        echo '<!DOCTYPE html><html><head><title>Service Unavailable</title></head>
        <body style="font-family:sans-serif;text-align:center;padding:60px;">
        <h2>Service Temporarily Unavailable</h2>
        <p>We are experiencing technical difficulties. Please try again shortly.</p>
        </body></html>';
    }
    exit;
}
