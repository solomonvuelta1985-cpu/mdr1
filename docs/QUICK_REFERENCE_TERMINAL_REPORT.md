# Terminal Report System - Quick Reference Card

## 🎯 One-Page Cheat Sheet

---

## 📍 Main Navigation Menu

```
Sidebar Menu:
├── Disaster Events       ← Create & manage events
├── Terminal Report       ← Generate reports
└── Annex 1-21           ← Enter data
```

---

## ⚡ Quick Actions

### Generate a Terminal Report (30 seconds)
```
1. Sidebar → Terminal Report
2. Select event from dropdown
3. Click "Generate Terminal Report"
4. Document downloads automatically
```

### Link Data to Event (1 minute)
```
1. Sidebar → Disaster Events
2. Click "Set as Active" on event
3. Purple box appears → Event is now active
4. Go to any Annex form → Data auto-links
```

### Create New Disaster Event (2 minutes)
```
1. Sidebar → Disaster Events
2. Click "Create New Event"
3. Fill in:
   - Event Name: ST "UWAN" (FUNG-WONG)
   - Type: Typhoon
   - Start Date: Nov 8, 2025
   - Status: Ongoing
4. Click "Create Event"
5. Click "Set as Active"
```

---

## 🎨 Visual Indicators

| Indicator | Meaning |
|-----------|---------|
| 🟣 **Purple Box** | Active event - data will link here |
| 🟡 **Yellow Warning** | No active event - data won't link |
| 🔵 **Blue Border** | Currently active event card |
| 🔴 **Red Badge** | Event is Ongoing |
| 🟢 **Green Badge** | Event has Ended |
| ⚫ **Gray Badge** | Event is Archived |

---

## 🔄 Typical Workflow

```
BEFORE DISASTER:
Create Event → Set Active → Monitor

DURING DISASTER:
Enter Annex 1 (Incidents)
Enter Annex 2 (Population)
Enter Annex 3 (Casualties)
Enter Annex 9 (Power)
Enter Annex 20 (Assistance)

AFTER DISASTER:
Complete remaining annexes
Update event status to "Ended"
Generate Terminal Report
```

---

## 📊 Report Sections (What's Included)

| Section | Data Source | Available? |
|---------|-------------|------------|
| I. Situation Overview | Weather, Roads, Power | ⚠️ Partial |
| II. Pre-emptive Evacuation | Annex 2 | ✅ Yes |
| III. Calamity Declaration | New table | ⚠️ Need data |
| IV. Response Assets | New table | ⚠️ Need data |
| V. Effects | Annex 3,4,5,6 | ✅ Yes |
| VI. Response Operations | New table | ⚠️ Need data |
| VII. Assistance Extended | Annex 20,21 | ✅ Yes |
| VIII. Preparedness | New table | ⚠️ Need data |

---

## 🛠️ Troubleshooting

| Problem | Solution |
|---------|----------|
| No events in dropdown | Create an event first |
| Data not linking | Set event as active |
| Document corrupted | Contact admin (column mismatch) |
| Can't see menu | Check if logged in |
| PHPWord error | Run: `composer install` |

---

## 📝 Event Status Guide

```
ONGOING    Use during active disaster
           ↓
ENDED      Use when disaster is over
           ↓
ARCHIVED   Use for historical records
```

---

## 💡 Pro Tips

✅ **Set event active BEFORE entering data**
✅ **Use official disaster names**
✅ **Update status when disaster ends**
✅ **Generate report while event is fresh**
✅ **Clear active event when switching tasks**

---

## 📞 Quick Help

**Can't find a feature?**
- Disaster Events → Manage events
- Terminal Report → Generate reports
- Annex Forms → Enter data

**Need to switch events?**
- Go to Disaster Events
- Click "Set as Active" on different event

**Want to unlink data?**
- Click "Clear Active Event" button
- Data won't link until you set active again

---

## 🔐 Important Notes

⚠️ **Only ONE event can be active at a time**
⚠️ **Deleting event doesn't delete annex data**
⚠️ **Active event persists until you change it**
⚠️ **Terminal Report needs event to be created first**

---

## 📅 Keyboard Shortcuts

None currently - all actions via clicking

---

## 🎓 Training Resources

- `TERMINAL_REPORT_IMPLEMENTATION.md` - Technical guide
- `EVENT_MANAGEMENT_IMPLEMENTATION.md` - Event system guide
- `HOW_TO_USE_EVENT_LINKING.md` - User manual
- `TERMINAL_REPORT_COMPLETION_SUMMARY.md` - Complete overview

---

**Version:** 1.0 | **Date:** January 14, 2025 | **System:** MDRRM-ARMS
