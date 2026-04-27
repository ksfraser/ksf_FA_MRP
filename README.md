# ksf_FA_MRP - Manufacturing Resource Planning for FrontAccounting

**ksf_FA_MRP** is a Manufacturing Resource Planning (MRP) module for FrontAccounting that provides Gantt-based production scheduling, work center capacity tracking, material requirements planning from Bill of Materials (BOM), and optional integration with ksf_FA_ProjectManagement for project-linked manufacturing.

## Module Overview

| Property | Value |
|----------|-------|
| Version | 0.1.0 |
| Module ID | ksf_FA_MRP |
| Dependencies | ksf_Gantt (required), ksf_FA_ProjectManagement (optional), FA Manufacturing |
| Platform | FrontAccounting 2.4+ |
| License | MIT |

---

## Features

### 1. Gantt Production Schedule
- Visual timeline of production orders using ksf_Gantt library
- Color-coded by status and priority
- Project/task links when PM module is active
- Drag-and-drop scheduling (future enhancement)
- Export to JSON format

### 2. Work Center Capacity Tracking
- Track daily capacity vs utilization for each work center
- Identify capacity bottlenecks
- 7-day rolling capacity view
- Available hours calculation

### 3. Material Requirements Planning
- Auto-calculate material requirements from BOM
- Show on-hand inventory from stock_master
- Show on-order quantities from purchase orders
- Highlight material shortages
- Create purchase suggestions from shortages

### 4. PM Integration (Optional)
- Links production schedules to projects and tasks
- Auto-creates PM tasks when work orders are created
- Shows project references in Gantt chart
- Resource utilization references PM assignments

---

## Installation

### Prerequisites

1. **FrontAccounting 2.4+** installed
2. **ksf_Gantt** library installed (for Gantt rendering)
3. **FA Manufacturing** module enabled
4. PHP 8.1+

### Installation Steps

1. Copy the `ksf_FA_MRP` folder to `/modules/`
2. Navigate to: **Setup > Modules** in FA
3. Find "KSF Manufacturing Resource Planning"
4. Click **Install** or **Activate**

On installation, the module creates:
- Production schedule table
- Work center capacity table
- Material requirements table
- Settings table

### Menu Items Created

After installation, these menu items appear under **Manufacturing**:

| Menu Item | Description |
|----------|-------------|
| MRP Schedule | Gantt production schedule view |
| Material Requirements | Material shortage analysis |
| Capacity Planning | Work center capacity view |

---

## Database Tables

### ksf_mrp_production_schedule
Stores production schedule entries with work order references.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| workorder_id | VARCHAR(20) | Reference to work order |
| item_code | VARCHAR(20) | Item being manufactured |
| quantity | DECIMAL(15,3) | Production quantity |
| start_date | DATE | Scheduled start date |
| end_date | DATE | Scheduled end date |
| scheduled_hours | DECIMAL(10,2) | Estimated hours |
| priority | VARCHAR(20) | Priority (Low/Normal/High/Critical) |
| status | VARCHAR(30) | Status (Scheduled/In Progress/Completed/Delayed) |
| workcenter_id | INT | Assigned work center |
| project_id | VARCHAR(20) | Linked project (PM) |
| task_id | VARCHAR(20) | Linked task (PM) |
| notes | TEXT | Additional notes |
| created_by | VARCHAR(100) | User who created |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

### ksf_mrp_workcenter_capacity
Tracks daily capacity utilization per work center.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| workcenter_id | VARCHAR(20) | Work center reference |
| date | DATE | Date |
| capacity_hours | DECIMAL(6,2) | Available hours (default 8) |
| utilized_hours | DECIMAL(6,2) | Used hours |
| available | TINYINT | Available flag |

### ksf_mrp_material_requirements
Stores calculated material requirements from BOM.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| item_code | VARCHAR(20) | Material item code |
| required_date | DATE | Date material needed |
| quantity_required | DECIMAL(15,3) | Total required |
| quantity_on_hand | DECIMAL(15,3) | Current stock |
| quantity_on_order | DECIMAL(15,3) | On purchase order |
| shortage | DECIMAL(15,3) | Shortage amount |
| workorder_id | VARCHAR(20) | Related work order |
| priority | VARCHAR(20) | Priority |
| status | VARCHAR(30) | Status (Pending/Shortage/OK) |

### ksf_mrp_settings
Module configuration settings.

| Setting | Default | Description |
|---------|---------|-------------|
| default_capacity_per_day | 8 | Default work hours per day |
| lead_time_days | 3 | Default lead time for auto-scheduling |
| auto_link_pm | 1 | Auto-link to PM when active |
| gantt_day_width | 50 | Gantt chart day width in pixels |
| gantt_row_height | 45 | Gantt chart row height in pixels |

---

## Integration

### With FA Manufacturing

The module integrates with FA Manufacturing by:

1. **Reading Work Orders**: Reads released work orders from `workorders` table
2. **Work Centers**: Uses work centers from `wo_manufacture` table
3. **BOM Integration**: Uses `bom` table for material requirements
4. **Stock Levels**: Pulls on-hand quantities from `stock_master`
5. **Purchase Orders**: Gets open PO quantities from `purch_order_details`

### With ksf_FA_ProjectManagement

When ksf_FA_ProjectManagement is installed and active:

1. **Task Creation**: Auto-creates tasks in PM when production scheduled
2. **Project Links**: Links production to projects via project_id
3. **Task Links**: Links production to specific tasks via task_id
4. **Gantt Display**: Shows project references in Gantt chart

Integration is controlled by the `auto_link_pm` setting.

### With ksf_Gantt

Uses ksf_Gantt library for Gantt rendering:
- GanttTask entity for individual schedule items
- GanttChart for the complete schedule
- GanttRenderer for HTML output

---

## API Reference

### Database Functions (includes/mrp_db.inc)

#### Production Schedule Functions

```php
// Add new schedule entry
int add_mrp_schedule(array $data)

// Get schedule entries
mixed get_mrp_schedule(int $id = null, array $filters = [])

// Update schedule entry
bool update_mrp_schedule(int $id, array $data)

// Delete schedule entry
void delete_mrp_schedule(int $id)

// Link schedule to project
bool link_schedule_to_project(int $schedule_id, string $project_id, string $task_id = null)

// Get schedule count
int get_schedule_count(string $status = null)
```

#### Work Center Functions

```php
// Get all work centers
mixed get_mrp_workcenters()

// Get capacity for specific work center/date
array get_workcenter_capacity(string $workcenter_id, string $date)

// Update work center capacity
void update_workcenter_capacity(string $workcenter_id, string $date, float $utilized_hours)
```

#### Material Requirements Functions

```php
// Calculate material requirements from BOM
array calculate_material_requirements(string $item_code, string $required_date, float $quantity)

// Get material shortages
mixed get_material_shortages()
```

#### Settings Functions

```php
// Get MRP setting
string get_mrp_setting(string $key)

// Set MRP setting
void set_mrp_setting(string $key, mixed $value)

// Check if PM module is active
bool is_pm_active()
```

### Hook Functions (hooks.php)

```php
// Module installation
void ksf_FA_MRP_install()

// Module uninstallation
void ksf_FA_MRP_uninstall()

// Work order created hook
void ksf_FA_MRP_workorder_created(string $workorder_id, array $data)
```

---

## Permissions

The module defines these permissions:

| Permission | Code | Description |
|------------|------|-------------|
| View MRP | MRP_VIEW | View Gantt and reports |
| Manage MRP | MRP_MANAGE | Manage production schedule |
| View Costs | MRP_VIEW_COSTS | View cost reports |
| Admin | MRP_ADMIN | Admin settings |

---

## Usage

### Basic Workflow

1. **Create Work Orders**: Create work orders in FA Manufacturing module
2. **View Gantt**: Navigate to Manufacturing > MRP Schedule to view Gantt
3. **Link to PM** (optional): Click "Link to PM" to link schedules to projects
4. **Check Materials**: Review Material Requirements for shortages
5. **Check Capacity**: Review Work Center Capacity for bottlenecks

### Manual Schedule Entry

If not using FA Manufacturing work orders:

1. Go to MRP Schedule page
2. Click **Schedule** tab
3. Click **Add Schedule**
4. Fill in item code, quantity, dates, work center
5. Save

### Recalculating Materials

To recalculate all material requirements:

1. Go to **Material Requirements** page
2. Click **Recalculate All**
3. Review shortages and status

---

## Troubleshooting

### Gantt Not Displaying
- Verify ksf_Gantt is installed
- Check browser console for JavaScript errors
- Verify production schedules exist

### Material Calculations Wrong
- Verify BOM exists for item
- Check stock_master has correct quantities
- Verify purchase orders are linked correctly

### PM Integration Not Working
- Verify ksf_FA_ProjectManagement is installed and active
- Check auto_link_pm setting is enabled
- Verify project_id and task_id fields are populated

---

## File Structure

```
ksf_FA_MRP/
├── README.md                  # This file
├── ksf_FA_MRP.php           # Module class
├── hooks.php                # Installation hooks
├── sql/
│   └── install.sql          # Database schema
├── includes/
│   └── mrp_db.inc          # Database functions
├── pages/
│   └── gantt.php           # Main page (Gantt, Schedule, Materials, Capacity)
└── ProjectDcs/
    ├── Functional Requirements.md
    ├── Architecture.md
    ├── Test Plan.md
    └── UAT Plan.md
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 0.1.0 | 2024-XX-XX | Initial release |

---

## License

MIT License - See LICENSE file for details

---

## Support

For issues and questions:
- GitHub Issues: [link]
- Email: [link]

---

## Acknowledgments

- FrontAccounting Community
- ksf_Gantt library
- Contributors
