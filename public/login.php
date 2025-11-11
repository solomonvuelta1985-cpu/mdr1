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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MDRRM-ARMS | Login</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #f0f4f8;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      padding: 40px;
      width: 100%;
      max-width: 420px;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo-container {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }

    .logo {
      width: 70px;
      height: 70px;
      background: #1e40af;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 28px;
    }

    h1 {
      color: #1e293b;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .subtitle {
      color: #64748b;
      font-size: 14px;
      line-height: 1.5;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: #374151;
      font-weight: 500;
      font-size: 14px;
    }

    .input-with-icon {
      position: relative;
    }

    .input-with-icon i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
    }

    input {
      width: 100%;
      padding: 14px 14px 14px 45px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.2s ease;
      background: #f9fafb;
    }

    input:focus {
      outline: none;
      border-color: #3b82f6;
      background: white;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
      cursor: pointer;
      z-index: 10;
    }

    .login-btn {
      width: 100%;
      padding: 14px;
      background: #1e40af;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease;
      margin-top: 10px;
    }

    .login-btn:hover {
      background: #1e3a8a;
    }

    .login-btn:disabled {
      background: #93c5fd;
      cursor: not-allowed;
    }

    .footer {
      text-align: center;
      margin-top: 30px;
      color: #9ca3af;
      font-size: 12px;
      line-height: 1.5;
    }

    .flash-message {
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-left: 4px solid;
    }

    .flash-message.error {
      background: #fef2f2;
      color: #dc2626;
      border-left-color: #dc2626;
    }

    .flash-message.success {
      background: #f0fdf4;
      color: #16a34a;
      border-left-color: #16a34a;
    }

    .flash-message i {
      font-size: 18px;
    }

    @media (max-width: 480px) {
      .login-container {
        padding: 30px 25px;
      }

      h1 {
        font-size: 22px;
      }

      .logo {
        width: 60px;
        height: 60px;
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="header">
      <div class="logo-container">
        <div class="logo">
          <i class="fas fa-shield-alt"></i>
        </div>
      </div>
      <h1>MDRRM-ARMS</h1>
      <p class="subtitle">
        Municipal Disaster Risk Reduction & Management<br>
        Annex Reporting and Monitoring System
      </p>
    </div>

    <!-- PHP Flash Messages -->
    <?php
    if (isset($_SESSION['flash_message'])) {
        $flash_type = $_SESSION['flash_type'] ?? 'error';
        $flash_message = $_SESSION['flash_message'];
        $icon = $flash_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        echo '<div class="flash-message ' . htmlspecialchars($flash_type) . '">
          <i class="fas ' . $icon . '"></i>
          <span>' . htmlspecialchars($flash_message) . '</span>
        </div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
    ?>

    <form method="POST" id="loginForm">
      <input type="hidden" name="csrf_token" value="<?= $token ?>">

      <div class="form-group">
        <label for="username">Username</label>
        <div class="input-with-icon">
          <i class="fas fa-user"></i>
          <input type="text" id="username" name="username" placeholder="Enter your username" value="<?= htmlspecialchars($username) ?>" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
          <span class="password-toggle" id="passwordToggle">
            <i class="fas fa-eye"></i>
          </span>
        </div>
      </div>

      <button type="submit" class="login-btn" id="loginBtn">
        Sign In
      </button>
    </form>

    <div class="footer">
      &copy; 2025 MDRRM-ARMS<br>
      Developed for the Municipal Disaster Risk Reduction and Management Office
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Password visibility toggle
      const passwordToggle = document.getElementById('passwordToggle');
      const passwordInput = document.getElementById('password');

      passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Toggle eye icon
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      });

      // Form submission animation
      const loginForm = document.getElementById('loginForm');
      const loginBtn = document.getElementById('loginBtn');

      loginForm.addEventListener('submit', function(e) {
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
        loginBtn.disabled = true;
      });
    });
  </script>
</body>
</html>