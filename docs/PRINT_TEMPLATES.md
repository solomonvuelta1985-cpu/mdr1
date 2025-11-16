# NDRRMC Print Templates Documentation

## Overview
This document provides comprehensive information about the print/export functionality for NDRRMC Annex templates (Annex 2, 3, 4, 5, 6, 7, and 8).

## Table of Contents
- [Template Specifications](#template-specifications)
- [File Locations](#file-locations)
- [Font Sizes and Typography](#font-sizes-and-typography)
- [Page Setup](#page-setup)
- [Export Features](#export-features)
- [Template Details](#template-details)

---

## Template Specifications

### Paper Size
- **Format**: Legal size (8.5" x 13")
- **Orientation**: Landscape
- **Margins**:
  - Top/Bottom: 0.75 inches
  - Left/Right: 0.5 inches

### Typography Standards
All templates follow standardized font sizing for consistency and readability:

| Element | Font Size | Notes |
|---------|-----------|-------|
| Header h1 (NDRRMC Title) | 12pt | NDRRMC Memorandum Circular title |
| Header h2 (Annex Title) | 12pt | Specific annex name |
| Table Headers (th) | 8pt | Column headers |
| Table Cells (td) | 9pt | Default cell content |
| Text Content (.text-left, .text-center) | 9pt | Location and text fields |
| Numeric Values (.numeric) | 9pt | Number fields |
| Sub-headers | 8pt | Cumulative/Current labels (Annex 2) |
| Memo Info | 9pt | Date and record count |
| Summary Section | 9pt | Bottom summary statistics |

### Header Spacing
Compact header design to maximize content space:
- **Header margin-bottom**: 12px
- **h1 margin-bottom**: 4px
- **h1 line-height**: 1.2
- **h2 margin-top**: 2px

---

## File Locations

### Print Template Files
```
c:\xampp\htdocs\mdr1\print_templates\
├── annex2_print_template.php  (Affected Population - 23 columns)
├── annex3_print_template.php  (Casualties - 15 columns)
├── annex4_print_template.php  (Damaged Houses - 9 columns)
├── annex5_print_template.php  (Agriculture Damage - 16 columns)
├── annex6_print_template.php  (Infrastructure Damage - 14 columns)
├── annex7_print_template.php  (Other Assets Damage - 10 columns)
└── annex8_print_template.php  (Road/Bridge Status - 11 columns)
```

### Records Pages (with Export Buttons)
```
c:\xampp\htdocs\mdr1\public\
├── annex2_records.php
├── annex3_records.php
├── annex4_records.php
├── annex5_records.php
├── annex6_records.php
├── annex7_records.php
└── annex8_records.php
```

---

## Page Setup

### CSS @page Configuration
```css
@page {
    size: 8.5in 13in landscape;
    margin: 0.75in 0.5in;
}
```

### Body Styles
```css
body {
    font-family: Arial, sans-serif;
    font-size: 10pt;
    line-height: 1.4;
    color: #000;
    padding: 15px;
}
```

### Table Borders
```css
table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #000;
    table-layout: auto;
}

th, td {
    border: 0.5px solid #000;
    padding: 6px 4px; /* 5px 3px for Annex 5 */
    text-align: center;
    vertical-align: middle;
}
```

---

## Export Features

### Available Export Formats
Each template supports four export options:

1. **Print** - Opens browser print dialog
2. **Word** - Downloads as .doc file (HTML format)
3. **Excel** - Downloads as .xls file (tab-delimited)
4. **CSV** - Downloads as .csv file (comma-separated)

### Export Button Implementation
Located in records pages with Bootstrap 5 dropdown:

```html
<div class="btn-group">
    <button type="button" class="btn-custom btn-info-custom dropdown-toggle"
            data-bs-toggle="dropdown">
        <i class="fas fa-print"></i> Print/Export
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" onclick="printAnnex[X]()">Print</a></li>
        <li><a class="dropdown-item" onclick="exportToWord()">Export to Word</a></li>
        <li><a class="dropdown-item" onclick="exportToExcel()">Export to Excel</a></li>
        <li><a class="dropdown-item" onclick="exportToCSV()">Export to CSV</a></li>
    </ul>
</div>
```

### JavaScript Functions
Each records page includes:
- `printAnnex[X]()` - Opens print template in new window
- `exportToWord()` - Generates HTML-based Word document
- `exportToExcel()` - Generates tab-delimited Excel file
- `exportToCSV()` - Generates comma-separated CSV file

---

## Template Details

### Annex 2: Affected Population
**Columns**: 23 total
- Location: Region, Province, City/Municipality, Barangay
- Affected Families: Cumulative/Current
- Affected Persons: Cumulative/Current
- Number of ECs: Cumulative/Current
- Inside ECs: Families & Persons (Cumulative/Current)
- Outside ECs: Families & Persons (Cumulative/Current)
- Total Displaced: Families & Persons (Cumulative/Current)
- Remarks

**Database Table**: `annex2_affected_population`

**Special Features**:
- Multi-row headers (3 rows)
- Sub-headers for Cumulative/Current
- Complex colspan/rowspan structure

**Summary Statistics**:
- Total Affected Families (Cumulative & Current)
- Total Affected Persons (Cumulative & Current)
- Total Displaced Persons

---

### Annex 3: Casualties
**Columns**: 15 total
- Location: Region, Province, City/Municipality, Barangay
- Category: dead, injured, ill, or missing
- Name: Surname, First Name, Middle Name
- Demographics: Age, Sex
- Details: Address, Cause, Remarks
- Validation: Source of Data, Validated (yes/no)

**Database Table**: `annex3_casualties`

**Special Features**:
- Single-row header
- Text-heavy content
- Name fields (surname, first, middle)

**Summary Statistics**:
- Total Dead
- Total Injured
- Total Ill
- Total Missing
- Total Validated records

---

### Annex 4: Damaged Houses
**Columns**: 9 total
- Location: Region, Province, City/Municipality, Barangay
- Damage Count: Totally Damaged, Partially Damaged, Total
- Financial: Cost
- Notes: Remarks

**Database Table**: `annex4_damaged_houses`

**Special Features**:
- Two-row header
- Simple structure
- Cost field (text format for flexibility)

**Summary Statistics**:
- Total Totally Damaged Houses
- Total Partially Damaged Houses
- Total Damaged Houses

---

### Annex 5: Damage and Losses to Agriculture
**Columns**: 16 total
- Location: Region, Province, City/Municipality, Barangay
- Agriculture Type: Classification, Type
- People Affected: Number of fisherfolks or farmers
- Crop Area (ha): No recovery, With recovery, Total
- Production Loss: Volume (MT)
- Livestock: Number of heads
- Infrastructure: Totally damaged, Partially damaged, Total
- Financial: Production loss/cost damage value

**Database Table**: `annex5_agriculture_damage`

**Special Features**:
- Most complex table structure
- Two-row header with colspan
- Agriculture-specific fields
- Mixed numeric and text data

**Summary Statistics**:
- Total Affected People
- Total Crop Area Affected (ha)
- Total Production Loss (MT)
- Total Animal Heads
- Total Infrastructure Damaged

---

### Annex 6: Damage to Infrastructure
**Columns**: 14 total
- Location: Region, Province, City/Municipality, Barangay
- Damaged Infrastructure: Classification, Type
- Damage Assessment: Totally Damaged (Count, Unit), Partially Damaged (Count, Unit)
- Financial: Cost (per Type), Total Damaged (Count, Unit, Cost)
- Notes: Remarks

**Database Table**: `annex6_infrastructure_damage`

**Special Features**:
- Two-row header with complex colspan/rowspan
- Infrastructure classification (Roads, Bridges, Buildings, etc.)
- Unit specification for damaged infrastructure
- Separate cost tracking per damage type
- Total cost calculation across all infrastructure

**Summary Statistics**:
- Total Infrastructure Totally Damaged
- Total Infrastructure Partially Damaged
- Total Infrastructure Damage Cost

---

### Annex 7: Damage to Other Assets
**Columns**: 10 total
- Location: Region, Province, City/Municipality, Barangay
- Asset Details: Classification (Vehicles, Aircraft, Medicines, etc.), Particulars/Name of Asset
- Quantity: Unit, Quantity
- Financial: Cost
- Notes: Remarks

**Database Table**: `annex7_other_assets_damage`

**Special Features**:
- Single-row header
- Asset classification categories (Vehicles, Aircraft, Communication Equipment, etc.)
- Flexible unit specification (pieces, units, sets, etc.)
- Text-based cost field for flexibility

**Summary Statistics**:
- Total Assets by Classification
- Total Quantity by Unit Type
- Total Damage Cost

---

### Annex 8: Status of Roads and Bridges
**Columns**: 11 total
- Location: Region, Province, City/Municipality, Barangay
- Infrastructure: Type (Road/Bridge), Classification (National/Provincial/City/Barangay)
- Identification: Road Section/Bridge Name
- Status: Status (Passable, Not Passable, Passable to Heavy Vehicles Only, Passable to Light Vehicles Only)
- Timeline: Date/Time Not Passable, Date/Time Passable
- Notes: Remarks
- Image: Attached photo/image

**Database Table**: `annex8_road_bridge_status`

**Special Features**:
- Single-row header
- Date/time tracking for status changes
- Image upload capability
- **Status History Tracking** (NEW FEATURE - See below)

**Status History System**:
Annex 8 includes a complete audit trail system that tracks all status changes:
- **History Table**: `annex8_status_history` stores every status change
- **Timeline View**: Visual timeline showing all status changes in the view modal
- **Automatic Logging**: Status changes are automatically logged when records are created or updated
- **Detailed Tracking**: Each history entry includes:
  - Previous and new status
  - Date/time of change
  - User who made the change
  - Remarks at time of change

**Summary Statistics**:
- Total Roads by Status
- Total Bridges by Status
- Total Passable Roads/Bridges
- Total Not Passable Roads/Bridges

---

## Print-Specific Styles

### Auto-Print Functionality
All templates include JavaScript to auto-trigger print dialog:

```javascript
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 500);
};

window.onafterprint = function() {
    setTimeout(function() {
        window.close();
    }, 500);
};
```

### Print Media Query
```css
@media print {
    body {
        padding: 10px;
    }
    .no-print {
        display: none !important;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
}
```

### Elements Hidden in Print
- `.no-print` class hides elements (auto-generation footer)

---

## Database Integration

### Authentication & Authorization
All templates include:
```php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
```

### Role-Based Data Access
**Admin users** see all records:
```php
if ($is_admin) {
    $stmt = db_query("
        SELECT a.*, u.full_name, u.username, u.barangay as user_barangay
        FROM annex[X]_table a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_archived = 0
        ORDER BY a.region, a.province, a.city
    ");
}
```

**Regular users** see only their records:
```php
else {
    $stmt = db_query("
        SELECT * FROM annex[X]_table
        WHERE created_by = ? AND is_archived = 0
        ORDER BY region, province, city
    ", [$user_id]);
}
```

### Barangay Field Handling
Admin view includes user barangay fallback:
```php
echo htmlspecialchars($record['barangay'] ?: $record['user_barangay'] ?: '');
```

---

## Common Issues & Solutions

### Issue: Text Too Small When Printed
**Solution**: Font sizes have been increased to minimum 9pt for content, 8pt for headers.

### Issue: No Margins / Layout Too Tight
**Solution**: Page margins set to 0.75in x 0.5in with proper @page configuration.

### Issue: Header Takes Too Much Space
**Solution**: Reduced header spacing:
- margin-bottom: 12px
- h1 margin-bottom: 4px
- h1 line-height: 1.2

### Issue: Columns Don't Fit on Page
**Solution**:
- Use landscape orientation
- Optimized font sizes (8-9pt)
- Adjusted padding values
- table-layout: auto for flexible column widths

---

## Maintenance Notes

### Version History
- **Initial Release**: Basic print templates with A3 size, small fonts
- **Update 1**: Changed to 8.5x13" legal size, increased body font to 11pt
- **Update 2**: Standardized all fonts to 8-9pt range, headers to 12pt, reduced header spacing

### Future Considerations
1. **Column Overflow**: If adding more columns, may need to further reduce font sizes or padding
2. **Multi-page Records**: Currently uses page-break-inside: avoid for rows
3. **Custom Branding**: Logo/seal can be added to header section
4. **Date Ranges**: Could add disaster event date range to header
5. **Filtering**: Could add filter options before export (by region, date, etc.)

---

## Official Reference
Templates follow **NDRRMC Memorandum Circular No. 05, s. 2025** re NDRRMC Reporting Templates.

All templates are designed to match official NDRRMC format requirements for disaster management reporting.

---

## Contact & Support
For issues or modifications to print templates, refer to:
- Main application documentation
- Database schema documentation
- Bootstrap 5 documentation (for dropdown buttons)
- PHP PDO documentation (for database queries)

---

## Annex 8 Status History Feature

### Overview
The Annex 8 status history system provides complete audit trail functionality for tracking road and bridge status changes over time. This ensures that no historical data is lost when status updates occur.

### Database Structure

#### Main Table: `annex8_road_bridge_status`
Stores current road/bridge status information.

#### History Table: `annex8_status_history`
```sql
CREATE TABLE annex8_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    road_bridge_id INT NOT NULL,
    status ENUM('Passable', 'Not Passable', 'Passable to Heavy Vehicles Only', 'Passable to Light Vehicles Only'),
    status_date DATETIME NOT NULL,
    remarks TEXT,
    changed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (road_bridge_id) REFERENCES annex8_road_bridge_status(id) ON DELETE CASCADE
);
```

### Helper Functions
Location: `c:\xampp\htdocs\mdr1\includes\annex8_status_history.php`

**Available Functions**:
1. `log_status_change($road_bridge_id, $status, $status_date, $remarks, $changed_by)` - Log a status change
2. `get_status_history($road_bridge_id)` - Retrieve complete timeline for a record
3. `get_latest_status($road_bridge_id)` - Get most recent status entry
4. `get_status_change_count($road_bridge_id)` - Count number of status changes
5. `migrate_existing_status_data()` - One-time migration for existing records

### Automatic Logging

#### New Records (`api/annex8_save.php`)
When a new road/bridge record is created:
- Initial status is automatically logged to history table
- Uses the appropriate date field (date_not_passable or date_passable)
- Falls back to current timestamp if no date provided

#### Status Updates (`api/annex8_update.php`)
When an existing record's status is changed:
- Old status is compared with new status
- If changed, new status is logged to history
- Includes remarks and user who made the change
- Timestamp records when the change occurred

### Timeline View

#### Location
Status history timeline appears in the view modal: `api/annex8_view.php`

#### Visual Features
- **Timeline Design**: Vertical timeline with color-coded status badges
- **Status Colors**:
  - Passable: Green (success)
  - Not Passable: Red (danger)
  - Passable to Heavy Vehicles Only: Yellow (warning)
  - Passable to Light Vehicles Only: Blue (info)
- **Current Status Indicator**: Latest entry marked with "Current" badge and glow effect
- **Timeline Connector**: Visual line connecting status changes
- **Detailed Information**: Each entry shows:
  - Status badge
  - Date/time of change
  - Remarks (if any)
  - User who made the change

#### Example Timeline Display
```
[●] Passable [Current]                 Mar 15, 2025 2:30 PM
│   Remarks: Road cleared and repaired
│   Changed by: Admin User
│
[●] Not Passable                       Mar 10, 2025 8:00 AM
    Remarks: Landslide blocking the road
    Changed by: Field Officer
```

### Migration Script

#### Purpose
Backfill status history for existing records that were created before the history system was implemented.

#### Location
`c:\xampp\htdocs\mdr1\migrations\migrate_existing_annex8_status.php`

#### Usage
Run once via command line:
```bash
php c:\xampp\htdocs\mdr1\migrations\migrate_existing_annex8_status.php
```

Or access via web (admin only):
```
http://localhost/mdr1/migrations/migrate_existing_annex8_status.php
```

#### Migration Process
1. Fetches all non-archived records from `annex8_road_bridge_status`
2. Checks if history already exists for each record
3. Creates initial history entry using current status and dates
4. Reports success/skipped/failed counts

### Implementation Files

**Core Files Modified**:
- `api/annex8_save.php` - Added initial status logging on creation
- `api/annex8_update.php` - Added status change detection and logging
- `api/annex8_view.php` - Added status history timeline display

**New Files Created**:
- `includes/annex8_status_history.php` - Status history helper functions
- `migrations/create_annex8_status_history.sql` - Database table creation
- `migrations/migrate_existing_annex8_status.php` - Data migration script

### Benefits

1. **Complete Audit Trail**: Never lose historical status information
2. **Timeline Visibility**: Users can see the complete history of status changes
3. **Accountability**: Track who made each status change and when
4. **Data Integrity**: Foreign key constraints ensure referential integrity
5. **Automatic**: No manual intervention required - status changes are logged automatically

### Troubleshooting

**Issue**: Timeline not showing in view modal
- Check if `annex8_status_history` table exists
- Verify records have been migrated or created after implementation
- Check browser console for JavaScript errors

**Issue**: Status changes not being logged
- Verify `annex8_status_history.php` is included in save/update files
- Check PHP error logs for database errors
- Ensure status is actually changing (not just updating other fields)

**Issue**: Migration script shows errors
- Verify database connection is working
- Check that `annex8_status_history` table exists
- Review PHP error logs for specific error messages

---

**Document Version**: 2.0
**Last Updated**: 2025
**Author**: System Documentation
**Status**: Active
