<?php
/**
 * Process Terminal Report Generation
 * Handles the actual document generation and download
 */

require_once __DIR__ . '/../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has admin or special_access role
if (!in_array($_SESSION['user_role'], ['admin', 'special_access'])) {
    $_SESSION['error_message'] = "Access Denied. You do not have permission to generate Terminal Reports.";
    header("Location: dashboard.php");
    exit();
}

// Check if PHPWord is installed
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('Error: PHPWord library is not installed. Please run: composer install');
}

// Load the Terminal Report Generator
require_once __DIR__ . '/../report/TerminalReportGenerator.php';

try {
    // Get form data
    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;
    $format = isset($_POST['format']) ? $_POST['format'] : 'docx';

    if (!$eventId) {
        throw new Exception('Please select a disaster event.');
    }

    // Verify event exists
    $sql = "SELECT * FROM disaster_events WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        throw new Exception('Selected disaster event not found.');
    }

    // Create generator instance
    $generator = new TerminalReportGenerator($eventId);

    // Generate the report
    $generator->generate();

    // Prepare filename
    $eventName = str_replace([' ', '"', '\'', '(', ')'], '_', $event['event_name']);
    $filename = 'Terminal_Report_' . $eventName . '_' . date('Ymd_His') . '.' . $format;

    // Download the document
    $generator->download($filename);

} catch (Exception $e) {
    // Error handling
    $_SESSION['error_message'] = 'Error generating report: ' . $e->getMessage();
    header("Location: generate_terminal_report.php");
    exit();
}
