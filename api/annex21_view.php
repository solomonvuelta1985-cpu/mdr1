<?php
/**
 * API Handler: View Assistance Provided to LGUs Record Details (Annex 21)
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
                FROM annex21_assistance_lgu a 
                LEFT JOIN users u ON a.created_by = u.id 
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT a.*, u.full_name 
                FROM annex21_assistance_lgu a 
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
        'annex21_view',
        "Viewed assistance to LGUs record #{$record_id} for {$record['recipient']}"
    );
    
    // SECURITY LOG: Record view
    log_security_event(
        $_SESSION['user_id'],
        'record_view',
        "Viewed Annex 21 record #{$record_id}"
    );
    
} catch (PDOException $e) {
    error_log("Annex21 View Error: " . $e->getMessage());
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
    .highlight {
        font-size: 1.2rem;
        font-weight: 600;
        color: #198754;
    }
    .amount {
        font-size: 1.2rem;
        font-weight: 600;
        color: #0d6efd;
    }
</style>

<div class="view-container">
    <!-- Header -->
    <div class="mb-4">
        <h5 class="mb-2">Assistance to LGUs Report #<?php echo $record['id']; ?></h5>
        <small class="text-muted">
            <?php echo htmlspecialchars($record['recipient']); ?> - 
            <?php echo htmlspecialchars($record['barangay']); ?>, 
            <?php echo htmlspecialchars($record['city']); ?>, 
            <?php echo htmlspecialchars($record['province']); ?>
        </small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Location and Recipient -->
            <div class="mb-4">
                <div class="section-header">Location and Recipient</div>
                <div class="info-row">
                    <div class="info-label">Recipient:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($record['recipient']); ?></strong></div>
                </div>
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
                    <div class="info-value"><?php echo htmlspecialchars($record['barangay']); ?></div>
                </div>
            </div>

            <!-- Assistance Details -->
            <div class="mb-4">
                <div class="section-header">Assistance Details</div>
                <div class="info-row">
                    <div class="info-label">Cluster:</div>
                    <div class="info-value">
                        <?php
                        $badge_color = match($record['cluster']) {
                            'Food' => '#198754',
                            'WASH' => '#0dcaf0',
                            'Shelter' => '#fd7e14',
                            'Health' => '#dc3545',
                            'Protection' => '#6f42c1',
                            'Education' => '#20c997',
                            'Nutrition' => '#e83e8c',
                            'Camp Management' => '#ffc107',
                            'Emergency Telecommunications' => '#6610f2',
                            'Logistics' => '#6c757d',
                            default => '#6c757d'
                        };
                        ?>
                        <span class="badge-simple" style="background: <?php echo $badge_color; ?>; color: white;">
                            <?php echo htmlspecialchars($record['cluster']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Type:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($record['type']); ?></strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Quantity:</div>
                    <div class="info-value highlight"><?php echo number_format($record['quantity']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Unit:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['unit']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Financial Details -->
            <div class="mb-4">
                <div class="section-header">Financial Details</div>
                <div class="info-row">
                    <div class="info-label">Cost per Unit:</div>
                    <div class="info-value">₱<?php echo number_format($record['cost_per_unit'], 2); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Amount:</div>
                    <div class="info-value amount">₱<?php echo number_format($record['amount'], 2); ?></div>
                </div>
            </div>

            <!-- Source Information -->
            <div class="mb-4">
                <div class="section-header">Source Information</div>
                <div class="info-row">
                    <div class="info-label">Source:</div>
                    <div class="info-value">
                        <?php
                        $source_badge_color = match($record['source']) {
                            'DSWD' => '#198754',
                            'DOH' => '#dc3545',
                            'OCD' => '#0d6efd',
                            'DPWH' => '#fd7e14',
                            'LGU' => '#6f42c1',
                            'NGO' => '#20c997',
                            'Private Sector' => '#6610f2',
                            'Foreign Aid' => '#e83e8c',
                            'UN Agencies' => '#ffc107',
                            'Other Government Agency' => '#6c757d',
                            default => '#6c757d'
                        };
                        ?>
                        <span class="badge-simple" style="background: <?php echo $source_badge_color; ?>; color: white;">
                            <?php echo htmlspecialchars($record['source']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Remarks:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['remarks'] ?? 'No remarks provided'); ?></div>
                </div>
            </div>

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