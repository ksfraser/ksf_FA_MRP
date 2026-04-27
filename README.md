# ksf_FA_MRP - Manufacturing Resource Planning for FrontAccounting

**ksf_FA_MRP** - MRP module with Gantt visualization and optional Project Management integration.

## Features

- **Gantt Production Schedule** - Visual timeline of production orders
- **Work Center Capacity** - Track utilization vs capacity
- **Material Requirements** - Auto-calculate from BOM and shortages
- **PM Integration** - Link production to ksf_FA_ProjectManagement tasks

## Integration

### With FA Manufacturing
- Reads existing work orders from `workorders` table
- Uses work centers from `wo_manufacture` table
- Calculates material requirements from Bill of Materials

### With ksf_FA_ProjectManagement (optional)
- Creates tasks in PM when production scheduled
- Links production to projects/tasks
- Shows project references in Gantt

## Permissions

| Permission | Description |
|------------|-------------|
| MRP_VIEW | View MRP Gantt and reports |
| MRP_MANAGE | Manage production schedule |
| MRP_VIEW_COSTS | View cost reports |
| MRP_ADMIN | Admin settings |

## Database Tables

- `ksf_mrp_production_schedule` - Production schedule entries
- `ksf_mrp_workcenter_capacity` - Daily capacity tracking
- `ksf_mrp_material_requirements` - Material shortage alerts
- `ksf_mrp_settings` - Module settings

## Usage

1. Create work orders in FA Manufacturing
2. Activate ksf_FA_MRP module
3. View Gantt - production schedule visualized
4. Link to PM if module active

## Requirements

- FrontAccounting 2.4+
- PHP 8.1+
- ksf_Gantt (for Gantt rendering)
- FA Manufacturing module

## License

MIT