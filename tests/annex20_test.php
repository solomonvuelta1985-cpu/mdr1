<?php
/**
 * ANNEX 20 COMPREHENSIVE TEST SCRIPT
 * Tests: Insert, Save, Edit, Update, Delete, Archive, Restore
 *
 * Run this script via browser: http://localhost/mdr1/tests/annex20_test.php
 * Or via CLI: php tests/annex20_test.php
 */

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session
session_start();

// Set test user (you need to be logged in)
if (!isset($_SESSION['user_id'])) {
    // For testing, set a test user ID - CHANGE THIS TO YOUR ACTUAL USER ID
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['username'] = 'test_admin';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annex 20 Test Suite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .test-container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid; }
        .test-success { background: #d1e7dd; border-color: #198754; color: #0f5132; }
        .test-error { background: #f8d7da; border-color: #dc3545; color: #842029; }
        .test-info { background: #cff4fc; border-color: #0dcaf0; color: #055160; }
        .test-warning { background: #fff3cd; border-color: #ffc107; color: #664d03; }
        .test-section { margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .test-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; color: #0d6efd; }
        .btn-test { margin: 5px; }
        pre { background: #212529; color: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { padding: 20px; background: white; border: 1px solid #dee2e6; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #0d6efd; }
        .stat-label { color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="text-center mb-4">
            <i class="fas fa-vial text-primary"></i> Annex 20 Test Suite
        </h1>
        <p class="text-center text-muted mb-4">Comprehensive testing for Assistance Provided to Families</p>

        <?php
        $test_results = [];
        $test_passed = 0;
        $test_failed = 0;
        $test_data_ids = [];

        // Test 1: Database Connection
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-database"></i> Test 1: Database Connection</h3>';
        try {
            $pdo->query("SELECT 1");
            echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> Database connection successful</div>';
            $test_passed++;
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Database connection failed - ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test 2: Table Structure Verification
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-table"></i> Test 2: Table Structure</h3>';
        try {
            $columns = $pdo->query("DESCRIBE annex20_assistance")->fetchAll();
            $required_columns = ['id', 'created_by', 'region', 'province', 'city', 'barangay', 'cluster', 'type', 'quantity', 'unit', 'cost_per_unit', 'amount', 'remarks', 'is_archived', 'created_at', 'updated_at'];

            $found_columns = array_column($columns, 'Field');
            $missing_columns = array_diff($required_columns, $found_columns);

            if (empty($missing_columns)) {
                echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> All required columns exist</div>';
                echo '<pre>' . print_r($columns, true) . '</pre>';
                $test_passed++;
            } else {
                echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Missing columns: ' . implode(', ', $missing_columns) . '</div>';
                $test_failed++;
            }
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test 3: Insert Test Data
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-plus-circle"></i> Test 3: Insert Test Data</h3>';

        $test_data = [
            [
                'region' => 'Region II',
                'province' => 'Cagayan',
                'city' => 'Baggao',
                'barangay' => 'Poblacion',
                'cluster' => 'Food',
                'type' => 'Food packs',
                'quantity' => 100,
                'unit' => 'pack',
                'cost_per_unit' => 500.00,
                'amount' => 50000.00,
                'remarks' => 'Test insert - Food assistance'
            ],
            [
                'region' => 'Region II',
                'province' => 'Cagayan',
                'city' => 'Baggao',
                'barangay' => 'Poblacion',
                'cluster' => 'WASH',
                'type' => 'Hygiene kits',
                'quantity' => 50,
                'unit' => 'kit',
                'cost_per_unit' => 250.00,
                'amount' => 12500.00,
                'remarks' => 'Test insert - Hygiene kits distribution'
            ],
            [
                'region' => 'Region II',
                'province' => 'Cagayan',
                'city' => 'Baggao',
                'barangay' => 'Poblacion',
                'cluster' => 'Shelter',
                'type' => 'Temporary shelter materials',
                'quantity' => 25,
                'unit' => 'set',
                'cost_per_unit' => 2000.00,
                'amount' => 50000.00,
                'remarks' => 'Test insert - Shelter materials for affected families'
            ],
            [
                'region' => 'Region II',
                'province' => 'Cagayan',
                'city' => 'Baggao',
                'barangay' => 'Poblacion',
                'cluster' => 'Health',
                'type' => 'Medicines',
                'quantity' => 200,
                'unit' => 'box',
                'cost_per_unit' => 150.00,
                'amount' => 30000.00,
                'remarks' => 'Test insert - Medical supplies'
            ],
            [
                'region' => 'Region II',
                'province' => 'Cagayan',
                'city' => 'Baggao',
                'barangay' => 'Poblacion',
                'cluster' => 'Protection',
                'type' => 'Blankets',
                'quantity' => 150,
                'unit' => 'piece',
                'cost_per_unit' => 300.00,
                'amount' => 45000.00,
                'remarks' => 'Test insert - Blankets for evacuees'
            ]
        ];

        try {
            $stmt = $pdo->prepare("
                INSERT INTO annex20_assistance
                (created_by, region, province, city, barangay, cluster, type, quantity, unit, cost_per_unit, amount, remarks, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            foreach ($test_data as $index => $data) {
                $result = $stmt->execute([
                    $_SESSION['user_id'],
                    $data['region'],
                    $data['province'],
                    $data['city'],
                    $data['barangay'],
                    $data['cluster'],
                    $data['type'],
                    $data['quantity'],
                    $data['unit'],
                    $data['cost_per_unit'],
                    $data['amount'],
                    $data['remarks']
                ]);

                if ($result) {
                    $last_id = $pdo->lastInsertId();
                    $test_data_ids[] = $last_id;
                    echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> Inserted test record #' . ($index + 1) . ' - ID: ' . $last_id . ' (' . $data['cluster'] . ' - ' . $data['type'] . ')</div>';
                } else {
                    echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Failed to insert test record #' . ($index + 1) . '</div>';
                    $test_failed++;
                }
            }

            if (count($test_data_ids) === count($test_data)) {
                $test_passed++;
                echo '<div class="test-result test-info"><strong>Summary:</strong> Inserted ' . count($test_data_ids) . ' test records successfully</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test 4: Read/Select Test
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-eye"></i> Test 4: Read Test Data</h3>';
        try {
            if (!empty($test_data_ids)) {
                $placeholders = implode(',', array_fill(0, count($test_data_ids), '?'));
                $stmt = $pdo->prepare("SELECT * FROM annex20_assistance WHERE id IN ($placeholders)");
                $stmt->execute($test_data_ids);
                $records = $stmt->fetchAll();

                if (count($records) === count($test_data_ids)) {
                    echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> Successfully read all ' . count($records) . ' test records</div>';
                    echo '<div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>ID</th><th>Cluster</th><th>Type</th><th>Quantity</th><th>Unit</th><th>Amount</th></tr></thead><tbody>';
                    foreach ($records as $record) {
                        echo '<tr>';
                        echo '<td>#' . $record['id'] . '</td>';
                        echo '<td><span class="badge bg-primary">' . htmlspecialchars($record['cluster']) . '</span></td>';
                        echo '<td>' . htmlspecialchars($record['type']) . '</td>';
                        echo '<td>' . number_format($record['quantity']) . '</td>';
                        echo '<td>' . htmlspecialchars($record['unit']) . '</td>';
                        echo '<td>₱' . number_format($record['amount'], 2) . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table></div>';
                    $test_passed++;
                } else {
                    echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Record count mismatch</div>';
                    $test_failed++;
                }
            } else {
                echo '<div class="test-result test-warning"><i class="fas fa-exclamation-triangle"></i> <strong>SKIPPED:</strong> No test data to read</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test 5: Update Test
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-edit"></i> Test 5: Update Test Data</h3>';
        try {
            if (!empty($test_data_ids)) {
                $test_id = $test_data_ids[0];
                $new_quantity = 150;
                $new_cost = 600.00;
                $new_amount = $new_quantity * $new_cost;

                $stmt = $pdo->prepare("
                    UPDATE annex20_assistance
                    SET quantity = ?, cost_per_unit = ?, amount = ?, remarks = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $result = $stmt->execute([$new_quantity, $new_cost, $new_amount, 'Test update - Modified values', $test_id]);

                if ($result && $stmt->rowCount() > 0) {
                    // Verify update
                    $verify = $pdo->prepare("SELECT * FROM annex20_assistance WHERE id = ?");
                    $verify->execute([$test_id]);
                    $updated = $verify->fetch();

                    if ($updated['quantity'] == $new_quantity && $updated['cost_per_unit'] == $new_cost) {
                        echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> Successfully updated record #' . $test_id . '</div>';
                        echo '<div class="test-result test-info">';
                        echo '<strong>Before:</strong> Quantity: 100, Cost: ₱500.00, Amount: ₱50,000.00<br>';
                        echo '<strong>After:</strong> Quantity: ' . $new_quantity . ', Cost: ₱' . number_format($new_cost, 2) . ', Amount: ₱' . number_format($new_amount, 2);
                        echo '</div>';
                        $test_passed++;
                    } else {
                        echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Update verification failed</div>';
                        $test_failed++;
                    }
                } else {
                    echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Update query failed or no rows affected</div>';
                    $test_failed++;
                }
            } else {
                echo '<div class="test-result test-warning"><i class="fas fa-exclamation-triangle"></i> <strong>SKIPPED:</strong> No test data to update</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test 6: Archive Test
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-archive"></i> Test 6: Archive Test Data</h3>';
        try {
            if (!empty($test_data_ids) && isset($test_data_ids[1])) {
                $archive_id = $test_data_ids[1];
                $stmt = $pdo->prepare("UPDATE annex20_assistance SET is_archived = 1 WHERE id = ?");
                $result = $stmt->execute([$archive_id]);

                if ($result && $stmt->rowCount() > 0) {
                    // Verify archive
                    $verify = $pdo->prepare("SELECT is_archived FROM annex20_assistance WHERE id = ?");
                    $verify->execute([$archive_id]);
                    $archived = $verify->fetch();

                    if ($archived['is_archived'] == 1) {
                        echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> Successfully archived record #' . $archive_id . '</div>';
                        $test_passed++;
                    } else {
                        echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Archive verification failed</div>';
                        $test_failed++;
                    }
                } else {
                    echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Archive query failed</div>';
                    $test_failed++;
                }
            } else {
                echo '<div class="test-result test-warning"><i class="fas fa-exclamation-triangle"></i> <strong>SKIPPED:</strong> No test data to archive</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test 7: Restore Test
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-undo"></i> Test 7: Restore Archived Data</h3>';
        try {
            if (!empty($test_data_ids) && isset($test_data_ids[1])) {
                $restore_id = $test_data_ids[1];
                $stmt = $pdo->prepare("UPDATE annex20_assistance SET is_archived = 0 WHERE id = ?");
                $result = $stmt->execute([$restore_id]);

                if ($result && $stmt->rowCount() > 0) {
                    // Verify restore
                    $verify = $pdo->prepare("SELECT is_archived FROM annex20_assistance WHERE id = ?");
                    $verify->execute([$restore_id]);
                    $restored = $verify->fetch();

                    if ($restored['is_archived'] == 0) {
                        echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> Successfully restored record #' . $restore_id . '</div>';
                        $test_passed++;
                    } else {
                        echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Restore verification failed</div>';
                        $test_failed++;
                    }
                } else {
                    echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Restore query failed</div>';
                    $test_failed++;
                }
            } else {
                echo '<div class="test-result test-warning"><i class="fas fa-exclamation-triangle"></i> <strong>SKIPPED:</strong> No test data to restore</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test 8: Delete Test
        echo '<div class="test-section">';
        echo '<h3 class="test-title"><i class="fas fa-trash"></i> Test 8: Delete Test Data</h3>';
        try {
            if (!empty($test_data_ids)) {
                // Delete last record as test
                $delete_id = end($test_data_ids);
                $stmt = $pdo->prepare("DELETE FROM annex20_assistance WHERE id = ?");
                $result = $stmt->execute([$delete_id]);

                if ($result && $stmt->rowCount() > 0) {
                    // Verify deletion
                    $verify = $pdo->prepare("SELECT * FROM annex20_assistance WHERE id = ?");
                    $verify->execute([$delete_id]);
                    $deleted = $verify->fetch();

                    if (!$deleted) {
                        echo '<div class="test-result test-success"><i class="fas fa-check-circle"></i> <strong>PASSED:</strong> Successfully deleted record #' . $delete_id . '</div>';
                        $test_passed++;
                        // Remove from tracking array
                        $test_data_ids = array_diff($test_data_ids, [$delete_id]);
                    } else {
                        echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Delete verification failed - record still exists</div>';
                        $test_failed++;
                    }
                } else {
                    echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> Delete query failed</div>';
                    $test_failed++;
                }
            } else {
                echo '<div class="test-result test-warning"><i class="fas fa-exclamation-triangle"></i> <strong>SKIPPED:</strong> No test data to delete</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="test-result test-error"><i class="fas fa-times-circle"></i> <strong>FAILED:</strong> ' . $e->getMessage() . '</div>';
            $test_failed++;
        }
        echo '</div>';

        // Test Summary
        echo '<div class="test-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">';
        echo '<h3 class="test-title" style="color: white;"><i class="fas fa-flag-checkered"></i> Test Summary</h3>';

        $total_tests = $test_passed + $test_failed;
        $pass_rate = $total_tests > 0 ? ($test_passed / $total_tests) * 100 : 0;

        echo '<div class="stats">';
        echo '<div class="stat-card"><div class="stat-number text-success">' . $test_passed . '</div><div class="stat-label">Passed</div></div>';
        echo '<div class="stat-card"><div class="stat-number text-danger">' . $test_failed . '</div><div class="stat-label">Failed</div></div>';
        echo '<div class="stat-card"><div class="stat-number text-primary">' . $total_tests . '</div><div class="stat-label">Total Tests</div></div>';
        echo '<div class="stat-card"><div class="stat-number ' . ($pass_rate >= 80 ? 'text-success' : 'text-warning') . '">' . number_format($pass_rate, 1) . '%</div><div class="stat-label">Pass Rate</div></div>';
        echo '</div>';

        if (!empty($test_data_ids)) {
            echo '<div class="alert alert-info mt-4">';
            echo '<strong><i class="fas fa-info-circle"></i> Test Data Created:</strong><br>';
            echo 'The following test record IDs were created: <strong>' . implode(', ', $test_data_ids) . '</strong><br>';
            echo '<small>You can view these records in the Annex 20 Records page or clean them up below.</small>';
            echo '</div>';
        }

        echo '</div>';

        // Cleanup Section
        if (!empty($test_data_ids)) {
            echo '<div class="test-section">';
            echo '<h3 class="test-title"><i class="fas fa-broom"></i> Cleanup Test Data</h3>';
            echo '<p>Click the button below to remove all remaining test records from the database.</p>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="cleanup_ids" value="' . implode(',', $test_data_ids) . '">';
            echo '<button type="submit" name="cleanup" class="btn btn-danger btn-lg"><i class="fas fa-trash-alt"></i> Clean Up Test Data</button>';
            echo '</form>';
            echo '</div>';
        }

        // Handle cleanup
        if (isset($_POST['cleanup']) && isset($_POST['cleanup_ids'])) {
            $cleanup_ids = explode(',', $_POST['cleanup_ids']);
            $cleanup_ids = array_filter($cleanup_ids, 'is_numeric');

            if (!empty($cleanup_ids)) {
                try {
                    $placeholders = implode(',', array_fill(0, count($cleanup_ids), '?'));
                    $stmt = $pdo->prepare("DELETE FROM annex20_assistance WHERE id IN ($placeholders)");
                    $stmt->execute($cleanup_ids);

                    echo '<div class="alert alert-success mt-3">';
                    echo '<strong><i class="fas fa-check-circle"></i> Cleanup Complete!</strong><br>';
                    echo 'Successfully removed ' . $stmt->rowCount() . ' test records.';
                    echo '</div>';
                    echo '<script>setTimeout(function(){ window.location.href = window.location.pathname; }, 2000);</script>';
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger mt-3">';
                    echo '<strong><i class="fas fa-exclamation-circle"></i> Cleanup Failed:</strong> ' . $e->getMessage();
                    echo '</div>';
                }
            }
        }
        ?>

        <div class="mt-5 text-center">
            <a href="../public/annex20_records.php" class="btn btn-primary btn-lg">
                <i class="fas fa-list"></i> View Annex 20 Records
            </a>
            <a href="../public/annex20.php" class="btn btn-success btn-lg">
                <i class="fas fa-plus"></i> Create New Record
            </a>
            <button onclick="location.reload()" class="btn btn-secondary btn-lg">
                <i class="fas fa-redo"></i> Run Tests Again
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
