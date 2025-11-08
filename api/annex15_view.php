<?php
/**
 * API Handler: View Suspension of Classes Record Details (Annex 15)
 * Simple and minimal version
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Authentication check
require_login();

// Get record ID
$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    exit;
}

// Get user info
$is_admin = ($_SESSION['user_role'] === 'admin');

// Fetch record
try {
    if ($is_admin) {
        $sql = "SELECT a.*, u.full_name 
                FROM annex15_suspension_classes a 
                LEFT JOIN users u ON a.created_by = u.id 
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT a.*, u.full_name 
                FROM annex15_suspension_classes a 
                LEFT JOIN users u ON a.created_by = u.id 
                WHERE a.id = ? AND a.created_by = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id, $_SESSION['user_id']]);
    }

    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        echo '<div class="alert alert-warning">Record not found or access denied.</div>';
        exit;
    }
    
    // Log the view action
    log_audit_action(
        $_SESSION['user_id'],
        'annex15_view',
        "Viewed suspension of classes record #{$record_id} for {$record['barangay']}"
    );
    
    // SECURITY LOG: Record view
    log_security_event(
        $_SESSION['user_id'],
        'record_view',
        "Viewed Annex 15 record #{$record_id}"
    );
    
} catch (PDOException $e) {
    error_log("Annex15 View Error: " . $e->getMessage());
    echo '<div class="alert alert-danger">Unable to load record. Please try again.</div>';
    exit;
}
?>

<style>
    .view-container {
        padding: 20px;
    }
    .section-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
    }
    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        width: 40%;
        font-weight: 500;
        color: #6c757d;
    }
    .info-value {
        width: 60%;
        color: #212529;
    }
    .badge-simple {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .status-active {
        background-color: #fff3cd;
        color: #856404;
    }
    .status-resumed {
        background-color: #d1edff;
        color: #0c63e4;
    }
</style>

<div class="view-container">
    <!-- Header -->
    <div class="mb-4">
        <h5 class="mb-2">Suspension of Classes Report #<?php echo $record['id']; ?></h5>
        <small class="text-muted">
            <?php echo htmlspecialchars($record['barangay']); ?>, 
            <?php echo htmlspecialchars($record['city']); ?>, 
            <?php echo htmlspecialchars($record['province']); ?>
        </small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Location Details -->
            <div class="mb-4">
                <div class="section-header">Location Information</div>
                <div class="info-row">
                    <div class="info-label">Region:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['region']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Province:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['province']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">City/Municipality:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['city']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Barangay:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($record['barangay']); ?></strong></div>
                </div>
            </div>

            <!-- Suspension Details -->
            <div class="mb-4">
                <div class="section-header">Suspension Details</div>
                <div class="info-row">
                    <div class="info-label">Level:</div>
                    <div class="info-value">
                        <?php
                        $badge_color = match($record['level']) {
                            'Preschool / Kindergarten' => '#198754',
                            'Elementary' => '#0dcaf0',
                            'Secondary / High School' => '#fd7e14',
                            'Senior High School' => '#6f42c1',
                            'Tertiary / College / University' => '#0d6efd',
                            'All Levels' => '#dc3545',
                            default => '#6c757d'
                        };
                        ?>
                        <span class="badge-simple" style="background: <?php echo $badge_color; ?>; color: white;">
                            <?php echo htmlspecialchars($record['level']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Type:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($record['type']); ?></strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Suspension Date:</div>
                    <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($record['suspension_date'])); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Resumption Date:</div>
                    <div class="info-value">
                        <?php if ($record['resumption_date']): ?>
                            <?php echo date('M j, Y g:i A', strtotime($record['resumption_date'])); ?>
                            <span class="badge-simple status-resumed">Resumed</span>
                        <?php else: ?>
                            <span class="badge-simple status-active">Still Suspended</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Additional Information -->
            <div class="mb-4">
                <div class="section-header">Additional Information</div>
                <div class="info-row">
                    <div class="info-label">Reason for Suspension:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['suspension_reason']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Issuing Authority:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['issuing_authority']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Scope of Suspension:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['suspension_scope']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Alternative Delivery:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['alternative_delivery']); ?></div>
                </div>
            </div>

            <?php if (!empty($record['remarks'])): ?>
            <div class="mb-4">
                <div class="section-header">Remarks</div>
                <div class="info-row">
                    <div class="info-value"><?php echo htmlspecialchars($record['remarks']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Record Info -->
            <div class="mb-4">
                <div class="section-header">Record Information</div>
                <?php if (isset($record['full_name'])): ?>
                <div class="info-row">
                    <div class="info-label">Created By:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['full_name']); ?></div>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <div class="info-label">Created:</div>
                    <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($record['created_at'])); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Updated:</div>
                    <div class="info-value"><?php echo date('M j, Y g:i A', strtotime($record['updated_at'])); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print
    </button>
</div>