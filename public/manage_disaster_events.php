<?php
/**
 * Disaster Events Management
 * Clean and simple design following annex5_records pattern
 */

// Define page title
define('PAGE_TITLE', 'Manage Disaster Events');

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check authentication and authorization
require_login();

// Check if user has admin or special_access role
if (!in_array($_SESSION['user_role'], ['admin', 'special_access'])) {
    set_flash('Access Denied. You do not have permission to access Disaster Events Management.', 'error');
    header("Location: dashboard.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Verification
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        set_flash('Security token validation failed', 'error');
        header('Location: manage_disaster_events.php');
        exit;
    }

    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $sql = "INSERT INTO disaster_events
                            (event_name, event_type, start_date, end_date, affected_region,
                             affected_province, affected_city, status, description, created_by)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $_POST['event_name'],
                        $_POST['event_type'],
                        $_POST['start_date'],
                        $_POST['end_date'] ?: null,
                        $_POST['affected_region'],
                        $_POST['affected_province'],
                        $_POST['affected_city'],
                        $_POST['status'],
                        $_POST['description'],
                        $_SESSION['user_id']
                    ]);
                    set_flash("Disaster event created successfully!", 'success');
                    log_audit_action($_SESSION['user_id'], 'disaster_event_created', "Created event: {$_POST['event_name']}");
                    break;

                case 'update':
                    $sql = "UPDATE disaster_events
                            SET event_name = ?, event_type = ?, start_date = ?, end_date = ?,
                                affected_region = ?, affected_province = ?, affected_city = ?,
                                status = ?, description = ?
                            WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $_POST['event_name'],
                        $_POST['event_type'],
                        $_POST['start_date'],
                        $_POST['end_date'] ?: null,
                        $_POST['affected_region'],
                        $_POST['affected_province'],
                        $_POST['affected_city'],
                        $_POST['status'],
                        $_POST['description'],
                        $_POST['event_id']
                    ]);
                    set_flash("Disaster event updated successfully!", 'success');
                    log_audit_action($_SESSION['user_id'], 'disaster_event_updated', "Updated event ID: {$_POST['event_id']}");
                    break;

                case 'delete':
                    $sql = "DELETE FROM disaster_events WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_POST['event_id']]);
                    set_flash("Disaster event deleted successfully!", 'success');
                    log_audit_action($_SESSION['user_id'], 'disaster_event_deleted', "Deleted event ID: {$_POST['event_id']}");
                    break;

                case 'set_active':
                    // Get event name first
                    $sql = "SELECT event_name FROM disaster_events WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_POST['event_id']]);
                    $event = $stmt->fetch();

                    // Update system settings
                    $sql = "UPDATE system_settings SET setting_value = ?, last_updated_by = ? WHERE setting_key = 'active_disaster_event_id'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_POST['event_id'], $_SESSION['user_id']]);

                    // Update event name cache
                    $sql = "UPDATE system_settings SET setting_value = ?, last_updated_by = ? WHERE setting_key = 'active_disaster_event_name'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$event['event_name'], $_SESSION['user_id']]);

                    set_flash("System-wide active event set to: " . $event['event_name'], 'success');
                    log_audit_action($_SESSION['user_id'], 'active_event_set', "Set active event: {$event['event_name']}");
                    break;

                case 'clear_active':
                    $sql = "UPDATE system_settings SET setting_value = NULL, last_updated_by = ? WHERE setting_key IN ('active_disaster_event_id', 'active_disaster_event_name')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_SESSION['user_id']]);

                    set_flash("System-wide active event cleared", 'success');
                    log_audit_action($_SESSION['user_id'], 'active_event_cleared', "Cleared active disaster event");
                    break;
            }
        }
        header("Location: manage_disaster_events.php");
        exit();
    } catch (Exception $e) {
        set_flash("Error: " . $e->getMessage(), 'error');
        header("Location: manage_disaster_events.php");
        exit();
    }
}

// Get system-wide active event
$sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'active_disaster_event_id'";
$result = $pdo->query($sql);
$setting = $result->fetch();
$systemActiveEventId = $setting['setting_value'] ?? null;

// Get active event details
$activeEventData = null;
if ($systemActiveEventId) {
    $sql = "SELECT * FROM disaster_events WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$systemActiveEventId]);
    $activeEventData = $stmt->fetch();
}

// Get all disaster events
$sql = "SELECT de.*, u.full_name as created_by_name
        FROM disaster_events de
        LEFT JOIN users u ON de.created_by = u.id
        ORDER BY de.start_date DESC";
$events = $pdo->query($sql)->fetchAll();

$csrf_token = generate_token();

// Include header with sidenav
include '../includes/sidenav.php';
?>

<style>
.event-card {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}
.event-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.event-card.active-event {
    border-left-color: #0d6efd;
    background-color: #f0f7ff;
}
.badge-lg {
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
}
</style>

<div class="container-fluid px-4 py-3">
    <?php show_flash(); ?>

    <!-- Active Event Alert -->
    <?php if ($activeEventData): ?>
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        <i class="bi bi-broadcast-tower me-2"></i>
        <strong>Active Event:</strong> <?= htmlspecialchars($activeEventData['event_name']) ?>
        <span class="text-muted ms-2">(All users will link entries to this event)</span>
        <form method="POST" style="display: inline;" class="float-end">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="clear_active">
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-x-circle"></i> Clear
            </button>
        </form>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>No Active Event Set</strong> - Annex entries will not be linked to any event. Click "Set Active" on an event below.
    </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-calendar-event me-2"></i>Disaster Events Management
            </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createEventModal">
                <i class="bi bi-plus-lg me-1"></i> Create New Event
            </button>
        </div>
    </div>

    <!-- Events List -->
    <?php if (count($events) > 0): ?>
    <div class="row">
        <?php foreach ($events as $event):
            $isActive = ($systemActiveEventId && $systemActiveEventId == $event['id']);
            $statusColor = match($event['status']) {
                'Ongoing' => 'danger',
                'Ended' => 'success',
                default => 'secondary'
            };
        ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card event-card h-100 <?= $isActive ? 'active-event' : '' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0 fw-bold"><?= htmlspecialchars($event['event_name']) ?></h6>
                        <span class="badge bg-<?= $statusColor ?> badge-lg"><?= $event['status'] ?></span>
                    </div>

                    <?php if ($isActive): ?>
                    <div class="alert alert-info py-1 px-2 mb-2" style="font-size: 0.8rem;">
                        <i class="bi bi-star-fill"></i> Currently Active
                    </div>
                    <?php endif; ?>

                    <p class="text-muted small mb-2">
                        <i class="bi bi-exclamation-diamond me-1"></i><?= htmlspecialchars($event['event_type']) ?>
                    </p>

                    <p class="small mb-2">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('M d, Y', strtotime($event['start_date'])) ?>
                        <?php if ($event['end_date']): ?>
                        - <?= date('M d, Y', strtotime($event['end_date'])) ?>
                        <?php endif; ?>
                    </p>

                    <p class="small mb-2">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= htmlspecialchars($event['affected_city'] . ', ' . $event['affected_province']) ?>
                    </p>

                    <?php if ($event['description']): ?>
                    <p class="small text-muted mb-3">
                        <?= htmlspecialchars(substr($event['description'], 0, 80)) ?>
                        <?= strlen($event['description']) > 80 ? '...' : '' ?>
                    </p>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <?php if (!$isActive): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="set_active">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="bi bi-check-circle"></i> Set as Active
                            </button>
                        </form>
                        <?php endif; ?>

                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary btn-sm"
                                    onclick='editEvent(<?= json_encode($event) ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteEvent(<?= $event['id'] ?>, '<?= addslashes($event['event_name']) ?>')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>No disaster events found. Create your first event to start linking Annex data.
    </div>
    <?php endif; ?>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Create Disaster Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include '../includes/event_form_fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="editEventForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="event_id" id="edit_event_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Disaster Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php include '../includes/event_form_fields.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form method="POST" id="deleteEventForm" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="event_id" id="delete_event_id">
</form>

<script>
function editEvent(event) {
    document.getElementById('edit_event_id').value = event.id;
    document.getElementById('editEventForm').querySelector('[name="event_name"]').value = event.event_name;
    document.getElementById('editEventForm').querySelector('[name="event_type"]').value = event.event_type;
    document.getElementById('editEventForm').querySelector('[name="start_date"]').value = event.start_date;
    document.getElementById('editEventForm').querySelector('[name="end_date"]').value = event.end_date || '';
    document.getElementById('editEventForm').querySelector('[name="affected_region"]').value = event.affected_region;
    document.getElementById('editEventForm').querySelector('[name="affected_province"]').value = event.affected_province;
    document.getElementById('editEventForm').querySelector('[name="affected_city"]').value = event.affected_city;
    document.getElementById('editEventForm').querySelector('[name="status"]').value = event.status;
    document.getElementById('editEventForm').querySelector('[name="description"]').value = event.description || '';

    new bootstrap.Modal(document.getElementById('editEventModal')).show();
}

function deleteEvent(id, name) {
    if (confirm(`Are you sure you want to delete the event: "${name}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('delete_event_id').value = id;
        document.getElementById('deleteEventForm').submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
