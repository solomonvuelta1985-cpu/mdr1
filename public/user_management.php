<?php
include '../includes/config.php';
include '../includes/functions.php';
include '../includes/auth.php';
include '../includes/password_policy.php'; // SECURITY FIX: Strong password policy

require_login();
require_admin(); // Only admin can access this page

$current_user_id = $_SESSION['user_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    
    if (verify_token($token)) {
        $action = $_POST['action'] ?? '';
        $user_id = $_POST['user_id'] ?? '';
        
        switch ($action) {
            case 'delete':
                // Soft delete user (set inactive)
                $stmt = db_query("UPDATE users SET is_active = FALSE WHERE id = ? AND id != ?", [$user_id, $current_user_id]);
                if ($stmt && $stmt->rowCount() > 0) {
                    // Also deactivate all sessions
                    db_query("UPDATE user_sessions SET is_active = FALSE WHERE user_id = ?", [$user_id]);
                    set_flash('User deactivated successfully!', 'success');
                } else {
                    set_flash('Failed to deactivate user or cannot delete yourself', 'error');
                }
                break;
                
            case 'activate':
                $stmt = db_query("UPDATE users SET is_active = TRUE WHERE id = ?", [$user_id]);
                if ($stmt && $stmt->rowCount() > 0) {
                    set_flash('User activated successfully!', 'success');
                } else {
                    set_flash('Failed to activate user', 'error');
                }
                break;
                
            case 'update':
                $full_name = sanitize($_POST['full_name']);
                $barangay = sanitize($_POST['barangay']);
                $contact_number = sanitize($_POST['contact_number']);
                $username = sanitize($_POST['username']);
                $user_role = sanitize($_POST['user_role']);
                
                // Check if username exists for other users
                $check_stmt = db_query("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $user_id]);
                if ($check_stmt->fetch()) {
                    set_flash('Username already exists!', 'error');
                } else {
                    $update_stmt = db_query("
                        UPDATE users 
                        SET full_name = ?, barangay = ?, contact_number = ?, username = ?, user_role = ?
                        WHERE id = ?
                    ", [$full_name, $barangay, $contact_number, $username, $user_role, $user_id]);
                    
                    if ($update_stmt && $update_stmt->rowCount() > 0) {
                        set_flash('User updated successfully!', 'success');
                    } else {
                        set_flash('No changes made or update failed', 'info');
                    }
                }
                break;
                
            case 'reset_password':
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                if ($new_password !== $confirm_password) {
                    set_flash('Passwords do not match!', 'error');
                } else {
                    // SECURITY FIX: Enforce strong password policy
                    $user_stmt = db_query("SELECT username FROM users WHERE id = ?", [$user_id]);
                    $user_data = $user_stmt->fetch();

                    $validation = validate_password_strength($new_password, [
                        'username' => $user_data['username'] ?? ''
                    ]);

                    if (!$validation['valid']) {
                        foreach ($validation['errors'] as $error) {
                            set_flash($error, 'error');
                        }
                    } else {
                        // Check password reuse
                        if (is_password_reused($user_id, $new_password, 5)) {
                            set_flash('Password has been used recently. Please choose a different password.', 'error');
                        } else {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $update_stmt = db_query("UPDATE users SET password = ?, password_changed_at = NOW() WHERE id = ?", [$hashed_password, $user_id]);

                            if ($update_stmt && $update_stmt->rowCount() > 0) {
                                // Save password to history
                                save_password_to_history($user_id, $hashed_password);

                                // Deactivate all active sessions for security
                                db_query("UPDATE user_sessions SET is_active = FALSE WHERE user_id = ?", [$user_id]);

                                // Log the password reset
                                log_audit_action($current_user_id, 'password_reset_admin', "Reset password for user ID: {$user_id}");

                                set_flash('Password reset successfully! All active sessions have been terminated.', 'success');
                            } else {
                                set_flash('Failed to reset password', 'error');
                            }
                        }
                    }
                }
                break;
                
            case 'create':
                $full_name = sanitize($_POST['full_name']);
                $barangay = sanitize($_POST['barangay']);
                $contact_number = sanitize($_POST['contact_number']);
                $username = sanitize($_POST['username']);
                $user_role = sanitize($_POST['user_role']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                // Validation
                $errors = [];
                if (empty($full_name)) $errors[] = "Full name is required";
                if (empty($barangay)) $errors[] = "Barangay is required";
                if (empty($contact_number)) $errors[] = "Contact number is required";
                if (empty($username)) $errors[] = "Username is required";
                if (empty($user_role)) $errors[] = "User role is required";
                if (empty($password)) $errors[] = "Password is required";
                if ($password !== $confirm_password) $errors[] = "Passwords do not match";

                // Check if username exists
                $check_stmt = db_query("SELECT id FROM users WHERE username = ?", [$username]);
                if ($check_stmt->fetch()) {
                    $errors[] = "Username already exists";
                }

                // SECURITY FIX: Validate password strength
                if (empty($errors) && !empty($password)) {
                    $validation = validate_password_strength($password, [
                        'username' => $username
                    ]);

                    if (!$validation['valid']) {
                        foreach ($validation['errors'] as $error) {
                            $errors[] = $error;
                        }
                    }
                }

                if (empty($errors)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_stmt = db_query("
                        INSERT INTO users (full_name, barangay, contact_number, username, password, user_role, created_by, password_changed_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ", [$full_name, $barangay, $contact_number, $username, $hashed_password, $user_role, $current_user_id]);

                    if ($insert_stmt) {
                        $new_user_id = $pdo->lastInsertId();

                        // Save password to history
                        save_password_to_history($new_user_id, $hashed_password);

                        // Log user creation
                        log_audit_action($current_user_id, 'user_created', "Created user: {$username} with role: {$user_role}");

                        set_flash('User created successfully!', 'success');
                    } else {
                        set_flash('Error creating user', 'error');
                    }
                } else {
                    foreach ($errors as $error) {
                        set_flash($error, 'error');
                    }
                }
                break;
        }
    } else {
        set_flash('Invalid security token', 'error');
    }
    
    header('Location: user_management.php');
    exit;
}

// Get all users with session info
$users_stmt = db_query("
    SELECT u.*, 
           creator.full_name as created_by_name,
           (SELECT COUNT(*) FROM user_sessions us WHERE us.user_id = u.id AND us.is_active = TRUE) as active_sessions
    FROM users u 
    LEFT JOIN users creator ON u.created_by = creator.id 
    ORDER BY u.created_at DESC
");
$users = $users_stmt->fetchAll();

// Get barangays for dropdown
$barangay_stmt = db_query("SELECT DISTINCT barangay FROM users WHERE barangay IS NOT NULL ORDER BY barangay");
$barangays = $barangay_stmt->fetchAll(PDO::FETCH_COLUMN);

$token = generate_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Annex Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="css/user_management.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header Section -->
                <div class="header-section">
                    <div class="header-content">
                        <h1><i class="fas fa-users me-2"></i>User Management</h1>
                        <p class="lead">Manage system users and their permissions</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="fas fa-plus me-2"></i>Create New User
                    </button>
                </div>

                <!-- Flash Messages -->
                <?php show_flash(); ?>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list me-2"></i>All Users</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Barangay</th>
                                        <th>Contact</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Sessions</th>
                                        <th>Created By</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr class="<?= $user['active_sessions'] > 0 ? 'session-active' : '' ?>">
                                        <td><?= $user['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                            <?php if ($user['id'] == $current_user_id): ?>
                                                <span class="badge bg-primary ms-1">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['barangay']) ?></td>
                                        <td><?= htmlspecialchars($user['contact_number']) ?></td>
                                        <td>
                                            <span class="role-<?= $user['user_role'] ?>">
                                                <i class="fas fa-<?= $user['user_role'] === 'admin' ? 'crown' : 'user' ?> me-1"></i>
                                                <?= ucfirst($user['user_role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="status-active">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="status-inactive">
                                                    <i class="fas fa-times-circle me-1"></i>Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['active_sessions'] > 0): ?>
                                                <span class="badge badge-online">
                                                    <i class="fas fa-circle me-1"></i><?= $user['active_sessions'] ?> active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-offline">
                                                    <i class="fas fa-circle me-1"></i>Offline
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['created_by_name'] ?? 'System') ?></td>
                                        <td><?= format_date($user['created_at']) ?></td>
                                        <td class="table-actions">
                                            <!-- Edit Button -->
                                            <button class="btn btn-outline-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal"
                                                    data-userid="<?= $user['id'] ?>"
                                                    data-fullname="<?= htmlspecialchars($user['full_name']) ?>"
                                                    data-username="<?= htmlspecialchars($user['username']) ?>"
                                                    data-barangay="<?= htmlspecialchars($user['barangay']) ?>"
                                                    data-contact="<?= htmlspecialchars($user['contact_number']) ?>"
                                                    data-role="<?= $user['user_role'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Reset Password Button -->
                                            <button class="btn btn-outline-info btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#resetPasswordModal"
                                                    data-userid="<?= $user['id'] ?>"
                                                    data-username="<?= htmlspecialchars($user['username']) ?>">
                                                <i class="fas fa-key"></i>
                                            </button>

                                            <!-- Activate/Deactivate Button -->
                                            <?php if ($user['id'] != $current_user_id): ?>
                                                <?php if ($user['is_active']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="token" value="<?= $token ?>">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-outline-warning btn-sm"
                                                                onclick="return confirm('Deactivate this user? This will log them out immediately.')"
                                                                title="Deactivate User">
                                                            <i class="fas fa-user-slash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="token" value="<?= $token ?>">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="btn btn-outline-success btn-sm"
                                                                onclick="return confirm('Activate this user?')"
                                                                title="Activate User">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="create_full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="create_full_name" name="full_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="create_username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="create_username" name="username" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="create_contact_number" class="form-label">Contact Number *</label>
                                <input type="tel" class="form-control" id="create_contact_number" name="contact_number"
                                       pattern="[0-9]{11}" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="create_barangay" class="form-label">Barangay *</label>
                                <select class="form-select" id="create_barangay" name="barangay" required>
                                    <option value="">Select Barangay</option>
                                    <?php foreach ($barangays as $brgy): ?>
                                        <option value="<?= htmlspecialchars($brgy) ?>"><?= htmlspecialchars($brgy) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="create_user_role" class="form-label">User Role *</label>
                                <select class="form-select" id="create_user_role" name="user_role" required>
                                    <option value="">Select Role</option>
                                    <option value="user">User</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <!-- Empty column for alignment -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="create_password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="create_password" name="password" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="create_confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="create_confirm_password" name="confirm_password" required minlength="6">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" id="edit_user_id">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_contact_number" class="form-label">Contact Number *</label>
                                <input type="tel" class="form-control" id="edit_contact_number" name="contact_number"
                                       pattern="[0-9]{11}" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_barangay" class="form-label">Barangay *</label>
                                <select class="form-select" id="edit_barangay" name="barangay" required>
                                    <option value="">Select Barangay</option>
                                    <?php foreach ($barangays as $brgy): ?>
                                        <option value="<?= htmlspecialchars($brgy) ?>"><?= htmlspecialchars($brgy) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_role" class="form-label">User Role *</label>
                                <select class="form-select" id="edit_user_role" name="user_role" required>
                                    <option value="">Select Role</option>
                                    <option value="user">User</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <!-- Empty column for alignment -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="user_id" id="reset_user_id">

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Resetting the password will log out the user from all active sessions.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" id="reset_username" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Enter new password">
                        </div>

                        <div class="mb-3">
                            <label for="reset_confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="reset_confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm new password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#usersTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true
            });
        });

        // Edit User Modal Handler
        const editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-userid');
            const fullName = button.getAttribute('data-fullname');
            const username = button.getAttribute('data-username');
            const barangay = button.getAttribute('data-barangay');
            const contact = button.getAttribute('data-contact');
            const role = button.getAttribute('data-role');
            
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_barangay').value = barangay;
            document.getElementById('edit_contact_number').value = contact;
            document.getElementById('edit_user_role').value = role;
        });

        // Reset Password Modal Handler
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        resetPasswordModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-userid');
            const username = button.getAttribute('data-username');
            
            document.getElementById('reset_user_id').value = userId;
            document.getElementById('reset_username').value = username;
            
            // Clear password fields
            document.getElementById('new_password').value = '';
            document.getElementById('reset_confirm_password').value = '';
        });

        // Password confirmation validation for create form
        document.querySelector('form[action="create"]')?.addEventListener('submit', function(e) {
            const password = document.getElementById('create_password').value;
            const confirmPassword = document.getElementById('create_confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });

        // Password confirmation validation for reset form
        document.querySelector('form[action="reset_password"]')?.addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('reset_confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });

        // Real-time password match indicator for create form
        document.getElementById('create_confirm_password')?.addEventListener('input', function() {
            const password = document.getElementById('create_password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (confirm && password === confirm) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-invalid', 'is-valid');
            }
        });

        // Real-time password match indicator for reset form
        document.getElementById('reset_confirm_password')?.addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (confirm && password === confirm) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-invalid', 'is-valid');
            }
        });
    </script>
</body>
</html>