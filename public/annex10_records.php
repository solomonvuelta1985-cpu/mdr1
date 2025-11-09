<?php
/**
 * Annex 10: Status of Communication Lines - Records Page
 * Clean table-based view
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

// Get all records
if ($is_admin) {
    $stmt = db_query("
        SELECT c.*, u.full_name, u.username, u.barangay as user_barangay
        FROM annex10_communication_lines c
        LEFT JOIN users u ON c.created_by = u.id
        WHERE c.is_archived = 0
        ORDER BY c.reported_time DESC, c.created_at DESC
    ");
} else {
    $stmt = db_query("
        SELECT * FROM annex10_communication_lines
        WHERE created_by = ? AND is_archived = 0
        ORDER BY reported_time DESC, created_at DESC
    ", [$user_id]);
}

$records = $stmt ? $stmt->fetchAll() : [];

ob_start();
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .records-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: clamp(20px, 4vw, 30px);
    }

    .header-section {
        text-align: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 15px;
    }

    .header-section h1 {
        font-size: clamp(1.3rem, 3.5vw, 1.6rem);
        font-weight: 600;
        color: #212529;
    }

    .table-container {
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }

    .table {
        margin-bottom: 0;
        font-size: 0.875rem;
    }

    .table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 12px;
        vertical-align: middle;
    }

    .table tbody td {
        padding: 10px 12px;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-normal {
        background: #d1e7dd;
        color: #0f5132;
    }

    .badge-disrupted {
        background: #f8d7da;
        color: #842029;
    }

    .badge-partial {
        background: #fff3cd;
        color: #664d03;
    }

    .badge-offline {
        background: #dc3545;
        color: white;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 0.8rem;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="container-fluid mt-4">
    <div class="records-container">
        <!-- Header -->
        <div class="header-section">
            <h1>
                <i class="fas fa-signal me-2"></i>
                Annex 10: Status of Communication Lines Records
            </h1>
        </div>

        <?php show_flash(); ?>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4 flex-wrap gap-3">
            <a href="annex10.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Report
            </a>
            <div class="d-flex gap-2">
                <a href="annex10_archived.php" class="btn btn-outline-warning">
                    <i class="fas fa-archive me-2"></i>View Archived
                </a>
            </div>
        </div>

        <!-- Records Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Barangay</th>
                        <th>Communication Type</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Reported Time</th>
                        <th>Restored Time</th>
                        <?php if ($is_admin): ?>
                        <th>Created By</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="<?php echo $is_admin ? '9' : '8'; ?>" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No communication line status reports found</p>
                            <a href="annex10.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create First Report
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                        <tr>
                            <td>#<?php echo $record['id']; ?></td>
                            <td><?php echo htmlspecialchars($record['barangay']); ?></td>
                            <td><?php echo htmlspecialchars($record['communication_type']); ?></td>
                            <td><?php echo htmlspecialchars($record['provider']); ?></td>
                            <td>
                                <?php
                                $badge_class = 'badge-normal';
                                switch ($record['status']) {
                                    case 'Disrupted':
                                        $badge_class = 'badge-disrupted';
                                        break;
                                    case 'Partially Disrupted':
                                        $badge_class = 'badge-partial';
                                        break;
                                    case 'Offline':
                                        $badge_class = 'badge-offline';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($record['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($record['reported_time'])); ?></td>
                            <td>
                                <?php echo $record['restored_time'] ? date('M d, Y h:i A', strtotime($record['restored_time'])) : '<span class="text-muted">Ongoing</span>'; ?>
                            </td>
                            <?php if ($is_admin): ?>
                            <td><?php echo htmlspecialchars($record['full_name'] ?? $record['username'] ?? 'N/A'); ?></td>
                            <?php endif; ?>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-info btn-sm" onclick="viewRecord(<?php echo $record['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="annex10_edit.php?id=<?php echo $record['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-secondary btn-sm" onclick="if(confirm('Archive this record?')) location.href='annex10_archive.php?id=<?php echo $record['id']; ?>'">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Communication Line Status Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function viewRecord(id) {
    const record = <?php echo json_encode($records); ?>.find(r => r.id === id);
    if (!record) return;

    const statusBadge = {
        'Normal': '<span class="badge badge-normal">Normal</span>',
        'Disrupted': '<span class="badge badge-disrupted">Disrupted</span>',
        'Partially Disrupted': '<span class="badge badge-partial">Partially Disrupted</span>',
        'Offline': '<span class="badge badge-offline">Offline</span>'
    };

    document.getElementById('modalContent').innerHTML = `
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Record ID</th>
                    <td>#${record.id}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>${record.barangay}, ${record.city}, ${record.province}, ${record.region}</td>
                </tr>
                <tr>
                    <th>Communication Type</th>
                    <td>${record.communication_type}</td>
                </tr>
                <tr>
                    <th>Service Provider</th>
                    <td>${record.provider}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>${statusBadge[record.status]}</td>
                </tr>
                <tr>
                    <th>Affected Area</th>
                    <td>${record.affected_area || 'N/A'}</td>
                </tr>
                <tr>
                    <th>Reported Time</th>
                    <td>${new Date(record.reported_time).toLocaleString()}</td>
                </tr>
                <tr>
                    <th>Restored Time</th>
                    <td>${record.restored_time ? new Date(record.restored_time).toLocaleString() : '<span class="text-muted">Ongoing</span>'}</td>
                </tr>
                <tr>
                    <th>Cause of Disruption</th>
                    <td>${record.cause_of_disruption || 'N/A'}</td>
                </tr>
                <tr>
                    <th>Remarks</th>
                    <td>${record.remarks || 'N/A'}</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>${record.full_name || record.username || 'N/A'}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>${new Date(record.created_at).toLocaleString()}</td>
                </tr>
            </table>
        </div>
    `;

    new bootstrap.Modal(document.getElementById('viewModal')).show();
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/sidenav.php';
?>
