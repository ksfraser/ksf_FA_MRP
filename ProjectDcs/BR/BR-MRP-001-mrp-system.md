# BR-MRP-001 - Material Requirements Planning (MRP)

## Business Requirement

**Source**: webERP MRP system + WebErpMesv2 `methods_*` tables
**Module**: ksf_FA_MRP
**Status**: Proposed

### Problem Statement

Manufacturing and assembly operations need MRP to calculate:
- What components are needed (BOM explosion)
- When to order/purchase (lead time scheduling)
- How much to order (lot sizing)
- What can be produced (capacity constraints)

FA's current BOM module lacks the planning/scheduling layer.

### Business Value

- **Inventory Optimization**: Reduce stockouts and overstock
- **Planning Accuracy**: Calculate true requirements
- **Lead Time Management**: Order components in time for production
- **Cost Reduction**: Batch purchasing, reduce expediting

### Scope

#### In Scope
1. MRP parameters (planning horizon, time buckets)
2. Demand aggregation (sales orders, forecasts, safety stock)
3. BOM explosion (multi-level, phantom dissolution)
4. Net requirements calculation
5. Lot sizing methods (FOQ, EOQ, POQ, lot-for-lot)
6. Planned order generation (purchase requisitions, work orders)
7. MRP regeneration vs net change
8. Action messages (order, reschedule, cancel)

#### Out of Scope
1. Capacity planning (CRP - use planning module)
2. Finite scheduling
3. Shop floor control (use manufacturing module)
4. Demand forecasting

### Constraints

- PHP 7.3+ compatibility
- Must use existing FA BOM, stock, purchasing modules

### Dependencies

- ksf_FA_MRP (existing)
- ksf_FA_ManufacturingConsolidation (for work orders)
- ksf_FA_PurchaseOrderTracking

### Related Requirements

- FR-MRP-001-001: MRP parameters
- FR-MRP-001-002: Demand aggregation
- FR-MRP-001-003: BOM explosion
- FR-MRP-001-004: Net requirements
- FR-MRP-001-005: Lot sizing
- FR-MRP-001-006: Planned orders
- FR-MRP-001-007: Action messages
