# TERMINAL REPORT GENERATOR - SETUP GUIDE
## Hybrid Implementation Approach

**Date:** 2025-01-14
**Status:** 🚀 READY TO START

---

## 📋 WHAT WE'RE DOING

Building a Terminal Report generator using:
- ✅ **50% existing data** from your MDRRM-ARMS system
- ✅ **50% new tables** for missing sections
- ✅ **PHPWord library** for document generation
- ✅ **Incremental rollout** - working reports ASAP

---

## 🎯 PHASE 1: DATABASE SETUP (You Are Here!)

### Step 1: Run the Migration

```bash
# Navigate to project directory
cd c:\xampp\htdocs\mdr1

# Import the database migration
mysql -u root -p mdrrm_arms < database/migrations/terminal_report_phase1.sql
```

**Or via phpMyAdmin:**
1. Open http://localhost/phpmyadmin
2. Select database: `mdrrm_arms`
3. Click "Import" tab
4. Choose file: `database/migrations/terminal_report_phase1.sql`
5. Click "Go"

### What This Migration Does:

✅ **Creates 9 New Tables:**
1. `disaster_events` - Master event tracker
2. `weather_data` - PAGASA forecasts
3. `water_level_monitoring` - Gauging stations
4. `water_supply_status` - Water service tracking
5. `response_teams` - Deployed teams/assets
6. `response_operations` - Operations log
7. `preparedness_actions` - Pre-disaster actions
8. `calamity_declarations` - State of calamity
9. `evacuation_centers` - EC details

✅ **Modifies 13 Existing Tables:**
- Adds `event_id` foreign key to all annexes
- Links everything to specific disaster events
- Adds missing fields (casualties, suspensions, etc.)
- Adds job titles to `users` table

✅ **Inserts Sample Data:**
- Creates sample event: ST "UWAN" (FUNG-WONG)

---

## 🔍 VERIFY INSTALLATION

After running the migration, check if tables were created:

```sql
-- Check new tables exist
SHOW TABLES LIKE '%disaster%';
SHOW TABLES LIKE '%weather%';
SHOW TABLES LIKE '%response%';

-- View sample disaster event
SELECT * FROM disaster_events;

-- Check event_id was added to existing tables
DESCRIBE annex1_related_incidents;
DESCRIBE annex2_affected_population;
DESCRIBE annex3_casualties;
```

---

## 📊 WHAT YOU CAN DO NOW

### Current Capabilities (50% Complete):

With existing data, Terminal Report can show:
- ✅ **Section II:** Pre-emptive Evacuation (annex2)
- ✅ **Section V.B:** Affected Population (annex2)
- ✅ **Section V.B.1:** Casualties (annex3)
- ✅ **Section V.D:** Damaged Houses (annex4)
- ✅ **Section V.E:** Class/Work Suspension (annex14, annex15)
- ✅ **Section V.F:** Cost of Damages (annex5, annex6)
- ✅ **Section VII:** Assistance Extended (annex20, annex21)
- ✅ **Section I.D:** Roads & Bridges (annex8)
- ✅ **Section I.C.1/3:** Electricity & Communications (annex9, annex11)

### Missing Sections (Need Data Entry):

- ❌ **Section I.A/B:** Weather Forecast (use `weather_data` table)
- ❌ **Section I.C:** Water Level (use `water_level_monitoring` table)
- ❌ **Section III:** Calamity Declaration (use `calamity_declarations` table)
- ❌ **Section IV:** Response Assets (use `response_teams` table)
- ❌ **Section VI:** Response Operations (use `response_operations` table)
- ❌ **Section VIII:** Preparedness Actions (use `preparedness_actions` table)

---

## 🚀 NEXT STEPS

### Option A: Generate Partial Report NOW (Recommended)
Start with what you have - I'll build the generator to show existing 50% data and placeholder text for missing sections.

**Timeline:** 2-3 hours to first working report

### Option B: Build Data Entry Forms First
Create forms to populate new tables before generating report.

**Timeline:** 1-2 days before first report

### Option C: Manual Data Entry + Generate
You manually insert data into new tables via phpMyAdmin, then I generate report.

**Timeline:** Depends on how much data you want to enter

---

## 📁 FOLDER STRUCTURE

```
c:\xampp\htdocs\mdr1\
├── database/
│   └── migrations/
│       └── terminal_report_phase1.sql ✅ CREATED
├── report/
│   └── terminal_report_generator.php (NEXT - Phase 2)
├── public/
│   └── terminal_report_form.php (NEXT - Phase 3)
└── TERMINAL_REPORT_SETUP_GUIDE.md ✅ YOU ARE HERE
```

---

## 🎯 PHASE 2 PREVIEW: Document Generation

Once database is ready, I'll create:

### `report/terminal_report_generator.php`
- Fetches data from all tables (existing + new)
- Generates 16-page Word document
- Exact format matching original
- Handles missing data gracefully

### Key Features:
```php
// Generate report for specific event
$generator = new TerminalReportGenerator();
$generator->setEventId(1); // ST "UWAN"
$generator->generate();
$generator->download('Terminal_Report_UWAN.docx');
```

---

## 🎯 PHASE 3 PREVIEW: Data Entry Forms

User-friendly forms for new tables:

1. **Weather Monitoring Form** → `weather_data`
2. **Water Level Form** → `water_level_monitoring`
3. **Response Teams Form** → `response_teams`
4. **Operations Log Form** → `response_operations`
5. **Preparedness Actions Form** → `preparedness_actions`
6. **Calamity Declaration Form** → `calamity_declarations`

---

## 📞 IMMEDIATE ACTIONS

### Right Now:

1. ✅ Run the database migration
2. ✅ Verify tables were created
3. ✅ Check sample data inserted
4. **THEN TELL ME:** Which next step do you prefer?
   - Option A: Generate partial report now?
   - Option B: Build forms first?
   - Option C: Manual data entry?

---

## 💡 PRO TIPS

### Linking Existing Data to Events:

After creating your first `disaster_events` entry, update existing annex records:

```sql
-- Example: Link all existing data to ST "UWAN" event (id=1)
UPDATE annex1_related_incidents SET event_id = 1 WHERE event_id IS NULL;
UPDATE annex2_affected_population SET event_id = 1 WHERE event_id IS NULL;
UPDATE annex3_casualties SET event_id = 1 WHERE event_id IS NULL;
-- ... repeat for all annex tables
```

### Weather Data Integration:

You mentioned `WEATHER_API_GUIDE.md` - we can integrate this!
```php
// Fetch weather from API and save to weather_data table
$weather = fetchWeatherFromAPI();
saveToWeatherDataTable($weather, $event_id);
```

---

## ⚠️ IMPORTANT NOTES

### Foreign Key Relationships:

All new tables reference `disaster_events.id`:
- If you delete a disaster event, related data is also deleted (CASCADE)
- Existing annex data won't be deleted (SET NULL)
- This ensures data integrity

### Event_ID Workflow:

```
1. Create disaster_event (e.g., "Typhoon Pepito")
2. Get event_id (e.g., 2)
3. All data entry forms use this event_id
4. Generate report filters by event_id
5. Historical reports available anytime
```

---

## 🎉 SUCCESS CRITERIA

Phase 1 is complete when:
- ✅ All 9 new tables created
- ✅ All 13 existing tables modified
- ✅ Foreign keys working
- ✅ Sample event inserted
- ✅ No SQL errors

---

## 🔄 ROLLBACK (If Needed)

If something goes wrong:

```sql
-- Drop new tables
DROP TABLE IF EXISTS evacuation_centers;
DROP TABLE IF EXISTS calamity_declarations;
DROP TABLE IF EXISTS preparedness_actions;
DROP TABLE IF EXISTS response_operations;
DROP TABLE IF EXISTS response_teams;
DROP TABLE IF EXISTS water_supply_status;
DROP TABLE IF EXISTS water_level_monitoring;
DROP TABLE IF EXISTS weather_data;
DROP TABLE IF EXISTS disaster_events;

-- Remove added columns from existing tables
ALTER TABLE annex1_related_incidents DROP COLUMN event_id;
ALTER TABLE annex2_affected_population DROP COLUMN event_id;
-- ... etc
```

---

## 📊 EXPECTED TIMELINE

- **Phase 1 (Database):** 30 minutes ✅ YOU ARE HERE
- **Phase 2 (Generator):** 4-6 hours
- **Phase 3 (Forms):** 2-3 days
- **Testing & Polish:** 1-2 days

**Total:** 5-7 working days to complete system

---

## 🚀 LET'S GO!

**Status:** Ready for Phase 1 database migration

**Next:** Tell me when migration is done, then choose:
- Generate partial report now? (Fast results)
- Build forms first? (Complete data entry system)
- Mix of both? (Partial report + forms in parallel)

---

**Questions? Issues? Let me know!** 🎯
