<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include auth.php to get access to is_logged_in() and other auth functions
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .navbar {
            margin-bottom: 0 !important;
            border-radius: 0 !important;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .user-welcome {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .container {
            padding-left: 15px;
            padding-right: 15px;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary" style="margin: 0; padding: 0.5rem 0;">
        <div class="container" style="max-width: 100%;">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-file-contract me-2"></i>
          
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (is_logged_in()): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a>
                    </li>
                    <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_management.php"><i class="fas fa-users me-1"></i> User Management</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text user-welcome">
                                <small>Logged in as: <?php echo htmlspecialchars($_SESSION['user_role'] ?? 'User'); ?></small>
                                <?php if (!empty($_SESSION['barangay'])): ?>
                                <br><small>Barangay: <?php echo htmlspecialchars($_SESSION['barangay']); ?></small>
                                <?php endif; ?>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>