# AGENTS.md - ksf_FA_MRP#

## Architecture Overview#

**FA Module** for Material Requirements Planning - BOM, supply planning, and procurement.

### Core Principles#
- **SOLID**, **DRY**, **TDD**, **DI**, **SRP**#

## Repository Structure#

```
ksf_FA_MRP/
├── sql/#
│   ├── fa_mrp_requirements.sql#
│   ├── fa_mrp_bom.sql#
│   └── fa_mrp_procurement.sql#
├── includes/#
│   ├── requirements_db.inc#
│   ├── bom_db.inc#
│   └── procurement_db.inc#
├── pages/#
├── hooks.php#
├── composer.json#
└── ProjectDocs/#
```

## Dependencies#

- **ksf_FA_MRP_Core** (business logic)#
- **FrontAccounting 2.4+**#
