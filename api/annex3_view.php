<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    echo '<div class="alert alert-danger">Invalid record ID</div>';
    exit;
}

// Fetch record
$is_admin = ($_SESSION['user_role'] === 'admin');

if ($is_admin) {
    $sql = "SELECT a.*, u.full_name 
            FROM annex3_casualties a 
            LEFT JOIN users u ON a.created_by = u.id 
            WHERE a.id = ?";
    $stmt = db_query($sql, [$record_id]);
} else {
    $sql = "SELECT a.*, u.full_name 
            FROM annex3_casualties a 
            LEFT JOIN users u ON a.created_by = u.id 
            WHERE a.id = ? AND a.created_by = ?";
    $stmt = db_query($sql, [$record_id, $_SESSION['user_id']]);
}

$record = $stmt->fetch();

if (!$record) {
    echo '<div class="alert alert-warning">Record not found or access denied.</div>';
    exit;
}

// Log the view action
log_audit_action(
    $_SESSION['user_id'],
    'annex3_view',
    "Viewed casualty record #{$record_id} for {$record['barangay']}"
);
?>

<style>
    .table-view-container { 
        padding: clamp(15px, 3vw, 20px);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: white;
    }
    
    .view-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 6px;
        padding: clamp(12px, 2.5vw, 16px);
        margin-bottom: clamp(15px, 3vw, 20px);
        border-left: 4px solid #0d6efd;
    }
    
    .view-title {
        font-size: clamp(1.1rem, 3vw, 1.3rem);
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }
    
    .view-subtitle {
        font-size: clamp(0.8rem, 2.2vw, 0.9rem);
        color: #6c757d;
        margin: 0;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: clamp(0.8rem, 2.2vw, 0.875rem);
        margin-bottom: clamp(15px, 3vw, 20px);
    }
    
    .data-table:last-child {
        margin-bottom: 0;
    }
    
    .table-section-header {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: clamp(10px, 2.5vw, 12px) clamp(12px, 2.5vw, 15px);
        font-weight: 600;
        color: #495057;
        font-size: clamp(0.9rem, 2.5vw, 1rem);
    }
    
    .table-section-header i {
        color: #0d6efd;
        margin-right: 8px;
        width: 16px;
    }
    
    .data-row {
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
    }
    
    .data-row:last-child {
        border-bottom: 1px solid #dee2e6;
    }
    
    .data-label {
        width: 35%;
        padding: clamp(10px, 2.5vw, 12px) clamp(12px, 2.5vw, 15px);
        background-color: #f8f9fa;
        font-weight: 500;
        color: #495057;
        border-right: 1px solid #dee2e6;
        vertical-align: top;
    }
    
    .data-value {
        padding: clamp(10px, 2.5vw, 12px) clamp(12px, 2.5vw, 15px);
        color: #212529;
        vertical-align: top;
        background: white;
    }
    
    .badge-table {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .badge-dead { background-color: #dc3545; color: white; }
    .badge-injured { background-color: #fd7e14; color: white; }
    .badge-ill { background-color: #0dcaf0; color: white; }
    .badge-missing { background-color: #6c757d; color: white; }
    
    .badge-status {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 0.7rem;
        font-weight: 500;
    }
    
    .badge-validated { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
    .badge-not-validated { background-color: #f8d7da; color: #721c24; border: 1px solid #f1b0b7; }
    
    .text-muted-small {
        font-size: 0.75rem;
        color: #6c757d;
        display: block;
        margin-top: 2px;
    }
    
    .metadata-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px 15px;
        margin-top: 15px;
    }
    
    .meta-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
    }
    
    .meta-label {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .meta-value {
        font-size: 0.75rem;
        color: #495057;
        font-weight: 400;
    }
    
    .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 12px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    
    .btn-action {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .btn-action:hover {
        background-color: #5c636a;
    }
    
    .btn-print {
        background-color: #0d6efd;
    }
    
    .btn-print:hover {
        background-color: #0b5ed7;
    }
    
    /* Print styles */
    @media print {
        .modal-footer {
            display: none !important;
        }
        
        .data-table {
            break-inside: avoid;
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .data-label {
            width: 40%;
        }
        
        .metadata-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 576px) {
        .data-label {
            width: 45%;
            padding: 8px 10px;
        }
        
        .data-value {
            padding: 8px 10px;
        }
    }
</style>

<div class="table-view-container">
    <!-- Header -->
    <div class="view-header">
        <div class="view-title">Casualty Report #<?php echo $record['id']; ?></div>
        <div class="view-subtitle">
            <i class="fas fa-map-marker-alt"></i>
            <?php echo htmlspecialchars($record['barangay']); ?>, 
            <?php echo htmlspecialchars($record['city']); ?>, 
            <?php echo htmlspecialchars($record['province']); ?>
        </div>
    </div>

    <!-- Personal Information Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th colspan="2" class="table-section-header">
                    <i class="fas fa-user"></i>Personal Information
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="data-row">
                <td class="data-label">Full Name</td>
                <td class="data-value">
                    <strong><?php echo htmlspecialchars($record['surname']) ?>, <?php echo htmlspecialchars($record['first_name']) ?></strong>
                    <?php if (!empty($record['middle_name'])): ?>
                        <span class="text-muted-small"><?php echo htmlspecialchars($record['middle_name']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Age & Sex</td>
                <td class="data-value">
                    <strong><?php echo $record['age']; ?> years old</strong>
                    <span class="<?php echo $record['sex'] === 'Male' ? 'text-primary' : 'text-danger'; ?>">
                        / <?php echo $record['sex']; ?>
                    </span>
                </td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Complete Address</td>
                <td class="data-value"><?php echo htmlspecialchars($record['address']); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Incident Details Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th colspan="2" class="table-section-header">
                    <i class="fas fa-exclamation-triangle"></i>Incident Details
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="data-row">
                <td class="data-label">Category</td>
                <td class="data-value">
                    <?php
                    $badge_class = match($record['category']) {
                        'Dead' => 'badge-table badge-dead',
                        'Injured' => 'badge-table badge-injured',
                        'Ill' => 'badge-table badge-ill',
                        'Missing' => 'badge-table badge-missing',
                        default => 'badge-table badge-secondary'
                    };
                    ?>
                    <span class="<?php echo $badge_class; ?>">
                        <i class="fas fa-<?php echo $record['category'] === 'Dead' ? 'skull' : ($record['category'] === 'Injured' ? 'user-injured' : ($record['category'] === 'Ill' ? 'stethoscope' : 'search')); ?> me-1"></i>
                        <?php echo htmlspecialchars($record['category']); ?>
                    </span>
                </td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Cause of Incident</td>
                <td class="data-value"><?php echo htmlspecialchars($record['cause']); ?></td>
            </tr>
            <?php if (!empty($record['remarks'])): ?>
            <tr class="data-row">
                <td class="data-label">Additional Remarks</td>
                <td class="data-value"><?php echo htmlspecialchars($record['remarks']); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Verification & Location Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th colspan="2" class="table-section-header">
                    <i class="fas fa-check-circle"></i>Verification & Location
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="data-row">
                <td class="data-label">Data Source</td>
                <td class="data-value">
                    <i class="fas fa-database me-2 text-primary"></i>
                    <?php echo htmlspecialchars($record['source']); ?>
                </td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Validation Status</td>
                <td class="data-value">
                    <span class="badge-status <?php echo $record['validated'] === 'Yes' ? 'badge-validated' : 'badge-not-validated'; ?>">
                        <i class="fas fa-<?php echo $record['validated'] === 'Yes' ? 'check' : 'times'; ?> me-1"></i>
                        <?php echo $record['validated']; ?>
                    </span>
                </td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Region</td>
                <td class="data-value"><?php echo htmlspecialchars($record['region']); ?></td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Province</td>
                <td class="data-value"><?php echo htmlspecialchars($record['province']); ?></td>
            </tr>
            <tr class="data-row">
                <td class="data-label">City/Municipality</td>
                <td class="data-value"><?php echo htmlspecialchars($record['city']); ?></td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Barangay</td>
                <td class="data-value">
                    <strong><?php echo htmlspecialchars($record['barangay']); ?></strong>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Record Metadata -->
    <div class="metadata-grid">
        <?php if (isset($record['full_name'])): ?>
        <div class="meta-item">
            <span class="meta-label">Created By:</span>
            <span class="meta-value"><?php echo htmlspecialchars($record['full_name']); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="meta-item">
            <span class="meta-label">Date Created:</span>
            <span class="meta-value"><?php echo date('M j, Y g:i A', strtotime($record['created_at'])); ?></span>
        </div>
        
        <?php if ($record['updated_at'] && $record['updated_at'] != $record['created_at']): ?>
        <div class="meta-item">
            <span class="meta-label">Last Updated:</span>
            <span class="meta-value"><?php echo date('M j, Y g:i A', strtotime($record['updated_at'])); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="meta-item">
            <span class="meta-label">Record ID:</span>
            <span class="meta-value">#<?php echo $record['id']; ?></span>
        </div>
    </div>
</div>

<div class="modal-footer">
    <div class="text-muted">
        <small>Viewed: <?php echo date('M j, Y g:i A'); ?></small>
    </div>
    <div class="action-buttons">
        <button type="button" class="btn-action" data-bs-dismiss="modal">
            <i class="fas fa-times"></i>Close
        </button>
        <button type="button" class="btn-action btn-print" onclick="window.print()">
            <i class="fas fa-print"></i>Print
        </button>
    </div>
</div>

<script>
// Enhanced print functionality
document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.querySelector('.btn-print');
    
    printBtn.addEventListener('click', function() {
        // Add a small delay to ensure DOM is ready
        setTimeout(() => {
            window.print();
        }, 100);
    });
});
</script>