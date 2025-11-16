<?php
// Define page title
define('PAGE_TITLE', 'Annex 17 Cumulative Settings');

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
        header('Location: annex17_cumulative_settings.php');
        exit;
    }

    $barangay = sanitize($_POST['barangay'] ?? '');

    // Collect all fields
    $fields = [
        // Basic Info
        'families_cumulative' => (int)($_POST['families_cumulative'] ?? 0),

        // Age Distribution - Cumulative
        'infant_male_cumulative' => (int)($_POST['infant_male_cumulative'] ?? 0),
        'infant_female_cumulative' => (int)($_POST['infant_female_cumulative'] ?? 0),
        'toddler_male_cumulative' => (int)($_POST['toddler_male_cumulative'] ?? 0),
        'toddler_female_cumulative' => (int)($_POST['toddler_female_cumulative'] ?? 0),
        'preschool_male_cumulative' => (int)($_POST['preschool_male_cumulative'] ?? 0),
        'preschool_female_cumulative' => (int)($_POST['preschool_female_cumulative'] ?? 0),
        'school_male_cumulative' => (int)($_POST['school_male_cumulative'] ?? 0),
        'school_female_cumulative' => (int)($_POST['school_female_cumulative'] ?? 0),
        'teenage_male_cumulative' => (int)($_POST['teenage_male_cumulative'] ?? 0),
        'teenage_female_cumulative' => (int)($_POST['teenage_female_cumulative'] ?? 0),
        'adult_male_cumulative' => (int)($_POST['adult_male_cumulative'] ?? 0),
        'adult_female_cumulative' => (int)($_POST['adult_female_cumulative'] ?? 0),
        'elderly_male_cumulative' => (int)($_POST['elderly_male_cumulative'] ?? 0),
        'elderly_female_cumulative' => (int)($_POST['elderly_female_cumulative'] ?? 0),

        // Vulnerable Groups - Cumulative
        'pregnant_lactating_cumulative' => (int)($_POST['pregnant_lactating_cumulative'] ?? 0),
        'child_headed_family_male_cumulative' => (int)($_POST['child_headed_family_male_cumulative'] ?? 0),
        'child_headed_family_female_cumulative' => (int)($_POST['child_headed_family_female_cumulative'] ?? 0),
        'single_headed_family_male_cumulative' => (int)($_POST['single_headed_family_male_cumulative'] ?? 0),
        'single_headed_family_female_cumulative' => (int)($_POST['single_headed_family_female_cumulative'] ?? 0),
        'solo_parent_male_cumulative' => (int)($_POST['solo_parent_male_cumulative'] ?? 0),
        'solo_parent_female_cumulative' => (int)($_POST['solo_parent_female_cumulative'] ?? 0),
        'pwd_male_cumulative' => (int)($_POST['pwd_male_cumulative'] ?? 0),
        'pwd_female_cumulative' => (int)($_POST['pwd_female_cumulative'] ?? 0),
        'ip_male_cumulative' => (int)($_POST['ip_male_cumulative'] ?? 0),
        'ip_female_cumulative' => (int)($_POST['ip_female_cumulative'] ?? 0),
        'fourps_male_cumulative' => (int)($_POST['fourps_male_cumulative'] ?? 0),
        'fourps_female_cumulative' => (int)($_POST['fourps_female_cumulative'] ?? 0),
    ];

    // Auto-calculate totals
    $fields['total_male_cumulative'] =
        $fields['infant_male_cumulative'] + $fields['toddler_male_cumulative'] +
        $fields['preschool_male_cumulative'] + $fields['school_male_cumulative'] +
        $fields['teenage_male_cumulative'] + $fields['adult_male_cumulative'] +
        $fields['elderly_male_cumulative'];

    $fields['total_female_cumulative'] =
        $fields['infant_female_cumulative'] + $fields['toddler_female_cumulative'] +
        $fields['preschool_female_cumulative'] + $fields['school_female_cumulative'] +
        $fields['teenage_female_cumulative'] + $fields['adult_female_cumulative'] +
        $fields['elderly_female_cumulative'];

    $fields['total_persons_cumulative'] = $fields['total_male_cumulative'] + $fields['total_female_cumulative'];

    // Validation
    $errors = [];
    if (empty($barangay)) {
        $errors[] = 'Barangay is required';
    }
    foreach ($fields as $field => $value) {
        if ($value < 0) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be non-negative';
        }
    }

    if (empty($errors)) {
        try {
            // Build the INSERT statement
            $fieldNames = array_keys($fields);
            $placeholders = array_fill(0, count($fields) + 2, '?'); // +2 for barangay and created_by

            $updateClauses = array_map(function($field) {
                return "$field = VALUES($field)";
            }, $fieldNames);

            $sql = "
                INSERT INTO annex17_cumulative_settings
                (barangay, " . implode(', ', $fieldNames) . ", created_by)
                VALUES (" . implode(', ', $placeholders) . ")
                ON DUPLICATE KEY UPDATE
                " . implode(', ', $updateClauses) . ",
                updated_at = NOW()
            ";

            $stmt = $pdo->prepare($sql);
            $params = array_merge([$barangay], array_values($fields), [$_SESSION['user_id']]);
            $stmt->execute($params);

            log_audit_action($_SESSION['user_id'], 'annex17_cumulative_settings_update', "Updated cumulative settings for barangay: $barangay");
            set_flash("Cumulative settings for <strong>$barangay</strong> updated successfully.", 'success');
        } catch (PDOException $e) {
            error_log("Annex17 cumulative settings error: " . $e->getMessage());
            set_flash('Failed to update cumulative settings. Please try again.', 'error');
        }
    } else {
        set_flash('Validation errors: ' . implode(', ', $errors), 'error');
    }

    header('Location: annex17_cumulative_settings.php');
    exit;
}

// Get existing settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM annex17_cumulative_settings ORDER BY barangay");
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
#total_male_cumulative,
#total_female_cumulative,
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

/* Gender color coding */
.gender-male {
    background-color: rgba(13, 110, 253, 0.05) !important;
    border-left: 3px solid #0d6efd;
}

.gender-female {
    background-color: rgba(220, 53, 69, 0.05) !important;
    border-left: 3px solid #dc3545;
}
</style>

<div class="container-fluid px-4 py-3">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-calculator me-2"></i>Annex 17 Cumulative Settings
            </h5>
        </div>
        <div class="card-body">
                    <?php show_flash(); ?>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instructions:</strong> Select a barangay and enter the cumulative baseline values for Pre-emptive Evacuation. These values will be used in the Annex 17 report.
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

                            <!-- Section 1: Basic Info -->
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="families_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-home me-1"></i>Number of Families
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="families_cumulative" name="families_cumulative" min="0" value="0" placeholder="Enter number">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Age Distribution -->
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Age Distribution (Cumulative)</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Infant (0-11 months) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-baby me-1"></i>Infant (0-11 months)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="infant_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="infant_male_cumulative" name="infant_male_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="infant_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="infant_female_cumulative" name="infant_female_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                    </div>

                                    <!-- Toddler (1-3 years) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-child me-1"></i>Toddler (1-3 years)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="toddler_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="toddler_male_cumulative" name="toddler_male_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="toddler_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="toddler_female_cumulative" name="toddler_female_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                    </div>

                                    <!-- Preschool (4-5 years) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-child me-1"></i>Preschool (4-5 years)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="preschool_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="preschool_male_cumulative" name="preschool_male_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="preschool_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="preschool_female_cumulative" name="preschool_female_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                    </div>

                                    <!-- School Age (6-12 years) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-school me-1"></i>School Age (6-12 years)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="school_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="school_male_cumulative" name="school_male_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="school_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="school_female_cumulative" name="school_female_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                    </div>

                                    <!-- Teenage (13-17 years) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-user-graduate me-1"></i>Teenage (13-17 years)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="teenage_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="teenage_male_cumulative" name="teenage_male_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="teenage_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="teenage_female_cumulative" name="teenage_female_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                    </div>

                                    <!-- Adult (18-59 years) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-user me-1"></i>Adult (18-59 years)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="adult_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="adult_male_cumulative" name="adult_male_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="adult_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="adult_female_cumulative" name="adult_female_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                    </div>

                                    <!-- Elderly (60+ years) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-walking-cane me-1"></i>Elderly (60+ years)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="elderly_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="elderly_male_cumulative" name="elderly_male_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="elderly_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="elderly_female_cumulative" name="elderly_female_cumulative" min="0" value="0" oninput="calculateTotals()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Vulnerable Groups -->
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fas fa-hands-helping me-2"></i>Vulnerable Groups (Cumulative)</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Pregnant/Lactating Mothers -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-baby-carriage me-1"></i>Pregnant/Lactating Mothers</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="pregnant_lactating_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="pregnant_lactating_cumulative" name="pregnant_lactating_cumulative" min="0" value="0">
                                        </div>
                                    </div>

                                    <!-- Child-headed Family -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-child me-1"></i>Child-headed Family</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="child_headed_family_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="child_headed_family_male_cumulative" name="child_headed_family_male_cumulative" min="0" value="0">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="child_headed_family_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="child_headed_family_female_cumulative" name="child_headed_family_female_cumulative" min="0" value="0">
                                        </div>
                                    </div>

                                    <!-- Single-headed Family -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-user-friends me-1"></i>Single-headed Family</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="single_headed_family_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="single_headed_family_male_cumulative" name="single_headed_family_male_cumulative" min="0" value="0">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="single_headed_family_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="single_headed_family_female_cumulative" name="single_headed_family_female_cumulative" min="0" value="0">
                                        </div>
                                    </div>

                                    <!-- Solo Parent -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-user me-1"></i>Solo Parent</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="solo_parent_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="solo_parent_male_cumulative" name="solo_parent_male_cumulative" min="0" value="0">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="solo_parent_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="solo_parent_female_cumulative" name="solo_parent_female_cumulative" min="0" value="0">
                                        </div>
                                    </div>

                                    <!-- PWD -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-wheelchair me-1"></i>PWD (Person with Disability)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="pwd_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="pwd_male_cumulative" name="pwd_male_cumulative" min="0" value="0">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="pwd_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="pwd_female_cumulative" name="pwd_female_cumulative" min="0" value="0">
                                        </div>
                                    </div>

                                    <!-- IP (Indigenous People) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-users me-1"></i>IP (Indigenous People)</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="ip_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="ip_male_cumulative" name="ip_male_cumulative" min="0" value="0">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="ip_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="ip_female_cumulative" name="ip_female_cumulative" min="0" value="0">
                                        </div>
                                    </div>

                                    <!-- 4Ps -->
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-2">
                                            <h6 class="text-muted"><i class="fas fa-hand-holding-heart me-1"></i>4Ps</h6>
                                        </div>
                                        <div class="col-md-6 gender-male">
                                            <label for="fourps_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Male
                                            </label>
                                            <input type="number" class="form-control" id="fourps_male_cumulative" name="fourps_male_cumulative" min="0" value="0">
                                        </div>
                                        <div class="col-md-6 gender-female">
                                            <label for="fourps_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Female
                                            </label>
                                            <input type="number" class="form-control" id="fourps_female_cumulative" name="fourps_female_cumulative" min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Total Persons (Auto-Calculated) -->
                            <div class="card mb-4 shadow-sm border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calculator me-2"></i>Total Persons (Auto-Calculated)
                                        <span class="badge bg-light text-dark ms-2">Automatic</span>
                                    </h5>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="total_male_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-male me-1 text-primary"></i>Total Male
                                            </label>
                                            <input type="number" class="form-control form-control-lg bg-white border-primary" id="total_male_cumulative" name="total_male_cumulative" min="0" value="0" readonly style="font-size: 1.5rem; font-weight: bold; color: #0d6efd;">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="total_female_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-female me-1 text-danger"></i>Total Female
                                            </label>
                                            <input type="number" class="form-control form-control-lg bg-white border-danger" id="total_female_cumulative" name="total_female_cumulative" min="0" value="0" readonly style="font-size: 1.5rem; font-weight: bold; color: #dc3545;">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="total_persons_cumulative" class="form-label fw-bold">
                                                <i class="fas fa-users me-1"></i>Total Persons
                                            </label>
                                            <input type="number" class="form-control form-control-lg bg-white border-success" id="total_persons_cumulative" name="total_persons_cumulative" min="0" value="0" readonly style="font-size: 1.5rem; font-weight: bold; color: #198754;">
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
                                <table class="table table-hover table-sm mb-0" style="font-size: 0.8rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="sticky-col" style="min-width: 150px;">Barangay</th>
                                            <th class="text-center" title="Families">Fam</th>
                                            <th class="text-center bg-primary bg-opacity-10" title="Total Male">Male</th>
                                            <th class="text-center bg-danger bg-opacity-10" title="Total Female">Female</th>
                                            <th class="text-center bg-success bg-opacity-10 fw-bold" title="Total Persons">Total</th>
                                            <th class="text-center" title="Pregnant/Lactating">Preg</th>
                                            <th class="text-center" title="PWD">PWD</th>
                                            <th class="text-center" title="Indigenous People">IP</th>
                                            <th class="text-center" title="4Ps">4Ps</th>
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
                                                <td class="text-center"><?= $hasData ? ($settings[$barangay]['families_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-primary bg-opacity-10"><?= $hasData ? ($settings[$barangay]['total_male_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-danger bg-opacity-10"><?= $hasData ? ($settings[$barangay]['total_female_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center bg-success bg-opacity-10 fw-bold"><?= $hasData ? ($settings[$barangay]['total_persons_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center"><?= $hasData ? ($settings[$barangay]['pregnant_lactating_cumulative'] ?? 0) : '-' ?></td>
                                                <td class="text-center"><?= $hasData ? (($settings[$barangay]['pwd_male_cumulative'] ?? 0) + ($settings[$barangay]['pwd_female_cumulative'] ?? 0)) : '-' ?></td>
                                                <td class="text-center"><?= $hasData ? (($settings[$barangay]['ip_male_cumulative'] ?? 0) + ($settings[$barangay]['ip_female_cumulative'] ?? 0)) : '-' ?></td>
                                                <td class="text-center"><?= $hasData ? (($settings[$barangay]['fourps_male_cumulative'] ?? 0) + ($settings[$barangay]['fourps_female_cumulative'] ?? 0)) : '-' ?></td>
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
                                <span class="badge bg-primary ms-2">Male</span>
                                <span class="badge bg-danger ms-2">Female</span>
                                <span class="badge bg-success ms-2">Total (Auto-calculated)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotals() {
    // Calculate Total Male (sum of all male age groups)
    const totalMale =
        (parseInt(document.getElementById('infant_male_cumulative').value) || 0) +
        (parseInt(document.getElementById('toddler_male_cumulative').value) || 0) +
        (parseInt(document.getElementById('preschool_male_cumulative').value) || 0) +
        (parseInt(document.getElementById('school_male_cumulative').value) || 0) +
        (parseInt(document.getElementById('teenage_male_cumulative').value) || 0) +
        (parseInt(document.getElementById('adult_male_cumulative').value) || 0) +
        (parseInt(document.getElementById('elderly_male_cumulative').value) || 0);
    document.getElementById('total_male_cumulative').value = totalMale;

    // Calculate Total Female (sum of all female age groups)
    const totalFemale =
        (parseInt(document.getElementById('infant_female_cumulative').value) || 0) +
        (parseInt(document.getElementById('toddler_female_cumulative').value) || 0) +
        (parseInt(document.getElementById('preschool_female_cumulative').value) || 0) +
        (parseInt(document.getElementById('school_female_cumulative').value) || 0) +
        (parseInt(document.getElementById('teenage_female_cumulative').value) || 0) +
        (parseInt(document.getElementById('adult_female_cumulative').value) || 0) +
        (parseInt(document.getElementById('elderly_female_cumulative').value) || 0);
    document.getElementById('total_female_cumulative').value = totalFemale;

    // Calculate Total Persons = Total Male + Total Female
    const totalPersons = totalMale + totalFemale;
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

    // Load all fields
    document.getElementById('families_cumulative').value = barangaySettings.families_cumulative || '0';

    // Age Distribution
    document.getElementById('infant_male_cumulative').value = barangaySettings.infant_male_cumulative || '0';
    document.getElementById('infant_female_cumulative').value = barangaySettings.infant_female_cumulative || '0';
    document.getElementById('toddler_male_cumulative').value = barangaySettings.toddler_male_cumulative || '0';
    document.getElementById('toddler_female_cumulative').value = barangaySettings.toddler_female_cumulative || '0';
    document.getElementById('preschool_male_cumulative').value = barangaySettings.preschool_male_cumulative || '0';
    document.getElementById('preschool_female_cumulative').value = barangaySettings.preschool_female_cumulative || '0';
    document.getElementById('school_male_cumulative').value = barangaySettings.school_male_cumulative || '0';
    document.getElementById('school_female_cumulative').value = barangaySettings.school_female_cumulative || '0';
    document.getElementById('teenage_male_cumulative').value = barangaySettings.teenage_male_cumulative || '0';
    document.getElementById('teenage_female_cumulative').value = barangaySettings.teenage_female_cumulative || '0';
    document.getElementById('adult_male_cumulative').value = barangaySettings.adult_male_cumulative || '0';
    document.getElementById('adult_female_cumulative').value = barangaySettings.adult_female_cumulative || '0';
    document.getElementById('elderly_male_cumulative').value = barangaySettings.elderly_male_cumulative || '0';
    document.getElementById('elderly_female_cumulative').value = barangaySettings.elderly_female_cumulative || '0';

    // Vulnerable Groups
    document.getElementById('pregnant_lactating_cumulative').value = barangaySettings.pregnant_lactating_cumulative || '0';
    document.getElementById('child_headed_family_male_cumulative').value = barangaySettings.child_headed_family_male_cumulative || '0';
    document.getElementById('child_headed_family_female_cumulative').value = barangaySettings.child_headed_family_female_cumulative || '0';
    document.getElementById('single_headed_family_male_cumulative').value = barangaySettings.single_headed_family_male_cumulative || '0';
    document.getElementById('single_headed_family_female_cumulative').value = barangaySettings.single_headed_family_female_cumulative || '0';
    document.getElementById('solo_parent_male_cumulative').value = barangaySettings.solo_parent_male_cumulative || '0';
    document.getElementById('solo_parent_female_cumulative').value = barangaySettings.solo_parent_female_cumulative || '0';
    document.getElementById('pwd_male_cumulative').value = barangaySettings.pwd_male_cumulative || '0';
    document.getElementById('pwd_female_cumulative').value = barangaySettings.pwd_female_cumulative || '0';
    document.getElementById('ip_male_cumulative').value = barangaySettings.ip_male_cumulative || '0';
    document.getElementById('ip_female_cumulative').value = barangaySettings.ip_female_cumulative || '0';
    document.getElementById('fourps_male_cumulative').value = barangaySettings.fourps_male_cumulative || '0';
    document.getElementById('fourps_female_cumulative').value = barangaySettings.fourps_female_cumulative || '0';

    // Calculate totals after loading
    calculateTotals();

    // Focus on first input
    document.getElementById('families_cumulative').focus();
}

// Add event listeners to auto-calculate when values change
document.addEventListener('DOMContentLoaded', function() {
    const ageInputs = [
        'infant_male_cumulative', 'infant_female_cumulative',
        'toddler_male_cumulative', 'toddler_female_cumulative',
        'preschool_male_cumulative', 'preschool_female_cumulative',
        'school_male_cumulative', 'school_female_cumulative',
        'teenage_male_cumulative', 'teenage_female_cumulative',
        'adult_male_cumulative', 'adult_female_cumulative',
        'elderly_male_cumulative', 'elderly_female_cumulative'
    ];

    ageInputs.forEach(function(inputId) {
        document.getElementById(inputId).addEventListener('input', calculateTotals);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
