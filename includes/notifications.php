<?php
/**
 * Notification System Helper Functions
 * Handles creation, retrieval, and management of system notifications
 */

/**
 * Create a notification for admins and staff
 *
 * @param string $type Type of notification (e.g., 'annex8_submission')
 * @param string $annex_number Annex number (e.g., '8', '3', '9')
 * @param string $annex_name Full annex name (e.g., 'Status of Roads and Bridges')
 * @param string $action Action performed: 'created', 'updated', 'deleted', 'status_changed'
 * @param string $barangay Barangay name
 * @param int $submitted_by_user_id User ID who performed the action
 * @param string $submitted_by_name Full name of user who performed the action
 * @param int $record_id ID of the record
 * @param string $message Brief summary message
 * @param bool $is_priority Whether this is a priority notification (Annexes 3, 8, 9, 11)
 * @return bool Success status
 */
function create_notification($type, $annex_number, $annex_name, $action, $barangay, $submitted_by_user_id, $submitted_by_name, $record_id, $message, $is_priority = false) {
    try {
        // Get all admin and staff users
        $stmt = db_query("
            SELECT id
            FROM users
            WHERE (user_role = 'admin' OR user_role = 'staff')
            AND is_active = TRUE
            AND id != ?
        ", [$submitted_by_user_id]); // Don't notify the user who made the change

        $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($recipients)) {
            return true; // No recipients to notify
        }

        // Create notification for each recipient
        foreach ($recipients as $recipient_id) {
            db_query("
                INSERT INTO notifications (
                    user_id, type, annex_number, annex_name, action,
                    barangay, submitted_by_user_id, submitted_by_name,
                    record_id, message, is_priority
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $recipient_id,
                $type,
                $annex_number,
                $annex_name,
                $action,
                $barangay,
                $submitted_by_user_id,
                $submitted_by_name,
                $record_id,
                $message,
                $is_priority ? 1 : 0
            ]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notification count for a user
 *
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function get_unread_notification_count($user_id) {
    try {
        $stmt = db_query("
            SELECT COUNT(*)
            FROM notifications
            WHERE user_id = ? AND is_read = FALSE
        ", [$user_id]);

        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Failed to get unread notification count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get recent notifications for a user
 *
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to retrieve
 * @param bool $unread_only Only fetch unread notifications
 * @return array Array of notification objects
 */
function get_notifications($user_id, $limit = 50, $unread_only = false) {
    try {
        $sql = "
            SELECT n.*
            FROM notifications n
            WHERE n.user_id = ?
        ";

        if ($unread_only) {
            $sql .= " AND n.is_read = FALSE";
        }

        $sql .= " ORDER BY n.is_read ASC, n.created_at DESC LIMIT ?";

        $stmt = db_query($sql, [$user_id, $limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Failed to get notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Mark a notification as read
 *
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security check)
 * @return bool Success status
 */
function mark_notification_read($notification_id, $user_id) {
    try {
        $stmt = db_query("
            UPDATE notifications
            SET is_read = TRUE, read_at = NOW()
            WHERE id = ? AND user_id = ?
        ", [$notification_id, $user_id]);

        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Failed to mark notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 *
 * @param int $user_id User ID
 * @return bool Success status
 */
function mark_all_notifications_read($user_id) {
    try {
        $stmt = db_query("
            UPDATE notifications
            SET is_read = TRUE, read_at = NOW()
            WHERE user_id = ? AND is_read = FALSE
        ", [$user_id]);

        return true;
    } catch (Exception $e) {
        error_log("Failed to mark all notifications as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete old read notifications (cleanup function)
 *
 * @param int $days_old Delete notifications older than this many days
 * @return int Number of notifications deleted
 */
function delete_old_notifications($days_old = 30) {
    try {
        $stmt = db_query("
            DELETE FROM notifications
            WHERE is_read = TRUE
            AND read_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ", [$days_old]);

        return $stmt->rowCount();
    } catch (Exception $e) {
        error_log("Failed to delete old notifications: " . $e->getMessage());
        return 0;
    }
}

/**
 * Check if an annex is priority (requires red highlighting)
 *
 * @param string $annex_number Annex number
 * @return bool True if priority annex
 */
function is_priority_annex($annex_number) {
    $priority_annexes = ['3', '8', '9', '11'];
    return in_array($annex_number, $priority_annexes);
}

/**
 * Format notification action for display
 *
 * @param string $action Action type
 * @return string Formatted action text
 */
function format_notification_action($action) {
    $actions = [
        'created' => 'submitted a new report',
        'updated' => 'updated a report',
        'deleted' => 'deleted a report',
        'status_changed' => 'changed status'
    ];

    return $actions[$action] ?? $action;
}

/**
 * Get barangay activity summary (for 3-hour monitor widget)
 *
 * @param int $hours Number of hours to look back (default 3)
 * @return array Array of barangays with their last activity time
 */
function get_barangay_activity($hours = 3) {
    try {
        // Get all unique barangays from users table
        $all_barangays = db_query("
            SELECT DISTINCT barangay
            FROM users
            WHERE barangay IS NOT NULL
            AND user_role != 'admin'
            ORDER BY barangay
        ")->fetchAll(PDO::FETCH_COLUMN);

        $activity = [];

        foreach ($all_barangays as $barangay) {
            // Check last activity across all annex tables
            $last_activity = null;
            $last_annex = null;

            // Check common annex tables
            $tables = [
                'annex2_affected_population' => 'Annex 2',
                'annex3_casualties' => 'Annex 3',
                'annex4_damaged_houses' => 'Annex 4',
                // annex5, annex6, annex7 reserved for Terminal Report Generator (future development)
                'annex8_road_bridge_status' => 'Annex 8'
            ];

            foreach ($tables as $table => $annex_name) {
                try {
                    $stmt = db_query("
                        SELECT MAX(updated_at) as last_update
                        FROM {$table}
                        WHERE barangay = ?
                    ", [$barangay]);

                    $result = $stmt->fetch();
                    if ($result && $result['last_update']) {
                        if (!$last_activity || $result['last_update'] > $last_activity) {
                            $last_activity = $result['last_update'];
                            $last_annex = $annex_name;
                        }
                    }
                } catch (Exception $e) {
                    // Table might not exist, skip
                    continue;
                }
            }

            $hours_since_update = null;
            $is_overdue = false;

            if ($last_activity) {
                $diff = strtotime('now') - strtotime($last_activity);
                $hours_since_update = round($diff / 3600, 1);
                $is_overdue = $hours_since_update > $hours;
            }

            $activity[] = [
                'barangay' => $barangay,
                'last_activity' => $last_activity,
                'last_annex' => $last_annex,
                'hours_since_update' => $hours_since_update,
                'is_overdue' => $is_overdue
            ];
        }

        return $activity;
    } catch (Exception $e) {
        error_log("Failed to get barangay activity: " . $e->getMessage());
        return [];
    }
}

/**
 * Get enhanced barangay activity with login session data
 * Combines report submissions AND user login activity
 *
 * @param int $hours Number of hours to look back (default 3)
 * @return array Array of barangays with comprehensive activity data
 */
function get_barangay_activity_enhanced($hours = 3) {
    try {
        // Get all unique barangays from users table
        $all_barangays = db_query("
            SELECT DISTINCT barangay
            FROM users
            WHERE barangay IS NOT NULL
            AND user_role != 'admin'
            ORDER BY barangay
        ")->fetchAll(PDO::FETCH_COLUMN);

        $activity = [];

        foreach ($all_barangays as $barangay) {
            // Get barangay user IDs
            $user_ids_stmt = db_query("
                SELECT id FROM users
                WHERE barangay = ?
                AND user_role = 'user'
            ", [$barangay]);

            if (!$user_ids_stmt) {
                continue;
            }

            $user_ids = $user_ids_stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($user_ids)) {
                continue;
            }

            // Check if user is currently online (active session in last 15 minutes)
            $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
            $is_online_stmt = db_query("
                SELECT COUNT(*) as count
                FROM user_sessions
                WHERE user_id IN ($placeholders)
                AND is_active = 1
                AND last_activity >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ", $user_ids);

            $is_online = false;
            if ($is_online_stmt) {
                $online_result = $is_online_stmt->fetch();
                $is_online = $online_result && $online_result['count'] > 0;
            }

            // Get last login time
            $last_login_stmt = db_query("
                SELECT MAX(last_activity) as last_login
                FROM user_sessions
                WHERE user_id IN ($placeholders)
            ", $user_ids);

            $last_login = null;
            if ($last_login_stmt) {
                $login_result = $last_login_stmt->fetch();
                $last_login = $login_result ? $login_result['last_login'] : null;
            }

            // Check last report submission across all annex tables
            $last_submission = null;
            $last_annex = null;

            $tables = [
                'annex2_affected_population' => 'Annex 2',
                'annex3_casualties' => 'Annex 3',
                'annex4_damaged_houses' => 'Annex 4',
                // annex5, annex6, annex7 reserved for Terminal Report Generator (future development)
                'annex8_road_bridge_status' => 'Annex 8'
            ];

            foreach ($tables as $table => $annex_name) {
                try {
                    $stmt = db_query("
                        SELECT MAX(updated_at) as last_update
                        FROM {$table}
                        WHERE barangay = ?
                    ", [$barangay]);

                    // Check if query was successful before calling fetch()
                    if ($stmt === false || !is_object($stmt)) {
                        continue;
                    }

                    // Additional check to ensure statement is valid
                    if (method_exists($stmt, 'fetch')) {
                        $result = $stmt->fetch();
                        if ($result && isset($result['last_update']) && $result['last_update']) {
                            if (!$last_submission || $result['last_update'] > $last_submission) {
                                $last_submission = $result['last_update'];
                                $last_annex = $annex_name;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Log the error but continue processing other tables
                    error_log("Error checking table {$table} for barangay {$barangay}: " . $e->getMessage());
                    continue;
                } catch (Error $e) {
                    // Catch fatal errors as well
                    error_log("Fatal error checking table {$table} for barangay {$barangay}: " . $e->getMessage());
                    continue;
                }
            }

            // Calculate hours since login
            $hours_since_login = null;
            if ($last_login) {
                $diff = strtotime('now') - strtotime($last_login);
                $hours_since_login = round($diff / 3600, 1);
            }

            // Calculate hours since submission
            $hours_since_submission = null;
            if ($last_submission) {
                $diff = strtotime('now') - strtotime($last_submission);
                $hours_since_submission = round($diff / 3600, 1);
            }

            // Determine overall status
            $status = 'no_activity';
            $hours_since_activity = null;
            $activity_type = 'none';

            // Use the most recent activity (login or submission)
            if ($last_login && $last_submission) {
                if ($last_login > $last_submission) {
                    $hours_since_activity = $hours_since_login;
                    $activity_type = 'login';
                } else {
                    $hours_since_activity = $hours_since_submission;
                    $activity_type = 'submission';
                }
            } elseif ($last_login) {
                $hours_since_activity = $hours_since_login;
                $activity_type = 'login';
            } elseif ($last_submission) {
                $hours_since_activity = $hours_since_submission;
                $activity_type = 'submission';
            }

            // Categorize based on activity
            if ($is_online) {
                if ($hours_since_submission !== null && $hours_since_submission < 3) {
                    $status = 'active_online'; // Online AND recently submitted
                } else {
                    $status = 'online_quiet'; // Online but no recent submission
                }
            } elseif ($hours_since_activity !== null) {
                if ($hours_since_activity < 3) {
                    $status = 'active';
                } elseif ($hours_since_activity < 6) {
                    $status = 'quiet';
                } else {
                    $status = 'critical';
                }
            }

            $activity[] = [
                'barangay' => $barangay,
                'status' => $status,
                'is_online' => $is_online,
                'last_login' => $last_login,
                'last_submission' => $last_submission,
                'last_annex' => $last_annex,
                'hours_since_login' => $hours_since_login,
                'hours_since_submission' => $hours_since_submission,
                'hours_since_activity' => $hours_since_activity,
                'activity_type' => $activity_type
            ];
        }

        return $activity;
    } catch (Exception $e) {
        error_log("Failed to get enhanced barangay activity: " . $e->getMessage());
        return [];
    }
}
