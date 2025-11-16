<?php
/**
 * One-time migration script to populate status history for existing Annex 8 records
 * Run this once to backfill the status history table with existing data
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/annex8_status_history.php';

// Check if running from command line or authorized admin
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        die('This script can only be run by administrators');
    }
}

echo "Starting migration of existing Annex 8 records to status history...\n\n";

try {
    // Call the migration function from the helper file
    $result = migrate_existing_status_data();

    echo "Migration completed!\n";
    echo "Records migrated successfully: {$result['success']}\n";
    echo "Records skipped (already have history): {$result['skipped']}\n";
    echo "Records failed: {$result['failed']}\n\n";

    if ($result['success'] > 0) {
        echo "Successfully populated status history for {$result['success']} existing records.\n";
    }

    if ($result['skipped'] > 0) {
        echo "{$result['skipped']} records were skipped because they already have status history.\n";
    }

    if ($result['failed'] > 0) {
        echo "Warning: {$result['failed']} records failed to migrate. Check error logs for details.\n";
    }

    echo "\nMigration process complete!\n";

} catch (Exception $e) {
    echo "Fatal error during migration: " . $e->getMessage() . "\n";
    error_log("Annex8 status migration error: " . $e->getMessage());
}
