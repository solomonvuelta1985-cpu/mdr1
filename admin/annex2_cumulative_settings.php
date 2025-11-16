<?php
// Define page title
define('PAGE_TITLE', 'Annex 2 Cumulative Settings');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_admin();

// Get barangay list
$barangays = get_baggao_barangays();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_token($_POST['csrf_token'])) {
        set_flash('Security token validation failed. Please try again.', 'error');
        header('Location: annex2_cumulative_settings.php');
        exit;
    }

    $barangay = sanitize($_POST['barangay'] ?? '');
    $affected_families_cumulative = (int)($_POST['affected_families_cumulative'] ?? 0);
    $affected_persons_cumulative = (int)($_POST['affected_persons_cumulative'] ?? 0);
    $num_ecs_cumulative = (int)($_POST['num_ecs_cumulative'] ?? 0);
    $inside_families_cumulative = (int)($_POST['inside_families_cumulative'] ?? 0);
    $inside_persons_cumulative = (int)($_POST['inside_persons_cumulative'] ?? 0);
    $outside_families_cumulative = (int)($_POST['outside_families_cumulative'] ?? 0);
    $outside_persons_cumulative = (int)($_POST['outside_persons_cumulative'] ?? 0);

    // Auto-calculate totals (sum of inside and outside)
    $total_families_cumulative = $inside_families_cumulative + $outside_families_cumulative;
    $total_persons_cumulative = $inside_persons_cumulative + $outside_persons_cumulative;

    // Validation
    $errors = [];
    if (empty($barangay)) {
        $errors[] = 'Barangay is required';
    }
    $fields = [
        'affected_families_cumulative' => $affected_families_cumulative,
        'affected_persons_cumulative' => $affected_persons_cumulative,
        'num_ecs_cumulative' => $num_ecs_cumulative,
        'inside_families_cumulative' => $inside_families_cumulative,
        'inside_persons_cumulative' => $inside_persons_cumulative,
        'outside_families_cumulative' => $outside_families_cumulative,
        'outside_persons_cumulative' => $outside_persons_cumulative,
        'total_families_cumulative' => $total_families_cumulative,
        'total_persons_cumulative' => $total_persons_cumulative,
    ];
    foreach ($fields as $field => $value) {
        if ($value < 0) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be non-negative';
        }
    }

    if (empty($errors)) {
        try {
            // Insert or update
            $stmt = $pdo->prepare("
                INSERT INTO annex2_cumulative_settings 
                (barangay, affected_families_cumulative, affected_persons_cumulative, num_ecs_cumulative, 
                 inside_families_cumulative, inside_persons_cumulative, outside_families_cumulative, 
                 outside_persons_cumulative, total_families_cumulative, total_persons_cumulative, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                affected_families_cumulative = VALUES(affected_families_cumulative),
                affected_persons_cumulative = VALUES(affected_persons_cumulative),
                num_ecs_cumulative = VALUES(num_ecs_cumulative),
                inside_families_cumulative = VALUES(inside_families_cumulative),
                inside_persons_cumulative = VALUES(inside_persons_cumulative),
                outside_families_cumulative = VALUES(outside_families_cumulative),
                outside_persons_cumulative = VALUES(outside_persons_cumulative),
                total_families_cumulative = VALUES(total_families_cumulative),
                total_persons_cumulative = VALUES(total_persons_cumulative),
                updated_at = NOW()
            ");
            $stmt->execute([
                $barangay, $affected_families_cumulative, $affected_persons_cumulative, $num_ecs_cumulative,
                $inside_families_cumulative, $inside_persons_cumulative, $outside_families_cumulative,
                $outside_persons_cumulative, $total_families_cumulative, $total_persons_cumulative,
                $_SESSION['user_id']
            ]);

            log_audit_action($_SESSION['user_id'], 'annex2_cumulative_settings_update', "Updated cumulative settings for barangay: $barangay");
            set_flash("Cumulative settings for <strong>$barangay</strong> updated successfully.", 'success');
        } catch (PDOException $e) {
            error_log("Annex2 cumulative settings error: " . $e->getMessage());
            set_flash('Failed to update cumulative settings. Please try again.', 'error');
        }
    } else {
        set_flash('Validation errors: ' . implode(', ', $errors), 'error');
    }

    header('Location: annex2_cumulative_settings.php');
    exit;
}

// Get existing settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM annex2_cumulative_settings ORDER BY barangay");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['barangay']] = $row;
}

$csrf_token = generate_token();

// Include sidenav
include '../includes/sidenav.php';
?>

<style>
/* Enhanced styling for better UX */
.form-control-lg {
    height: 48px;
    font-size: 1.1rem;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

#data-entry-section {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Highlight totals */
#total_families_cumulative,
#total_persons_cumulative {
    cursor: not-allowed;
}

/* Table sticky header */
.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}

.table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #f8f9fa;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .form-control-lg {
        height: 42px;
        font-size: 1rem;
    }
}
</style>

<div class="container-fluid px-4 py-3">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-calculator me-2"></i>Annex 2 Cumulative Settings
            </h5>
        </div>
        <div class="card-body">
                    <?php show_flash(); ?>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instructions:</strong> Select a barangay and enter the cumulative baseline values. These values will be used in the Annex 2 Affected Population Report.
                    </div>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <!-- Barangay Selection - Prominent -->
                        <div class="card mb-4 border-primary">
                            <div class="card-body bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-0 text-primary">
                                            <i class="fas fa-map-marker-alt me-2"></i>Step 1: Select Barangay
                                        </h5>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-select form-select-lg" id="barangay" name="barangay" required onchange="loadSettings()">
                                            <option value="">-- Choose a Barangay --</option>
                                            <?php foreach ($barangays as $barangay): ?>
                                                <option value="<?= htmlspecialchars($barangay) ?>"><?= htmlspecialchars($barangay) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Entry Section -->
                        <div id="data-entry-section" style="display: none;">
                            <h5 class="mb-3 text-primary">
                                <i class="fas fa-edit me-2"></i>Step 2: Enter Cumulative Data
                            </h5>

                            <!-- Section 1: Affected Population -->
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Affected Population</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="affected_families_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-home me-1"></i>Affected Families
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="affected_families_cumulative" name="affected_families_cumulative" min="0" value="0" placeholder="Enter number">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="affected_persons_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-user me-1"></i>Affected Persons
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="affected_persons_cumulative" name="affected_persons_cumulative" min="0" value="0" placeholder="Enter number">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="num_ecs_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-building me-1"></i>Number of ECs
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="num_ecs_cumulative" name="num_ecs_cumulative" min="0" value="0" placeholder="Enter number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Inside Evacuation Centers -->
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-house-user me-2"></i>Inside Evacuation Centers</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="inside_families_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-home me-1"></i>Families Inside ECs
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="inside_families_cumulative" name="inside_families_cumulative" min="0" value="0" oninput="calculateTotals()" placeholder="Enter number">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inside_persons_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-user me-1"></i>Persons Inside ECs
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="inside_persons_cumulative" name="inside_persons_cumulative" min="0" value="0" oninput="calculateTotals()" placeholder="Enter number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Outside Evacuation Centers -->
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fas fa-campground me-2"></i>Outside Evacuation Centers</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="outside_families_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-home me-1"></i>Families Outside ECs
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="outside_families_cumulative" name="outside_families_cumulative" min="0" value="0" oninput="calculateTotals()" placeholder="Enter number">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="outside_persons_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-user me-1"></i>Persons Outside ECs
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="outside_persons_cumulative" name="outside_persons_cumulative" min="0" value="0" oninput="calculateTotals()" placeholder="Enter number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Total Displaced (Auto-Calculated) -->
                            <div class="card mb-4 shadow-sm border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calculator me-2"></i>Total Displaced (Auto-Calculated)
                                        <span class="badge bg-light text-dark ms-2">Automatic</span>
                                    </h5>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="total_families_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-calculator me-1"></i>Total Displaced Families
                                            </label>
                                            <input type="number" class="form-control form-control-lg bg-white border-primary" id="total_families_cumulative" name="total_families_cumulative" min="0" value="0" readonly style="font-size: 1.5rem; font-weight: bold; color: #0d6efd;">
                                            <small class="text-muted"><i class="fas fa-info-circle"></i> Inside Families + Outside Families</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="total_persons_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-calculator me-1"></i>Total Displaced Persons
                                            </label>
                                            <input type="number" class="form-control form-control-lg bg-white border-primary" id="total_persons_cumulative" name="total_persons_cumulative" min="0" value="0" readonly style="font-size: 1.5rem; font-weight: bold; color: #0d6efd;">
                                            <small class="text-muted"><i class="fas fa-info-circle"></i> Inside Persons + Outside Persons</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Save Cumulative Settings
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-5">

                    <!-- Overview Table -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-table me-2"></i>All Barangays - Settings Overview</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="sticky-col" style="min-width: 150px;">Barangay</th>
                                            <th class="text-center" title="Affected Families">Aff. Fam</th>
                                            <th class="text-center" title="Affected Persons">Aff. Per</th>
                                            <th class="text-center" title="Number of ECs">ECs</th>
                                            <th class="text-center bg-info bg-opacity-10" title="Inside Families">In Fam</th>
                                            <th class="text-center bg-info bg-opacity-10" title="Inside Persons">In Per</th>
                                            <th class="text-center bg-warning bg-opacity-10" title="Outside Families">Out Fam</th>
                                            <th class="text-center bg-warning bg-opacity-10" title="Outside Persons">Out Per</th>
                                            <th class="text-center bg-primary bg-opacity-10 fw-bold" title="Total Families">Tot Fam</th>
                                            <th class="text-center bg-primary bg-opacity-10 fw-bold" title="Total Persons">Tot Per</th>
                                            <th style="min-width: 120px;">Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($barangays as $barangay):
                                            $hasData = isset($settings[$barangay]);
                                            $rowClass = $hasData ? '' : 'table-secondary opacity-50';
                                        ?>
                                            <tr class="<?= $rowClass ?>">
                                                <td class="fw-bold"><?= htmlspecialchars($barangay) ?></td>
                                                <td class="text-center"><?= $hasData ? ($settings[$barangay]['affected_families_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center"><?= $hasData ? ($settings[$barangay]['affected_persons_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center"><?= $hasData ? ($settings[$barangay]['num_ecs_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-info bg-opacity-10"><?= $hasData ? ($settings[$barangay]['inside_families_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-info bg-opacity-10"><?= $hasData ? ($settings[$barangay]['inside_persons_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-warning bg-opacity-10"><?= $hasData ? ($settings[$barangay]['outside_families_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-warning bg-opacity-10"><?= $hasData ? ($settings[$barangay]['outside_persons_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-primary bg-opacity-10 fw-bold"><?= $hasData ? ($settings[$barangay]['total_families_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-primary bg-opacity-10 fw-bold"><?= $hasData ? ($settings[$barangay]['total_persons_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-muted small">
                                                    <?= $hasData && $settings[$barangay]['updated_at'] ? date('M j, Y', strtotime($settings[$barangay]['updated_at'])) : '<span class="badge bg-secondary">Not Set</span>' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Legend:</strong>
                                <span class="badge bg-info ms-2">Inside ECs</span>
                                <span class="badge bg-warning text-dark ms-2">Outside ECs</span>
                                <span class="badge bg-primary ms-2">Totals (Auto-calculated)</span>
                            </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotals() {
    // Calculate Total Families = Inside Families + Outside Families
    const insideFamilies = parseInt(document.getElementById('inside_families_cumulative').value) || 0;
    const outsideFamilies = parseInt(document.getElementById('outside_families_cumulative').value) || 0;
    const totalFamilies = insideFamilies + outsideFamilies;
    document.getElementById('total_families_cumulative').value = totalFamilies;

    // Calculate Total Persons = Inside Persons + Outside Persons
    const insidePersons = parseInt(document.getElementById('inside_persons_cumulative').value) || 0;
    const outsidePersons = parseInt(document.getElementById('outside_persons_cumulative').value) || 0;
    const totalPersons = insidePersons + outsidePersons;
    document.getElementById('total_persons_cumulative').value = totalPersons;
}

function loadSettings() {
    const barangay = document.getElementById('barangay').value;
    const dataEntrySection = document.getElementById('data-entry-section');

    if (!barangay) {
        // Hide data entry section
        dataEntrySection.style.display = 'none';
        return;
    }

    // Show data entry section with smooth animation
    dataEntrySection.style.display = 'block';
    setTimeout(() => {
        dataEntrySection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);

    // Load settings from the settings array (passed from PHP)
    const settings = <?= json_encode($settings) ?>;
    const barangaySettings = settings[barangay] || {};

    document.getElementById('affected_families_cumulative').value = barangaySettings.affected_families_cumulative || '0';
    document.getElementById('affected_persons_cumulative').value = barangaySettings.affected_persons_cumulative || '0';
    document.getElementById('num_ecs_cumulative').value = barangaySettings.num_ecs_cumulative || '0';
    document.getElementById('inside_families_cumulative').value = barangaySettings.inside_families_cumulative || '0';
    document.getElementById('inside_persons_cumulative').value = barangaySettings.inside_persons_cumulative || '0';
    document.getElementById('outside_families_cumulative').value = barangaySettings.outside_families_cumulative || '0';
    document.getElementById('outside_persons_cumulative').value = barangaySettings.outside_persons_cumulative || '0';

    // Calculate totals after loading
    calculateTotals();

    // Focus on first input
    document.getElementById('affected_families_cumulative').focus();
}

// Add event listeners to auto-calculate when inside/outside values change
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('inside_families_cumulative').addEventListener('input', calculateTotals);
    document.getElementById('outside_families_cumulative').addEventListener('input', calculateTotals);
    document.getElementById('inside_persons_cumulative').addEventListener('input', calculateTotals);
    document.getElementById('outside_persons_cumulative').addEventListener('input', calculateTotals);
});
</script>

<?php include '../includes/footer.php'; ?>
