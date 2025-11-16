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

        /* Notification Bell Styles */
        .notification-bell {
            position: relative;
            margin-right: 20px;
        }
        .notification-bell .btn {
            position: relative;
            color: #fff;
            background: transparent;
            border: none;
            font-size: 1.3rem;
            padding: 0.5rem;
        }
        .notification-bell .btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -6px;
            background: #dc3545;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 400px;
            max-height: 500px;
            overflow: hidden;
            display: none;
            z-index: 1050;
        }
        .notification-dropdown.show {
            display: block;
        }
        .notification-header {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fafb;
        }
        .notification-header h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }
        .mark-all-read {
            background: none;
            border: none;
            color: #3b82f6;
            font-size: 12px;
            cursor: pointer;
            padding: 0;
        }
        .mark-all-read:hover {
            text-decoration: underline;
        }
        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notification-item:hover {
            background: #f9fafb;
        }
        .notification-item.unread {
            background: #eff6ff;
        }
        .notification-item.priority {
            border-left: 4px solid #dc3545;
        }
        .notification-content {
            font-size: 13px;
            color: #374151;
            margin-bottom: 4px;
        }
        .notification-content strong {
            color: #111827;
            font-weight: 600;
        }
        .notification-meta {
            font-size: 11px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification-time {
            color: #9ca3af;
        }
        .notification-badge-inline {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 3px;
            margin-left: 6px;
        }
        .badge-priority {
            background: #fee2e2;
            color: #991b1b;
        }
        .empty-notifications {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
        }
        .empty-notifications i {
            font-size: 3rem;
            margin-bottom: 12px;
            opacity: 0.3;
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
                    <?php
                    // Show notification bell for admin and staff only
                    $show_notifications = isset($_SESSION['user_role']) &&
                                        ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff');

                    // Debug: Uncomment to see why bell is not showing
                    echo "<!-- DEBUG: user_role=" . ($_SESSION['user_role'] ?? 'NOT SET') . ", show_notifications=" . ($show_notifications ? 'YES' : 'NO') . " -->";
                    ?>

                    <!-- TEST: Temporary visible indicator -->
                    <?php if ($show_notifications): ?>
                        <li class="nav-item" style="color: white; padding: 8px;">
                            [BELL SHOULD BE HERE]
                        </li>
                    <?php endif; ?>

                    <?php if ($show_notifications): ?>
                    <li class="nav-item notification-bell">
                        <button class="btn" id="notificationBell" type="button">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                        </button>

                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h6>Notifications</h6>
                                <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div class="empty-notifications">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No notifications</p>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>

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

    <?php if ($show_notifications ?? false): ?>
    <!-- Notification System JavaScript -->
    <script>
        (function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationBadge = document.getElementById('notificationBadge');
            const notificationList = document.getElementById('notificationList');
            const markAllReadBtn = document.getElementById('markAllRead');

            let notifications = [];
            let dropdownOpen = false;

            // Toggle dropdown
            notificationBell.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownOpen = !dropdownOpen;
                notificationDropdown.classList.toggle('show', dropdownOpen);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (dropdownOpen && !notificationDropdown.contains(e.target)) {
                    dropdownOpen = false;
                    notificationDropdown.classList.remove('show');
                }
            });

            // Fetch notifications
            function fetchNotifications() {
                fetch('../api/notifications_fetch.php')
                    .then(response => response.json())
                    .then(data => {
                        notifications = data.notifications || [];
                        updateBadge(data.count || 0);
                        renderNotifications();
                    })
                    .catch(error => {
                        console.error('Failed to fetch notifications:', error);
                    });
            }

            // Update badge count
            function updateBadge(count) {
                if (count > 0) {
                    notificationBadge.textContent = count > 99 ? '99+' : count;
                    notificationBadge.style.display = 'block';
                } else {
                    notificationBadge.style.display = 'none';
                }
            }

            // Render notifications list
            function renderNotifications() {
                if (notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div class="empty-notifications">
                            <i class="fas fa-bell-slash"></i>
                            <p>No notifications</p>
                        </div>
                    `;
                    return;
                }

                const html = notifications.map(notif => {
                    const priorityClass = notif.is_priority ? 'priority' : '';
                    const unreadClass = !notif.is_read ? 'unread' : '';
                    const priorityBadge = notif.is_priority ? '<span class="notification-badge-inline badge-priority">PRIORITY</span>' : '';

                    return `
                        <div class="notification-item ${unreadClass} ${priorityClass}"
                             data-id="${notif.id}"
                             data-record-id="${notif.record_id}"
                             data-annex="${notif.annex_number}">
                            <div class="notification-content">
                                <strong>Barangay ${notif.barangay}</strong> ${notif.action_text} in
                                <strong>Annex ${notif.annex_number}</strong>${priorityBadge}
                            </div>
                            <div class="notification-content">
                                ${notif.message}
                            </div>
                            <div class="notification-meta">
                                <span>By: ${notif.submitted_by_name}</span>
                                <span class="notification-time">${notif.time_ago}</span>
                            </div>
                        </div>
                    `;
                }).join('');

                notificationList.innerHTML = html;

                // Add click handlers to notification items
                notificationList.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const notifId = this.dataset.id;
                        const recordId = this.dataset.recordId;
                        const annexNumber = this.dataset.annex;

                        // Mark as read
                        markAsRead(notifId);

                        // Navigate to the record
                        const recordsPage = `annex${annexNumber}_records.php`;
                        window.location.href = recordsPage + '?highlight=' + recordId;
                    });
                });
            }

            // Mark single notification as read
            function markAsRead(notificationId) {
                fetch('../api/notifications_mark_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'mark_one',
                        notification_id: notificationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update local state
                        const notif = notifications.find(n => n.id == notificationId);
                        if (notif) {
                            notif.is_read = true;
                        }
                        fetchNotifications();
                    }
                })
                .catch(error => {
                    console.error('Failed to mark notification as read:', error);
                });
            }

            // Mark all notifications as read
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();

                fetch('../api/notifications_mark_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'mark_all'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notifications.forEach(n => n.is_read = true);
                        fetchNotifications();
                    }
                })
                .catch(error => {
                    console.error('Failed to mark all as read:', error);
                });
            });

            // Auto-refresh every 30 seconds
            fetchNotifications(); // Initial fetch
            setInterval(fetchNotifications, 30000); // Refresh every 30 seconds
        })();
    </script>
    <?php endif; ?>