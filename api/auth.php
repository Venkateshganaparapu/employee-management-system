<?php
// api/auth.php
// ── Login / Logout API endpoint ──────────────────────────────────────────────
// POST /api/auth.php   { "action": "login",  "username": "...", "password": "..." }
// POST /api/auth.php   { "action": "logout" }
// GET  /api/auth.php   { "action": "status" }
//
// Always returns JSON:
//   { "status": "success"|"error", "message": "...", "user": {...} }
//
// Only people with a valid username + bcrypt password stored in the users table
// can log in. On success the server session is created — subsequent page/API
// requests use this session for authentication.

require_once '../includes/env.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// ── Accept both JSON body and regular form POST ───────────────────────────────
$input = [];
$raw   = file_get_contents('php://input');
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $input = $decoded;
    }
}
// Merge POST fields (form-encoded) with JSON body; POST takes precedence
$input = array_merge($input, $_POST);

$action = strtolower(trim($input['action'] ?? $_GET['action'] ?? 'status'));

// ── Rate-limiting (simple session-based brute-force guard) ────────────────────
if ($action === 'login') {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts']  = 0;
        $_SESSION['login_last_time'] = time();
    }

    // Reset counter if 15 minutes have passed
    if (time() - $_SESSION['login_last_time'] > 900) {
        $_SESSION['login_attempts']  = 0;
        $_SESSION['login_last_time'] = time();
    }

    if ($_SESSION['login_attempts'] >= 10) {
        http_response_code(429);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Too many failed login attempts. Please wait 15 minutes.',
        ]);
        exit;
    }
}

// ── Route ─────────────────────────────────────────────────────────────────────
switch ($action) {

    // ── LOGIN ─────────────────────────────────────────────────────────────────
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Use POST.']);
            exit;
        }

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        // Basic validation
        if ($username === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
            exit;
        }

        if (strlen($username) > 50 || strlen($password) > 255) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1'
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // ── Success — reset brute-force counter ───────────────────────
                $_SESSION['login_attempts']  = 0;
                $_SESSION['login_last_time'] = time();

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // Optionally re-hash with newer cost if PHP's default has changed
                if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                        ->execute([$newHash, $user['id']]);
                }

                http_response_code(200);
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Login successful.',
                    'user'    => [
                        'id'       => $user['id'],
                        'username' => $user['username'],
                        'role'     => $user['role'],
                    ],
                ]);
            } else {
                // ── Failure — increment brute-force counter ───────────────────
                $_SESSION['login_attempts']++;
                $_SESSION['login_last_time'] = time();

                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
            }
        } catch (PDOException $e) {
            error_log('[MediCore][auth.php] DB error during login: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    // ── LOGOUT ────────────────────────────────────────────────────────────────
    case 'logout':
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/', '', false, true);
        }
        session_destroy();

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
        break;

    // ── STATUS (check if logged in) ───────────────────────────────────────────
    case 'status':
        if (!empty($_SESSION['user_id'])) {
            http_response_code(200);
            echo json_encode([
                'status'        => 'success',
                'authenticated' => true,
                'user'          => [
                    'id'       => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'role'     => $_SESSION['role'],
                ],
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'status'        => 'error',
                'authenticated' => false,
                'message'       => 'Not authenticated.',
            ]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action. Use: login, logout, status.']);
}
