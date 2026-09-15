<?php
// login.php
require_once 'includes/env.php';
require_once 'includes/error_handler.php';
require_once 'includes/auth.php';   // Starts session with secure settings
require_once 'includes/db.php';

// Already logged in? Redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // CSRF verification
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (strlen($username) > 50 || strlen($password) > 255) {
        $error = 'Invalid credentials.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1'
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // Re-hash if cost factor has changed
                if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                        ->execute([$newHash, $user['id']]);
                }

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            error_log('[MediCore][login.php] ' . $e->getMessage());
            $error = 'A server error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MediCore Pharmacy</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        body {
            height: 100vh;
            overflow: hidden;
            background: var(--bg-body);
        }
        
        .split-right {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: white;
            animation: fadeInRight 1s ease-out;
        }

        .split-left {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: var(--bg-gradient);
            position: relative;
            overflow: hidden;
            animation: fadeInLeft 1s ease-out;
        }

        @keyframes fadeInLeft {
            from { transform: translateX(-50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeInRight {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }

        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
        }

        .floating-icon {
            position: absolute;
            color: rgba(255,255,255,0.2);
            animation: float 8s infinite alternate ease-in-out;
        }
        
        .ic-1 { top: 15%; left: 20%; width: 80px; height: 80px; }
        .ic-2 { top: 60%; right: 15%; width: 100px; height: 100px; animation-delay: -2s; }
        .ic-3 { bottom: 10%; left: 30%; width: 60px; height: 60px; animation-delay: -4s; }

        .left-content {
            position: relative;
            z-index: 10;
            color: white;
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>

    <div class="row g-0 h-100">
        <!-- Left Side: Illustration Panel -->
        <div class="col-md-6 split-left d-none d-md-flex flex-column">
            <i data-lucide="pill" class="floating-icon ic-1"></i>
            <i data-lucide="cross" class="floating-icon ic-2"></i>
            <i data-lucide="flask-conical" class="floating-icon ic-3"></i>
            
            <div class="left-content shadow-lg">
                <div class="bg-white text-teal rounded-circle p-3 d-inline-block mb-4">
                    <i data-lucide="shield-plus" width="40" height="40"></i>
                </div>
                <h2 class="fw-bold mb-3">MediCore System</h2>
                <p class="mb-0 text-white-50">Streamline pharmacy workflows, track expiry dates, and manage sales securely in one unified platform.</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="col-md-6 split-right border-start">
            <div class="login-box glass-card border-0 shadow-none">
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-deep-blue">Welcome Back</h3>
                    <p class="text-muted">Sign in to your dashboard</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border text-muted">
                                <i data-lucide="user" width="18" height="18"></i>
                            </span>
                            <input type="text" name="username" class="form-control form-control-modern border-start-0 ps-0"
                                   placeholder="admin" required maxlength="50"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-semibold">Password</label>
                        <div class="input-group password-container">
                            <span class="input-group-text bg-transparent border-end-0 border text-muted">
                                <i data-lucide="lock" width="18" height="18"></i>
                            </span>
                            <input type="password" name="password" id="password"
                                   class="form-control form-control-modern border-start-0 ps-0"
                                   placeholder="Password" required maxlength="255">
                            <i data-lucide="eye" class="password-toggle" id="togglePassword" width="18" height="18"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input text-teal" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label text-muted small" for="remember">
                                Remember Default
                            </label>
                        </div>
                        <span class="small text-muted">Forgot password? Contact admin.</span>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" name="login" class="btn btn-custom py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            Sign In <i data-lucide="arrow-right" width="18"></i>
                        </button>
                    </div>
                    
                    <div class="text-center mt-4">
                       <a href="index.html" class="text-muted small text-decoration-none d-flex align-items-center justify-content-center gap-1">
                           <i data-lucide="arrow-left" width="14"></i> Back to Home
                       </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Init Icons
        lucide.createIcons();

        // Password Show/Hide Toggle
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            togglePassword.setAttribute('data-lucide', type === 'text' ? 'eye-off' : 'eye');
            lucide.createIcons();
        });
    </script>
</body>
</html>
