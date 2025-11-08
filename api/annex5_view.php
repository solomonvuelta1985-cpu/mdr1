<?php
/**
 * API Handler: View Agriculture Damage Record Details (Annex 5)
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
                FROM annex5_agriculture_damage a 
                LEFT JOIN users u ON a.created_by = u.id 
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
    } else {
        $sql = "SELECT a.*, u.full_name 
                FROM annex5_agriculture_damage a 
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
        'annex5_view',
        "Viewed agriculture damage record #{$record_id} for {$record['barangay']}"
    );
    
    // SECURITY LOG: Record view
    log_security_event(
        $_SESSION['user_id'],
        'record_view',
        "Viewed Annex 5 record #{$record_id}"
    );
    
} catch (PDOException $e) {
    error_log("Annex5 View Error: " . $e->getMessage());
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
        <h5 class="mb-2">Agriculture Damage Report #<?php echo $record['id']; ?></h5>
        <small class="text-muted">
            <?php echo htmlspecialchars($record['barangay']); ?>, 
            <?php echo htmlspecialchars($record['city']); ?>, 
            <?php echo htmlspecialchars($record['province']); ?>
        </small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Classification -->
            <div class="mb-4">
                <div class="section-header">Classification</div>
                <div class="info-row">
                    <div class="info-label">Classification:</div>
                    <div class="info-value">
                        <?php
                        $badge_color = match($record['classification']) {
                            'Crops' => '#198754',
                            'Livestock and Poultry' => '#fd7e14',
                            'Fisheries' => '#0dcaf0',
                            'Agricultural Infrastructure' => '#6f42c1',
                            'Machineries and Equipment' => '#6c757d',
                            default => '#6c757d'
                        };
                        ?>
                        <span class="badge-simple" style="background: <?php echo $badge_color; ?>; color: white;">
                            <?php echo htmlspecialchars($record['classification']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Type:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($record['type']); ?></strong></div>
                </div>
            </div>

            <!-- Impact -->
            <div class="mb-4">
                <div class="section-header">Impact Summary</div>
                <div class="info-row">
                    <div class="info-label">Affected People:</div>
                    <div class="info-value highlight"><?php echo number_format($record['affected_people']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Damage Value:</div>
                    <div class="info-value damage">₱<?php echo number_format($record['damage_value'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Specific Details -->
            <?php if ($record['classification'] === 'Crops'): ?>
            <div class="mb-4">
                <div class="section-header">Crops Details</div>
                <div class="info-row">
                    <div class="info-label">No Recovery Area:</div>
                    <div class="info-value"><?php echo number_format($record['no_recovery_area'] ?? 0, 2); ?> ha</div>
                </div>
                <div class="info-row">
                    <div class="info-label">With Recovery Area:</div>
                    <div class="info-value"><?php echo number_format($record['with_recovery_area'] ?? 0, 2); ?> ha</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Area:</div>
                    <div class="info-value"><strong><?php echo number_format($record['total_crop_area'] ?? 0, 2); ?> ha</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Production Loss:</div>
                    <div class="info-value"><strong><?php echo number_format($record['production_loss_volume'] ?? 0, 2); ?> MT</strong></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($record['classification'] === 'Livestock and Poultry'): ?>
            <div class="mb-4">
                <div class="section-header">Livestock Details</div>
                <div class="info-row">
                    <div class="info-label">Number of Heads:</div>
                    <div class="info-value highlight"><?php echo number_format($record['animal_heads'] ?? 0); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($record['classification'] === 'Fisheries'): ?>
            <div class="mb-4">
                <div class="section-header">Fisheries Details</div>
                <div class="info-row">
                    <div class="info-label">Impact Details:</div>
                    <div class="info-value"><?php echo htmlspecialchars($record['fisheries_details'] ?? 'No details provided'); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array($record['classification'], ['Agricultural Infrastructure', 'Machineries and Equipment'])): ?>
            <div class="mb-4">
                <div class="section-header">Damage Details</div>
                <div class="info-row">
                    <div class="info-label">Totally Damaged:</div>
                    <div class="info-value"><?php echo number_format($record['totally_damaged'] ?? 0); ?> units</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Partially Damaged:</div>
                    <div class="info-value"><?php echo number_format($record['partially_damaged'] ?? 0); ?> units</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Damaged:</div>
                    <div class="info-value"><strong><?php echo number_format($record['total_damaged'] ?? 0); ?> units</strong></div>
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