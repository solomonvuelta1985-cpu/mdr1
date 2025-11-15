# Access Control - Terminal Report & Event Management

## 🔒 Security Update - Role-Based Access Control

**Implementation Date:** January 14, 2025
**Status:** ✅ COMPLETED

---

## Overview

Terminal Report Generator and Disaster Events Management are now restricted to **admin** and **special_access** roles only.

---

## Access Restrictions Applied

### Pages Restricted:
1. **Disaster Events Management** (`manage_disaster_events.php`)
2. **Terminal Report Generator** (`generate_terminal_report.php`)
3. **Terminal Report Processing** (`process_terminal_report.php`)

### Who Has Access:
- ✅ **admin** role - Full access
- ✅ **special_access** role - Full access
- ❌ **user** role - Access denied
- ❌ **encoder** role - Access denied
- ❌ All other roles - Access denied

---

## Implementation Details

### Backend Protection

Each restricted page now has this check immediately after login verification:

```php
// Check if user has admin or special_access role
if (!in_array($_SESSION['user_role'], ['admin', 'special_access'])) {
    $_SESSION['error_message'] = "Access Denied. You do not have permission to access [Feature Name].";
    header("Location: dashboard.php");
    exit();
}
```

### Menu Visibility

Navigation menu items are now conditionally displayed:

```php
<!-- Manage Disaster Events (Admin & Special Access Only) -->
<?php if (in_array($_SESSION['user_role'], ['admin', 'special_access'])): ?>
<li class="nav-item">
    <a class="nav-link" href="manage_disaster_events.php">
        <i class="bi bi-calendar-event"></i>
        <span>Disaster Events</span>
    </a>
</li>
<?php endif; ?>

<!-- Terminal Report Generator (Admin & Special Access Only) -->
<?php if (in_array($_SESSION['user_role'], ['admin', 'special_access'])): ?>
<li class="nav-item">
    <a class="nav-link" href="generate_terminal_report.php">
        <i class="bi bi-file-earmark-text"></i>
        <span>Terminal Report</span>
    </a>
</li>
<?php endif; ?>
```

---

## Security Layers

### Layer 1: Menu Visibility
- Users without proper role **cannot see** the menu links
- Menu items dynamically hidden based on `$_SESSION['user_role']`

### Layer 2: Direct Access Protection
- Even if user knows the URL, they **cannot access** the page
- Server-side check redirects unauthorized users to dashboard
- Error message displayed explaining access denial

### Layer 3: Session Validation
- All pages verify user is logged in first
- Then verify user has appropriate role
- No bypass possible through URL manipulation

---

## User Experience

### For Authorized Users (admin/special_access):
1. Menu shows "Disaster Events" and "Terminal Report" links
2. Can click and access pages normally
3. Full functionality available

### For Unauthorized Users (user/encoder):
1. Menu **does not show** these links
2. If they try direct URL access:
   - Redirected to `dashboard.php`
   - See error message: "Access Denied. You do not have permission to access [Feature]."
3. No functionality available

---

## Error Messages

**Disaster Events:**
```
Access Denied. You do not have permission to access Disaster Events Management.
```

**Terminal Report Generator:**
```
Access Denied. You do not have permission to access Terminal Report Generator.
```

**Terminal Report Processing:**
```
Access Denied. You do not have permission to generate Terminal Reports.
```

---

## Files Modified

### 1. `public/manage_disaster_events.php`
**Added:** Role check after login verification (lines 15-20)

### 2. `public/generate_terminal_report.php`
**Added:** Role check after login verification (lines 15-20)

### 3. `public/process_terminal_report.php`
**Added:** Role check after login verification (lines 15-20)

### 4. `includes/sidenav.php`
**Modified:** Wrapped menu items in role check conditionals (lines 766-784)

---

## Testing Checklist

### Test as Admin:
- ✅ Can see "Disaster Events" menu link
- ✅ Can see "Terminal Report" menu link
- ✅ Can access manage_disaster_events.php
- ✅ Can access generate_terminal_report.php
- ✅ Can generate reports successfully

### Test as Special Access:
- ✅ Can see "Disaster Events" menu link
- ✅ Can see "Terminal Report" menu link
- ✅ Can access manage_disaster_events.php
- ✅ Can access generate_terminal_report.php
- ✅ Can generate reports successfully

### Test as Regular User:
- ✅ Cannot see "Disaster Events" menu link
- ✅ Cannot see "Terminal Report" menu link
- ✅ Gets redirected from manage_disaster_events.php
- ✅ Gets redirected from generate_terminal_report.php
- ✅ Sees error message on dashboard

### Test as Encoder:
- ✅ Cannot see "Disaster Events" menu link
- ✅ Cannot see "Terminal Report" menu link
- ✅ Gets redirected from manage_disaster_events.php
- ✅ Gets redirected from generate_terminal_report.php
- ✅ Sees error message on dashboard

---

## Database Requirements

### Users Table Must Have:
```sql
user_role ENUM('admin', 'user', 'encoder', 'special_access') NOT NULL
```

### To Grant Access to a User:
```sql
-- Make user an admin
UPDATE users SET user_role = 'admin' WHERE id = [user_id];

-- Or grant special access
UPDATE users SET user_role = 'special_access' WHERE id = [user_id];
```

---

## Comparison with Other Features

| Feature | Admin | Special Access | User | Encoder |
|---------|-------|----------------|------|---------|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| View Annexes | ✅ | ✅ | ✅ | ✅ |
| Edit Annexes | ✅ | ✅ | ✅ | ✅ |
| **Disaster Events** | ✅ | ✅ | ❌ | ❌ |
| **Terminal Report** | ✅ | ✅ | ❌ | ❌ |
| Settings | ✅ | ❌ | ❌ | ❌ |
| User Management | ✅ | ❌ | ❌ | ❌ |

---

## Why This Restriction?

### Reasons for Access Control:

1. **Data Sensitivity**
   - Terminal Reports contain comprehensive disaster data
   - Should only be generated by authorized personnel
   - Prevents unauthorized data export

2. **Event Management**
   - Creating/editing events affects entire system
   - Active event selection impacts all users' data entry
   - Requires understanding of disaster event lifecycle

3. **System Integrity**
   - Prevents accidental event deletion
   - Ensures proper event naming conventions
   - Maintains data quality and consistency

4. **Official Documentation**
   - Terminal Reports are official MDRRMO documents
   - Should only be generated by authorized staff
   - May require review before external distribution

---

## Future Enhancements

### Potential Future Roles:

**report_viewer**
- Can generate Terminal Reports
- Cannot manage events
- Read-only access

**event_manager**
- Can manage disaster events
- Cannot generate Terminal Reports
- For disaster coordinators

**analyst**
- Can view all data
- Can generate reports
- Cannot modify events

---

## Audit Logging (Future)

Consider implementing audit logs for:
- Terminal Report generation (who, when, which event)
- Event creation/modification/deletion
- Access denial attempts
- Role changes

```php
// Future implementation example
$sql = "INSERT INTO audit_log (user_id, action, resource, timestamp)
        VALUES (?, 'GENERATE_REPORT', ?, NOW())";
$stmt->execute([$_SESSION['user_id'], $eventId]);
```

---

## Troubleshooting

### User reports: "I can't see Terminal Report"
**Check:**
1. What is their user role?
2. Are they logged in?
3. Has their session expired?

**Solution:**
- If they need access, change their role to `admin` or `special_access`

### User gets "Access Denied" error
**This is normal if:**
- They are a regular user or encoder
- They tried accessing the page directly via URL

**This is a problem if:**
- They are admin or special_access
- Check: `$_SESSION['user_role']` value
- Verify: Session is not corrupted

### Menu shows but page denies access
**Possible causes:**
- Session role mismatch
- Code cache issue
- Different role checks in menu vs. page

**Solution:**
- Clear PHP opcode cache
- Verify session variables
- Check all files have identical role check

---

## Security Best Practices

✅ **Applied:**
- Role check on every page
- Server-side validation (not just client-side)
- Menu visibility matches page access
- Clear error messages without exposing system details
- Session-based authentication

✅ **Recommended:**
- Regular security audits
- Role review quarterly
- Access log monitoring
- Principle of least privilege

---

## Configuration

No configuration file changes needed. The roles are hardcoded:
```php
['admin', 'special_access']
```

If you need to change which roles have access, modify the array in all 4 files:
1. manage_disaster_events.php (line 16)
2. generate_terminal_report.php (line 16)
3. process_terminal_report.php (line 16)
4. sidenav.php (lines 767, 777)

---

## Documentation Updates

This access control is now documented in:
- ✅ This file (ACCESS_CONTROL_TERMINAL_REPORT.md)
- 📝 Update needed: TERMINAL_REPORT_IMPLEMENTATION.md (add Security section)
- 📝 Update needed: EVENT_MANAGEMENT_IMPLEMENTATION.md (add Access Control section)

---

**Implementation Completed:** January 14, 2025
**Security Level:** Role-Based Access Control (RBAC)
**Status:** Production Ready ✅
