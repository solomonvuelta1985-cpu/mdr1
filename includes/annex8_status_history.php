<?php
/**
 * Annex 8 Status History Helper Functions
 * Manages the tracking of road/bridge status changes
 */

/**
 * Log a status change to the history table
 *
 * @param int $road_bridge_id The ID of the road/bridge record
 * @param string $status The new status
 * @param string $status_date The datetime when this status became effective
 * @param string|null $remarks Optional remarks about the status change
 * @param int $changed_by User ID who made the change
 * @return bool True on success, false on failure
 */
function log_status_change($road_bridge_id, $status, $status_date, $remarks, $changed_by) {
    try {
        $stmt = db_query(
            "INSERT INTO annex8_status_history
            (road_bridge_id, status, status_date, remarks, changed_by)
            VALUES (?, ?, ?, ?, ?)",
            [$road_bridge_id, $status, $status_date, $remarks, $changed_by]
        );

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Failed to log status change: " . $e->getMessage());
        return false;
    }
}

/**
 * Get the complete status history for a road/bridge record
 *
 * @param int $road_bridge_id The ID of the road/bridge record
 * @return array Array of status history records, ordered by status_date DESC
 */
function get_status_history($road_bridge_id) {
    try {
        $stmt = db_query(
            "SELECT
                h.*,
                u.full_name as changed_by_name,
                u.username as changed_by_username
            FROM annex8_status_history h
            LEFT JOIN users u ON h.changed_by = u.id
            WHERE h.road_bridge_id = ?
            ORDER BY h.status_date DESC, h.created_at DESC",
            [$road_bridge_id]
        );

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Failed to get status history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get the latest status from history
 *
 * @param int $road_bridge_id The ID of the road/bridge record
 * @return array|null The latest status record or null if none exists
 */
function get_latest_status($road_bridge_id) {
    try {
        $stmt = db_query(
            "SELECT
                h.*,
                u.full_name as changed_by_name
            FROM annex8_status_history h
            LEFT JOIN users u ON h.changed_by = u.id
            WHERE h.road_bridge_id = ?
            ORDER BY h.status_date DESC, h.created_at DESC
            LIMIT 1",
            [$road_bridge_id]
        );

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Failed to get latest status: " . $e->getMessage());
        return null;
    }
}

/**
 * Get count of status changes for a road/bridge
 *
 * @param int $road_bridge_id The ID of the road/bridge record
 * @return int Number of status changes
 */
function get_status_change_count($road_bridge_id) {
    try {
        $stmt = db_query(
            "SELECT COUNT(*) as count
            FROM annex8_status_history
            WHERE road_bridge_id = ?",
            [$road_bridge_id]
        );

        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    } catch (PDOException $e) {
        error_log("Failed to get status change count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Migrate existing data to status history
 * Should be run once after creating the history table
 *
 * @return array Summary of migration results
 */
function migrate_existing_status_data() {
    $results = [
        'success' => 0,
        'failed' => 0,
        'skipped' => 0
    ];

    try {
        // Get all existing records from annex8_road_bridge_status
        $stmt = db_query("
            SELECT id, status, date_not_passable, date_passable, created_by, created_at
            FROM annex8_road_bridge_status
            WHERE is_archived = 0
        ");

        $records = $stmt->fetchAll();

        foreach ($records as $record) {
            // Check if history already exists for this record
            $check = db_query(
                "SELECT COUNT(*) as count FROM annex8_status_history WHERE road_bridge_id = ?",
                [$record['id']]
            );
            $existing = $check->fetch();

            if ($existing['count'] > 0) {
                $results['skipped']++;
                continue;
            }

            // Create initial history entry based on current status
            $status_date = $record['status'] === 'Not Passable'
                ? $record['date_not_passable']
                : $record['date_passable'];

            // Use created_at if no status date available
            if (!$status_date) {
                $status_date = $record['created_at'];
            }

            $success = log_status_change(
                $record['id'],
                $record['status'],
                $status_date,
                'Initial status from migration',
                $record['created_by']
            );

            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    } catch (PDOException $e) {
        error_log("Migration failed: " . $e->getMessage());
        return $results;
    }
}
