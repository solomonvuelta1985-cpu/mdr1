<?php
// Redirect to main form with edit parameter
// For now, editing redirects back to the main form
// In a full implementation, this would pre-populate the form with existing data

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$record_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$record_id) {
    set_flash('Invalid record ID', 'error');
    header('Location: ../public/annex17_records.php');
    exit;
}

// For now, inform the user that editing is not yet implemented
// They can delete and recreate the record
set_flash('Editing is not yet implemented. Please delete the record and create a new one with the correct information.', 'info');
header('Location: ../public/annex17_records.php');
exit;
?>
