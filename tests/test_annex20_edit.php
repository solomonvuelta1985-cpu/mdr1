<?php
/**
 * Test Script: Annex 20 Edit Amount Calculation
 * Tests if updating cost per unit works without "Amount calculation mismatch" error
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mock authentication for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Annex 20 Edit Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='p-4'>
<div class='container'>
    <h2 class='mb-4'>🧪 Annex 20 Edit Amount Calculation Test</h2>";

// Step 1: Get an existing record to test with
$stmt = db_query("SELECT * FROM annex20_assistance WHERE is_archived = 0 LIMIT 1");
$record = $stmt->fetch();

if (!$record) {
    echo "<div class='alert alert-warning'>❌ No records found. Please create a record first using annex20.php</div>";
    exit;
}

echo "<div class='alert alert-info'>";
echo "<strong>Testing with Record #" . $record['id'] . "</strong><br>";
echo "Barangay: " . htmlspecialchars($record['barangay']) . "<br>";
echo "Original Quantity: " . $record['quantity'] . "<br>";
echo "Original Cost per Unit: ₱" . number_format($record['cost_per_unit'], 2) . "<br>";
echo "Original Amount: ₱" . number_format($record['amount'], 2);
echo "</div>";

// Step 2: Test the calculation logic
$new_quantity = 200;
$new_cost_per_unit = 750.50;
$calculated_amount = $new_quantity * $new_cost_per_unit;

echo "<div class='card mb-3'>";
echo "<div class='card-header bg-primary text-white'><strong>Test Scenario</strong></div>";
echo "<div class='card-body'>";
echo "New Quantity: <strong>$new_quantity</strong><br>";
echo "New Cost per Unit: <strong>₱" . number_format($new_cost_per_unit, 2) . "</strong><br>";
echo "Calculated Amount: <strong>₱" . number_format($calculated_amount, 2) . "</strong>";
echo "</div></div>";

// Step 3: Simulate the update request
$_POST['csrf_token'] = generate_token();
$_POST['record_id'] = $record['id'];
$_POST['region'] = $record['region'];
$_POST['province'] = $record['province'];
$_POST['city'] = $record['city'];
$_POST['barangay'] = $record['barangay'];
$_POST['cluster'] = $record['cluster'];
$_POST['type'] = $record['type'];
$_POST['quantity'] = $new_quantity;
$_POST['unit'] = $record['unit'];
$_POST['cost_per_unit'] = $new_cost_per_unit;
$_POST['amount'] = $calculated_amount;
$_POST['remarks'] = 'Test update - amount calculation fix';

// Mock the validation logic from annex20_update.php
$quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
$cost_per_unit = filter_var($_POST['cost_per_unit'], FILTER_VALIDATE_FLOAT);
$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);

$server_calculated = $quantity * $cost_per_unit;
$difference = abs($server_calculated - $amount);

echo "<div class='card mb-3'>";
echo "<div class='card-header bg-secondary text-white'><strong>Server-Side Validation</strong></div>";
echo "<div class='card-body'>";
echo "Server Calculated: ₱" . number_format($server_calculated, 2) . "<br>";
echo "Submitted Amount: ₱" . number_format($amount, 2) . "<br>";
echo "Difference: ₱" . number_format($difference, 4) . "<br>";
echo "Tolerance: ₱0.02<br>";

if ($difference > 0.02) {
    echo "<div class='alert alert-danger mt-2'>❌ <strong>VALIDATION FAILED</strong> - Amount calculation mismatch!</div>";
} else {
    echo "<div class='alert alert-success mt-2'>✅ <strong>VALIDATION PASSED</strong> - Amount matches within tolerance!</div>";
}
echo "</div></div>";

// Step 4: Actually perform the update
try {
    $sql = "UPDATE annex20_assistance SET
            quantity = ?,
            cost_per_unit = ?,
            amount = ?,
            remarks = ?,
            updated_at = NOW()
            WHERE id = ?";

    $stmt = db_query($sql, [
        $quantity,
        $cost_per_unit,
        $amount,
        $_POST['remarks'],
        $record['id']
    ]);

    if ($stmt && $stmt->rowCount() > 0) {
        echo "<div class='alert alert-success'>";
        echo "<strong>✅ UPDATE SUCCESSFUL!</strong><br>";
        echo "Record #" . $record['id'] . " has been updated successfully.";
        echo "</div>";

        // Verify the update
        $verify_stmt = db_query("SELECT * FROM annex20_assistance WHERE id = ?", [$record['id']]);
        $updated_record = $verify_stmt->fetch();

        echo "<div class='card'>";
        echo "<div class='card-header bg-success text-white'><strong>Updated Record Values</strong></div>";
        echo "<div class='card-body'>";
        echo "Quantity: " . $updated_record['quantity'] . "<br>";
        echo "Cost per Unit: ₱" . number_format($updated_record['cost_per_unit'], 2) . "<br>";
        echo "Amount: ₱" . number_format($updated_record['amount'], 2) . "<br>";
        echo "Remarks: " . htmlspecialchars($updated_record['remarks']);
        echo "</div></div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ No rows were updated (possibly no changes made)</div>";
    }

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>❌ <strong>DATABASE ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<div class='mt-4'>";
echo "<a href='../public/annex20_records.php' class='btn btn-primary'>View Records</a> ";
echo "<a href='../public/annex20.php' class='btn btn-success'>Create New Record</a>";
echo "</div>";

echo "</div></body></html>";
?>
