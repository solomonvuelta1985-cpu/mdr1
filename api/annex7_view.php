<?php
/**
 * API Handler: View Other Assets Damage Record Details (Annex 7)
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
                FROM annex7_other_assets_damage a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT a.*, u.full_name
                FROM annex7_other_assets_damage a
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

    // Log the view action - MOVED AFTER VARIABLES ARE DEFINED
    log_audit_action(
        $_SESSION['user_id'],
        'annex7_view',
        "Viewed other assets damage record #{$record_id} for {$record['barangay']}"
    );

    // SECURITY LOG: Record view
    log_security_event(
        $_SESSION['user_id'],
        'record_view',
        "Viewed Annex 7 record #{$record_id}"
    );

} catch (PDOException $e) {
    error_log("Annex6 View Error: " . $e->getMessage());
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
    .damage {
        font-size: 1.2rem;
        font-weight: 600;
        color: #dc3545;
    }
</style>

<div class="view-container">
    <!-- Header -->
    <div class="mb-4">
        <h5 class="mb-2">Other Assets Damage Report #<?php echo $record['id']; ?></h5>
        <small class="text-muted">
            <?php echo htmlspecialchars($record['barangay']); ?>,
            <?php echo htmlspecialchars($record['city']); ?>,
            <?php echo htmlspecialchars($record['province']); ?>
        </small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Asset Details -->
            <div class="mb-4">
                <div class="section-header">Asset Details</div>
                <div class="info-row">
                    <div class="info-label">Classification:</div>
                    <div class="info-value">
                        <span class="badge-simple" style="background: #0d6efd; color: white;">
                            <?php echo htmlspecialchars($record['classification']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Particulars:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($record['particulars'] ?: 'Not specified'); ?></strong></div>
                </div>
            </div>

            <!-- Quantity and Cost -->
            <div class="mb-4">
                <div class="section-header">Quantity and Cost</div>
                <div class="info-row">
                    <div class="info-label">Unit:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['unit'] ?: 'Not specified'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Quantity:</div>
                    <div class="info-value highlight"><?php echo number_format($record['quantity'], 2); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Estimated Cost:</div>
                    <div class="info-value damage"><?php echo htmlspecialchars($record['cost'] ?: 'Not specified'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Remarks -->
            <div class="mb-4">
                <div class="section-header">Remarks</div>
                <div class="info-row">
                    <div class="info-label">Remarks:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['remarks'] ?: 'No remarks'); ?></div>
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
