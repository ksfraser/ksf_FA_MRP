# Architecture - ksf_FA_MRP

## Overview

This document describes the technical architecture for the Manufacturing Resource Planning (MRP) module, including the layered architecture, component design, database schema, and integration patterns.

---

## 1. System Architecture

### 1.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │ Gantt   │ │Schedule │ │Materials│ │Capacity│   │
│  │  Page   │ │  Page   │ │  Page   │ │  Page   │   │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘   │
│       │           │           │           │           │         │
│       └───────────┴───────────┴───────────┘           │
│                         │                             │
├─────────────────────────┼─────────────────────────────┤
│                    Service Layer                      │
│  ┌──────────────────────────────────────────────────┐  │
│  │              mrp_db.inc                   │  │
│  │   Database functions (CRUD operations)          │  │
│  └──────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────┐  │
│  │           hooks.php                          │  │
│  │   Installation hooks and events             │  │
│  └──────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────┤
│                    Business Layer                       │
│  ┌──────────────────────────────────────────────────┐  │
│  │           ksf_FA_MRP Module Class            │  │
│  │   - Permissions                            │  │
│  │   - Lifecycle management                   │  │
│  │   - Settings                            │  │
│  └──────────────────────────────────────────────────┘  │
├──────────────────────────────────────────────────────────┤
│                    Data Layer                          │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐              │
│  │Schedule  │ │Capacity │ │Materials │              │
│  │  Table   │ │  Table   │ │  Table   │              │
│  └──────────┘ └──────────┘ └──────────┘              │
├──────────────────────────────────────────────────────────┤
│                  Integration Layer                      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐              │
│  │FA Mfg    │ │ksf_Gantt │ │ksf-PM   │              │
│  │(WO,BOM)  │ │ Library  │ │ Module  │              │
│  └──────────┘ └──────────┘ └──────────┘              │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 Module Structure

```
ksf_FA_MRP/
├── ksf_FA_MRP.php          # Module class with permissions
├── hooks.php               # FA lifecycle hooks
├── README.md              # Documentation
├── sql/
│   └── install.sql        # Schema creation
├── includes/
│   └── mrp_db.inc     # Database functions
├── pages/
│   └── gantt.php     # Main page controller (all sections)
└── ProjectDcs/
    ├── Functional Requirements.md
    ├── Architecture.md
    ├── Test Plan.md
    └── UAT Plan.md
```

---

## 2. Component Design

### 2.1 Module Class (ksf_FA_MRP.php)

The main module class provides lifecycle management and permissions.

**Purpose**: Central module controller

**Properties**:
- `module_name` - Module identifier
- `module_version` - Version string
- `dependent_modules` - Module dependencies

**Methods**:
```php
class ksf_FA_MRP extends Module 
{
    function install()            // Create tables, add menu items
    function remove($force)      // Drop tables
    function activate()          // Enable module
    function deactivate()        // Disable module
    function get_permissions()   // Define permissions
}
```

### 2.2 Hook Functions (hooks.php)

Provides installation and event hooks.

**Functions**:
```php
// Installation hook
function ksf_FA_MRP_install()
    // Creates four tables: production_schedule, workcenter_capacity, 
    //                  material_requirements, settings
    // Adds menu items to Manufacturing section
    // Sets default settings

// Uninstallation hook
function ksf_FA_MRP_uninstall()
    // Drops all four tables

// Work order created event
function ksf_FA_MRP_workorder_created($workorder_id, $data)
    // Auto-links to PM when enabled
    // Creates schedule entry

// Helper functions
function is_pm_active()       // Check PM module status
function get_mrp_setting()   // Get setting value
function set_mrp_default_settings()  // Initialize defaults
```

### 2.3 Database Functions (mrp_db.inc)

Provides procedural database operations.

#### Production Schedule Functions
```php
// Create schedule
int add_mrp_schedule(array $data)

// Read schedules
mixed get_mrp_schedule(int $id = null, array $filters = [])

// Update schedule
bool update_mrp_schedule(int $id, array $data)

// Delete schedule
void delete_mrp_schedule(int $id)

// Link to project
bool link_schedule_to_project(int $schedule_id, string $project_id, string $task_id = null)

// Get count
int get_schedule_count(string $status = null)
```

#### Work Center Functions
```php
// Get all work centers
mixed get_mrp_workcenters()

// Get capacity for workcenter/date
array get_workcenter_capacity(string $workcenter_id, string $date)

// Update capacity
void update_workcenter_capacity(string $workcenter_id, string $date, float $utilized_hours)
```

#### Material Functions
```php
// Calculate requirements from BOM
array calculate_material_requirements(string $item_code, 
                                  string $required_date, 
                                  float $quantity)

// Get shortages
mixed get_material_shortages()
```

#### Settings Functions
```php
// Get setting
string get_mrp_setting(string $key)

// Set setting
void set_mrp_setting(string $key, mixed $value)

// Check PM active
bool is_pm_active()
```

---

## 3. Page Architecture

### 3.1 Main Page Controller (pages/gantt.php)

The gantt.php file acts as the main controller with section-based routing.

```
URL: ?page=mrp/gantt.php&section={gantt|schedule|materials|capacity|settings}
```

#### Section Handlers
- **gantt**: Gantt chart view using ksf_Gantt
- **schedule**: Schedule CRUD operations
- **materials**: Material requirements view
- **capacity**: Work center capacity view
- **settings**: Module settings

### 3.2 UI Components

```php
// Navigation
function mrp_navigation()
    // Renders tab navigation

// Gantt Page
function mrp_gantt_page()
    // Uses GanttRenderer from ksf_Gantt
    // Shows task list table

// Schedule Page
function mrp_schedule_list()
function mrp_schedule_form($action)

// Materials Page
function mrp_materials_page()

// Capacity Page
function mrp_capacity_page()

// Settings Page
function mrp_settings_page()
```

---

## 4. Database Schema

### 4.1 Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────┐
│   workorders    │       │ wo_manufacture  │
│  (FA Mfg)     │       │  (FA Mfg)    │
└────────┬────────┘       └────────┬────────┘
         │                        │
         │ 1:N                  │ 1:N
         ▼                        ▼
┌─────────────────────────────────────────────────┐
│         ksf_mrp_production_schedule              │
│ ┌────────────────────────────────────────────┐ │
│ │ id (PK)                                  │ │
│ │ workorder_id (FK) ────────► workorders    │ │
│ │ item_code ───────────────► stock_master     │ │
│ │ workcenter_id (FK) ──────► wo_manufacture │ │
│ │ project_id (FK, optional)                 │ │
│ │ task_id (FK, optional)                   │ │
│ │ start_date, end_date                   │ │
│ │ priority, status                     │ │
│ └────────────────────────────────────────────┘ │
└──────────────────────────┬──────────────────────┘
                       │
                       │ 1:N
                       ▼
┌─────────────────────────────────────────────────┐
│       ksf_mrp_workcenter_capacity            │
│ ┌────────────────────────────────────────────┐ │
│ │ id (PK)                                  │ │
│ │ workcenter_id (FK) ──────► wo_manufacture │ │
│ │ date                                     │ │
│ │ capacity_hours                           │ │
│ │ utilized_hours                          │ │
│ └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
         │
         │ (material calc)
         ▼
┌─────────────────────────────────────────────────┐
│     ksf_mrp_material_requirements             │
│ ┌────────────────────────────────────────────┐ │
│ │ id (PK)                                  │ │
│ │ item_code ──────────► stock_master       │ │
│ │ required_date                           │ │
│ │ quantity_required                     │ │
│ │ quantity_on_hand ──────► stock_master  │ │
│ │ quantity_on_order ──────► purch_orders │ │
│ │ shortage                            │ │
│ │ workorder_id (FK) ──────► workorders │ │
│ │ status                              │ │
│ └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

### 4.2 Table Definitions

#### ksf_mrp_production_schedule
```sql
CREATE TABLE `@TB_PREF@ksf_mrp_production_schedule` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `workorder_id` VARCHAR(20) DEFAULT NULL,
    `item_code` VARCHAR(20) NOT NULL,
    `quantity` DECIMAL(15,3) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE DEFAULT NULL,
    `scheduled_hours` DECIMAL(10,2) DEFAULT 0,
    `priority` VARCHAR(20) DEFAULT 'Normal',
    `status` VARCHAR(30) DEFAULT 'Scheduled',
    `workcenter_id` INT(11) DEFAULT NULL,
    `project_id` VARCHAR(20) DEFAULT NULL,
    `task_id` VARCHAR(20) DEFAULT NULL,
    `notes` TEXT,
    `created_by` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_workorder` (`workorder_id`),
    KEY `idx_item` (`item_code`),
    KEY `idx_dates` (`start_date`, `end_date`),
    KEY `idx_project_task` (`project_id`, `task_id`),
    KEY `idx_workcenter` (`workcenter_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### ksf_mrp_workcenter_capacity
```sql
CREATE TABLE `@TB_PREF@ksf_mrp_workcenter_capacity` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `workcenter_id` VARCHAR(20) NOT NULL,
    `date` DATE NOT NULL,
    `capacity_hours` DECIMAL(6,2) DEFAULT 8.00,
    `utilized_hours` DECIMAL(6,2) DEFAULT 0.00,
    `available` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_workcenter_date` (`workcenter_id`, `date`),
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### ksf_mrp_material_requirements
```sql
CREATE TABLE `@TB_PREF@ksf_mrp_material_requirements` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_code` VARCHAR(20) NOT NULL,
    `required_date` DATE NOT NULL,
    `quantity_required` DECIMAL(15,3) NOT NULL,
    `quantity_on_hand` DECIMAL(15,3) DEFAULT 0,
    `quantity_on_order` DECIMAL(15,3) DEFAULT 0,
    `shortage` DECIMAL(15,3) DEFAULT 0,
    `workorder_id` VARCHAR(20) DEFAULT NULL,
    `priority` VARCHAR(20) DEFAULT 'Normal',
    `status` VARCHAR(30) DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_item` (`item_code`),
    KEY `idx_required_date` (`required_date`),
    KEY `idx_workorder` (`workorder_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### ksf_mrp_settings
```sql
CREATE TABLE `@TB_PREF@ksf_mrp_settings` (
    `setting_id` VARCHAR(50) NOT NULL,
    `setting_value` TEXT,
    PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 5. Integration Patterns

### 5.1 FA Manufacturing Integration

The module integrates with FA Manufacturing:

#### Database Tables Used
- `workorders` - Work order data (read only)
- `wo_manufacture` - Work centers (read only)
- `bom` - Bill of Materials (read only)
- `stock_master` - Inventory (read only)
- `purch_order_details` - PO details (read only)

#### Integration via Hooks
```php
// Work order created hook
function ksf_FA_MRP_workorder_created($workorder_id, $data) {
    // Auto-create schedule from work order
    // Auto-link to PM if enabled
}
```

### 5.2 ksf_Gantt Integration

Uses ksf_Gantt library for Gantt visualization:

```php
// Include required classes
require_once __DIR__ . '/../../ksf_Gantt/src/Ksfraser/Gantt/Entity/GanttTask.php';
require_once __DIR__ . '/../../ksf_Gantt/src/Ksfraser/Gantt/Entity/GanttChart.php';
require_once __DIR__ . '/../../ksf_Gantt/src/Ksfraser/Gantt/Service/GanttRenderer.php';

// Create tasks
$task = new GanttTask($id, $name, $start, $end);
$task->setProgress(50);
$task->setStatus('in_progress');

// Add to chart
$chart = new GanttChart('mrp-schedule', 'Production Schedule');
$chart->addTask($task);

// Render
$renderer = new GanttRenderer($options);
echo $renderer->renderHtml($chart);
```

### 5.3 ksf_FA_ProjectManagement Integration

Optional integration when PM module is active:

```php
// Check if PM active
if (is_pm_active()) {
    // Link schedule to project
    link_schedule_to_project($schedule_id, $project_id, $task_id);
    
    // Auto-create task in PM
    db_query("INSERT INTO fa_pm_tasks ...");
}
```

---

## 6. Security Architecture

### 6.1 Permission Model

Defined in ksf_FA_MRP.php:

| Permission | Code | Description |
|------------|------|-------------|
| View MRP | MRP_VIEW | View Gantt and reports |
| Manage MRP | MRP_MANAGE | Manage schedule |
| View Costs | MRP_VIEW_COSTS | View cost reports |
| Admin | MRP_ADMIN | Admin settings |

### 6.2 Access Control

```php
// Permission check in pages
if (!check_fa_access('MRP_VIEW', false)) {
    display_error("Access denied");
    exit;
}
```

### 6.3 Data Validation

- SQL injection prevention via `db_escape()`
- Input sanitization via form validation
- Required field validation in business logic

---

## 7. Design Patterns

### 7.1 Patterns Used

| Pattern | Implementation |
|--------|---------------|
| Module Pattern | ksf_FA_MRP extends Module |
| Data Access Object | mrp_db.inc functions |
| Event Hooks | hooks.php |
| Page Controller | gantt.php routing |
| Settings Storage | Settings table |

### 7.2 Data Flow

```
User Request → gantt.php → Section Handler → mrp_db.inc → Database
                  ↓
              ksf_Gantt → HTML Output
```

---

## 8. Configuration

### 8.1 Default Settings

Stored in ksf_mrp_settings:

| Setting | Default | Type |
|---------|---------|------|
| default_capacity_per_day | 8 | int |
| lead_time_days | 3 | int |
| auto_link_pm | 1 | bool |
| gantt_day_width | 50 | int |
| gantt_row_height | 45 | int |

### 8.2 Menu Items

Created on installation:

```php
add_menu_item("Manufacturing", "?page=*", "manufacturing", "ksf_MRP", "MRP Schedule", "MRP_GANTT");
add_menu_item("Manufacturing", "?page=*", "manufacturing", "ksf_MRP", "Material Requirements", "MRP_MATERIAL");
add_menu_item("Manufacturing", "?page=*", "manufacturing", "ksf_MRP", "Capacity Planning", "MRP_CAPACITY");
```

---

## 9. Deployment

### 9.1 Installation

1. Copy module to `/modules/ksf_FA_MRP`
2. Activate via FA Modules admin
3. SQL creates tables
4. Permissions created
5. Menu items added

### 9.2 Dependencies

- FrontAccounting 2.4+
- PHP 8.1+
- ksf_Gantt library (required)
- FA Manufacturing (required)
- ksf_FA_ProjectManagement (optional)

---

## 10. Error Handling

### 10.1 Error Types

| Error | Handling |
|-------|----------|
| Database errors | Display via FA error function |
| Permission denied | Exit with error message |
| Missing data | Show informational message |
| Integration failure | Log and notify |

### 10.2 Logging

- Database timestamps for audit
- FA error logging for issues
- Activity tracking via timestamps
