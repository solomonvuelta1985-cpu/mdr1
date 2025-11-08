<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$username = '';
$password = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // SECURITY FIX: CSRF Token Validation on Login
    if (!isset($_POST['csrf_token']) || !verify_token($_POST['csrf_token'])) {
        log_security_event(0, 'csrf_failure_login', "CSRF token validation failed on login from IP: {$_SERVER['REMOTE_ADDR']}");
        set_flash('Security validation failed. Please try again.', 'error');
        header('Location: login.php');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // SECURITY FIX: LOGIN RATE LIMITING (5 attempts per 15 minutes per username)
    $rate_limit_key = "login_attempt_" . hash('sha256', strtolower($username));
    if (!check_rate_limit(0, $rate_limit_key, 5, 900)) {
        log_security_event(0, 'login_rate_limit_exceeded',
            "Excessive login attempts for user: {$username} from IP: {$_SERVER['REMOTE_ADDR']}");

        set_flash('Too many login attempts. Please try again in 15 minutes.', 'error');
        header('Location: login.php');
        exit;
    }

    // IMMEDIATE BARANGAY LOCK CHECK - BEFORE ANYTHING ELSE
    if (!can_user_login($username)) {
        $lock_message = get_barangay_lock_message($username);
        set_flash($lock_message, 'error');
        header('Location: login.php');
        exit;
    }


    // Continue with normal login...
    $stmt = db_query("SELECT * FROM users WHERE username = ? AND is_active = TRUE", [$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // SUCCESS - Clear rate limit for this username
        db_query("DELETE FROM rate_limits WHERE action = ? AND user_id = 0", [$rate_limit_key]);

        // SECURITY FIX: Regenerate session ID (prevents session fixation)
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['user_role'];
        $_SESSION['barangay'] = $user['barangay'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // SECURITY FIX: Create session fingerprint (anti-hijacking)
        $_SESSION['fingerprint'] = hash('sha256',
            ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') .
            ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') .
            session_id()
        );

        // Track session in database
        track_user_session($user['id'], 'login');

        // Log successful login
        log_audit_action($user['id'], 'login_success',
            "User logged in from IP: {$_SERVER['REMOTE_ADDR']}");

        // If admin, set barangay session
        if ($user['user_role'] === 'admin') {
            // Admin doesn't automatically lock their barangay - they must select one
        }

        set_flash('Login successful! Welcome back, ' . $user['full_name'], 'success');
        header('Location: dashboard.php');
        exit;
    } else {
        // FAILED LOGIN - Log it
        log_security_event(0, 'failed_login_attempt',
            "Failed login for user: {$username} from IP: {$_SERVER['REMOTE_ADDR']}");

        set_flash('Invalid username or password', 'error');
    }
}

$token = generate_token();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Annex Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            background: white;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem;
            text-align: center;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header login-header">
                <h3><i class="fas fa-file-contract me-2"></i>Annex System</h3>
                <p class="mb-0">Sign in to your account</p>
            </div>
            <div class="card-body p-4">
                <?php show_flash(); ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $token ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

                <!-- SECURITY FIX: Default credentials removed to prevent unauthorized access -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>