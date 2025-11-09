<?php
/**
 * API Handler: View Families Assisted Record Details (Annex 19)
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
                FROM annex19_families_assisted a 
                LEFT JOIN users u ON a.created_by = u.id 
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT a.*, u.full_name 
                FROM annex19_families_assisted a 
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
        'annex19_view',
        "Viewed families assisted record #{$record_id} for {$record['barangay']}"
    );
    
    // SECURITY LOG: Record view
    log_security_event(
        $_SESSION['user_id'],
        'record_view',
        "Viewed Annex 19 record #{$record_id}"
    );
    
} catch (PDOException $e) {
    error_log("Annex19 View Error: " . $e->getMessage());
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
    .progress {
        height: 8px;
        margin-top: 5px;
    }
</style>

<div class="view-container">
    <!-- Header -->
    <div class="mb-4">
        <h5 class="mb-2">Families Assisted Report #<?php echo $record['id']; ?></h5>
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

            <!-- Assistance Details -->
            <div class="mb-4">
                <div class="section-header">Assistance Details</div>
                <div class="info-row">
                    <div class="info-label">Assistance Type:</div>
                    <div class="info-value">
                        <?php
                        $badge_color = match($record['assistance_type']) {
                            'Food Packs' => '#198754',
                            'Hygiene Kits' => '#0dcaf0',
                            'Shelter Materials' => '#fd7e14',
                            'Medical Aid' => '#dc3545',
                            'Cash Assistance' => '#198754',
                            'Other' => '#6c757d',
                            default => '#6c757d'
                        };
                        ?>
                        <span class="badge-simple" style="background: <?php echo $badge_color; ?>; color: white;">
                            <?php echo htmlspecialchars($record['assistance_type']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Assistance Provider:</div>
                    <div class="info-value">
                        <span class="badge-simple" style="background: #6f42c1; color: white;">
                            <?php echo htmlspecialchars($record['assistance_provider']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Statistics -->
            <div class="mb-4">
                <div class="section-header">Assistance Statistics</div>
                <div class="info-row">
                    <div class="info-label">Families Requiring Assistance:</div>
                    <div class="info-value highlight"><?php echo number_format($record['families_requiring_assistance']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Families Assisted:</div>
                    <div class="info-value highlight"><?php echo number_format($record['families_assisted']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Percentage Assisted:</div>
                    <div class="info-value">
                        <strong><?php echo number_format($record['percentage_assisted'], 2); ?>%</strong>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?php echo min($record['percentage_assisted'], 100); ?>%" 
                                 aria-valuenow="<?php echo $record['percentage_assisted']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <?php if (!empty($record['remarks'])): ?>
            <div class="mb-4">
                <div class="section-header">Remarks</div>
                <div class="info-row">
                    <div class="info-value">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($record['remarks'])); ?></p>
                    </div>
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