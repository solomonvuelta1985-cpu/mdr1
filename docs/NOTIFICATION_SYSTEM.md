# NDRRMC Notification System Documentation

## Overview

The NDRRMC Notification System provides real-time alerts to administrators and staff when barangays submit, update, or delete disaster reports. The system includes:

1. **Bell Icon Notifications** - Facebook-style notification dropdown with auto-refresh
2. **Priority Highlighting** - Red badges for critical annexes (3, 8, 9, 11)
3. **3-Hour Activity Monitor** - Dashboard widget tracking barangay update frequency
4. **Special Access/Staff Role** - New user role for staff members who assist during technical difficulties

---

## Features

### 1. Real-Time Notifications

- **Auto-Refresh**: Notifications are fetched every 30 seconds automatically
- **Unread Badge**: Red counter badge shows number of unread notifications
- **Click to View**: Clicking a notification marks it as read and navigates to the record
- **Mark All Read**: Single button to mark all notifications as read
- **Priority Badges**: Critical annexes (3, 8, 9, 11) display red "PRIORITY" badges

### 2. Notification Triggers

Notifications are created when:

- **New Report Submitted** (`created` action)
- **Report Updated** (`updated` action)
- **Status Changed** (`status_changed` action for Annex 8 road/bridge status changes)
- **Report Deleted** (`deleted` action)

### 3. Who Receives Notifications

- **Administrators** (user_role = 'admin')
- **Special Access / Staff** (user_role = 'staff')
- **Note**: Users do NOT receive notifications for their own actions

### 4. Barangay Activity Monitor Widget

Displays real-time activity status for all barangays:

- **Updated (< 3h)**: Green - Barangays that updated within 3 hours
- **Overdue (> 3h)**: Red - Barangays that haven't updated in more than 3 hours
- **No Activity**: Yellow - Barangays with no recorded activity
- Shows last update time and which annex was updated
- Refresh button to manually update data

---

## Database Structure

### `notifications` Table

```sql
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                    -- Who receives this notification
    type VARCHAR(50) NOT NULL,               -- e.g., 'annex8_submission'
    annex_number VARCHAR(10) NOT NULL,       -- e.g., '8', '3', '9'
    annex_name VARCHAR(100) NOT NULL,        -- Full annex name
    action ENUM('created', 'updated', 'deleted', 'status_changed'),
    barangay VARCHAR(100) NOT NULL,          -- Which barangay took action
    submitted_by_user_id INT NOT NULL,       -- Who performed the action
    submitted_by_name VARCHAR(255) NOT NULL, -- Full name for display
    record_id INT NOT NULL,                  -- ID of the affected record
    message TEXT NOT NULL,                   -- Brief summary message
    is_priority BOOLEAN DEFAULT FALSE,       -- True for Annexes 3, 8, 9, 11
    is_read BOOLEAN DEFAULT FALSE,           -- Read status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## File Structure

### Core Files

```
includes/
├── notifications.php                 # Helper functions for notifications
├── barangay_activity_widget.php     # 3-hour activity monitor widget
└── header.php                        # Bell icon UI and JavaScript

api/
├── notifications_fetch.php           # API endpoint to fetch notifications (GET)
├── notifications_mark_read.php       # API endpoint to mark as read (POST)
├── annex8_save.php                   # Integrated notification creation
├── annex8_update.php                 # Integrated notification creation
└── annex8_delete.php                 # Integrated notification creation

migrations/
├── add_staff_role.sql                # Add 'staff' role to users table
└── create_notifications_table.sql    # Create notifications table

public/
└── user_management.php               # Updated with "Special Access / Staff" role
```

---

## PHP Helper Functions

### Core Functions (`includes/notifications.php`)

#### `create_notification()`

Creates a notification for all admins and staff.

```php
create_notification(
    string $type,                  // 'annex8_submission', 'annex8_update', etc.
    string $annex_number,          // '8', '3', '9', '11', etc.
    string $annex_name,            // 'Status of Roads and Bridges'
    string $action,                // 'created', 'updated', 'deleted', 'status_changed'
    string $barangay,              // Barangay name
    int $submitted_by_user_id,     // User ID who performed action
    string $submitted_by_name,     // Full name for display
    int $record_id,                // ID of the record
    string $message,               // Brief summary
    bool $is_priority = false      // Priority flag
): bool
```

**Example Usage:**

```php
create_notification(
    'annex8_submission',
    '8',
    'Status of Roads and Bridges',
    'created',
    'Barangay San Jose',
    $_SESSION['user_id'],
    $_SESSION['full_name'],
    $new_record_id,
    "Road: National Highway - Status: Not Passable",
    is_priority_annex('8')  // Returns true for Annexes 3, 8, 9, 11
);
```

#### `get_unread_notification_count()`

```php
get_unread_notification_count(int $user_id): int
```

Returns the number of unread notifications for a user.

#### `get_notifications()`

```php
get_notifications(
    int $user_id,
    int $limit = 50,
    bool $unread_only = false
): array
```

Returns array of notification objects sorted by unread first, then by date.

#### `mark_notification_read()`

```php
mark_notification_read(int $notification_id, int $user_id): bool
```

Marks a single notification as read.

#### `mark_all_notifications_read()`

```php
mark_all_notifications_read(int $user_id): bool
```

Marks all notifications as read for a user.

#### `is_priority_annex()`

```php
is_priority_annex(string $annex_number): bool
```

Returns true if annex is priority (3, 8, 9, 11).

#### `get_barangay_activity()`

```php
get_barangay_activity(int $hours = 3): array
```

Returns activity summary for all barangays, checking last update across all annex tables.

**Returns:**

```php
[
    [
        'barangay' => 'San Jose',
        'last_activity' => '2025-01-16 14:30:00',
        'last_annex' => 'Annex 8',
        'hours_since_update' => 1.5,
        'is_overdue' => false
    ],
    // ...
]
```

---

## API Endpoints

### GET `/api/notifications_fetch.php`

Fetches notifications for the logged-in user.

**Requirements:**
- User must be logged in
- User role must be 'admin' or 'staff'

**Response:**

```json
{
    "count": 5,
    "notifications": [
        {
            "id": 123,
            "type": "annex8_submission",
            "annex_number": "8",
            "annex_name": "Status of Roads and Bridges",
            "action": "created",
            "action_text": "submitted a new report",
            "barangay": "San Jose",
            "submitted_by_name": "Juan Dela Cruz",
            "record_id": 456,
            "message": "Road: National Highway - Status: Not Passable",
            "is_priority": true,
            "is_read": false,
            "created_at": "2025-01-16 14:30:00",
            "time_ago": "2 hours ago"
        }
    ]
}
```

### POST `/api/notifications_mark_read.php`

Marks notifications as read.

**Request Body (JSON):**

```json
{
    "action": "mark_one",
    "notification_id": 123
}
```

or

```json
{
    "action": "mark_all"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Notification marked as read"
}
```

---

## Integration Guide

### Step 1: Include Notification Helper

Add to the top of save/update/delete PHP files:

```php
require_once '../includes/notifications.php';
```

### Step 2: Create Notification After Action

After successfully saving/updating/deleting a record:

```php
// After successful save
$notification_message = "{$type}: {$road_section} - Status: {$status}";
create_notification(
    'annex8_submission',
    '8',
    'Status of Roads and Bridges',
    'created',
    $barangay,
    $_SESSION['user_id'],
    $_SESSION['full_name'] ?? 'Unknown User',
    $new_record_id,
    $notification_message,
    is_priority_annex('8')
);
```

### Step 3: Customize Message

Format the message to include relevant details:

```php
// For updates
$notification_message = "{$type}: {$road_section} - Changes: {$changes_str}";

// For deletes
$notification_message = "{$type}: {$road_section} - Deleted";

// For status changes
$notification_message = "{$type}: {$road_section} - Status: {$old_status} → {$new_status}";
```

---

## Adding the Activity Widget to Dashboard

Include the widget in your dashboard page:

```php
<?php include '../includes/barangay_activity_widget.php'; ?>
```

**Example (in dashboard.php):**

```php
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php include '../includes/barangay_activity_widget.php'; ?>
        </div>
    </div>

    <!-- Rest of dashboard content -->
</div>
```

The widget automatically checks user permissions and only displays for admins and staff.

---

## User Role: Special Access / Staff

### Purpose

The "Special Access / Staff" role was created for users who:
- Assist barangays during technical difficulties
- Monitor and respond to reports when regular users cannot access the system
- Need notification access but should not have full administrative privileges

### Database Changes

Modified the `users` table ENUM:

```sql
ALTER TABLE users
MODIFY COLUMN user_role ENUM('user', 'admin', 'staff') DEFAULT 'user';
```

### Creating Staff Users

In User Management:

1. Go to **User Management** → **Create New User**
2. Fill in user details
3. Select **"Special Access / Staff"** from the User Role dropdown
4. Submit

Staff users will:
- Receive all notifications like admins
- Have access to the notification bell icon
- See the barangay activity monitor widget

---

## Customization

### Changing Auto-Refresh Interval

Edit `includes/header.php`:

```javascript
// Current: 30 seconds
setInterval(fetchNotifications, 30000);

// Change to 60 seconds:
setInterval(fetchNotifications, 60000);
```

### Adding More Priority Annexes

Edit `includes/notifications.php`:

```php
function is_priority_annex($annex_number) {
    $priority_annexes = ['3', '8', '9', '11', '12']; // Add '12'
    return in_array($annex_number, $priority_annexes);
}
```

### Changing 3-Hour Threshold

When including the widget:

```php
// Use 6-hour threshold instead
$activity = get_barangay_activity(6);
```

Or call it directly in the widget file.

---

## Maintenance

### Cleanup Old Read Notifications

Run periodically (recommended: monthly cron job):

```php
delete_old_notifications(30); // Delete read notifications older than 30 days
```

**Cron Job Example:**

```php
<?php
// cleanup_notifications.php
require_once '../includes/config.php';
require_once '../includes/notifications.php';

$deleted = delete_old_notifications(30);
echo "Deleted {$deleted} old notifications\n";
```

### Monitor Notification Volume

Check notification count:

```sql
SELECT COUNT(*) FROM notifications WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

Check unread notifications by user:

```sql
SELECT u.full_name, COUNT(*) as unread_count
FROM notifications n
JOIN users u ON n.user_id = u.id
WHERE n.is_read = FALSE
GROUP BY n.user_id, u.full_name
ORDER BY unread_count DESC;
```

---

## Troubleshooting

### Notifications Not Appearing

1. **Check User Role:**
   ```sql
   SELECT user_role FROM users WHERE id = YOUR_USER_ID;
   ```
   Must be 'admin' or 'staff'.

2. **Check Browser Console:**
   - Open Developer Tools (F12)
   - Look for JavaScript errors
   - Check Network tab for failed API calls

3. **Check API Response:**
   - Navigate to: `/api/notifications_fetch.php`
   - Should return JSON with notifications

### Badge Count Wrong

1. Clear browser cache
2. Check if notifications are actually unread:
   ```sql
   SELECT COUNT(*) FROM notifications
   WHERE user_id = YOUR_USER_ID AND is_read = FALSE;
   ```

### Widget Not Showing

1. **Check user role in session:**
   ```php
   var_dump($_SESSION['user_role']); // Should be 'admin' or 'staff'
   ```

2. **Check if widget file exists:**
   ```bash
   ls includes/barangay_activity_widget.php
   ```

---

## Security Considerations

1. **CSRF Protection**: Not required for GET requests (fetch), but POST requests use JSON body
2. **Authorization**: All endpoints check user role (admin/staff only)
3. **SQL Injection**: All queries use prepared statements with parameter binding
4. **XSS Prevention**: All output is escaped with `htmlspecialchars()`
5. **Self-Notification**: Users never receive notifications for their own actions

---

## Future Enhancements

Planned features for future versions:

1. **SMS Integration**: Send SMS alerts for priority notifications
2. **Email Notifications**: Daily digest emails for admins
3. **Push Notifications**: Browser push notifications for critical alerts
4. **Custom Notification Rules**: Allow admins to configure which events trigger notifications
5. **Notification History**: Archive and search past notifications
6. **Per-Annex Subscriptions**: Choose which annexes to receive notifications for

---

## Version History

- **v1.0** (2025-01-16): Initial release
  - Bell icon notifications with auto-refresh
  - Priority annex highlighting
  - 3-hour barangay activity monitor
  - Special Access/Staff role
  - Integration with Annex 8 (save, update, delete)

---

## Support

For issues or questions about the notification system:

1. Check this documentation
2. Review error logs in `logs/php_errors.log`
3. Check audit logs for notification creation events
4. Contact system administrator

---

## Credits

Developed for the NDRRMC Annex Management System to improve real-time monitoring and response coordination for disaster management operations.
