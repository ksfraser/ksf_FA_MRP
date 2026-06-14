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

## Development Workflow

All development is done in the **devel tree** (`~/Documents/ksf_FA_MRP`). Do **not** edit files in the UAT bind point directly.

### Workflow Steps
1. **Develop** in this repo (feature branches preferred)
2. **Test**: run repo-appropriate tests
3. **Lint**: `php -l` on modified PHP files (no syntax errors)
4. **Commit** and **Push** branch to GitHub
5. **Merge** to `master` when ready
6. **Push** `master` to GitHub
7. **Deploy** to UAT by pulling in the Infrastructure bind point:

   ```
   cd ~/ksf_Infrastructure/fa_modules/ksf_FA_MRP
   git stash -u
   git pull origin master
   git stash pop
   ```

### UAT Bind Point
| Path | Purpose |
|------|---------|
| `~/Documents/ksf_FA_MRP` | Devel tree — all development, testing, commits |
| `~/ksf_Infrastructure/fa_modules/ksf_FA_MRP` | UAT bind point — deployment target, integration testing (if mirrored) |

