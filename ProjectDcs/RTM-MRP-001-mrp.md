# RTM-MRP-001-mrp - MRP Requirements Traceability Matrix

**Business Requirement**: BR-MRP-001
**Module**: ksf_FA_MRP
**Version**: 1.0.0
**Status**: Proposed
**PHP Version**: 7.3+

---

## 1. Document Information

| Field | Value |
|-------|-------|
| Project | ksf_FA_MRP Material Requirements Planning |
| Business Requirement | BR-MRP-001 |
| Created | 2026-09-07 |
| Last Updated | 2026-09-07 |

---

## 2. Traceability Matrix

### 2.1 Business Requirement to Functional Requirements

| BR ID | BR Name | FR ID | FR Name | Status |
|-------|---------|-------|---------|--------|
| BR-MRP-001 | Material Requirements Planning (MRP) | FR-MRP-001-001 | MRP Parameters | Proposed |
| BR-MRP-001 | Material Requirements Planning (MRP) | FR-MRP-001-002 | Demand Aggregation | Proposed |
| BR-MRP-001 | Material Requirements Planning (MRP) | FR-MRP-001-003 | BOM Explosion | Proposed |
| BR-MRP-001 | Material Requirements Planning (MRP) | FR-MRP-001-004 | Net Requirements Calculation | Proposed |
| BR-MRP-001 | Material Requirements Planning (MRP) | FR-MRP-001-005 | Lot Sizing Methods | Proposed |
| BR-MRP-001 | Material Requirements Planning (MRP) | FR-MRP-001-006 | Planned Order Generation | Proposed |
| BR-MRP-001 | Material Requirements Planning (MRP) | FR-MRP-001-007 | Action Messages | Proposed |

### 2.2 Functional Requirements to Acceptance Criteria

#### FR-MRP-001-001: MRP Parameters

| FR ID | AC ID | Acceptance Criterion | Test Method | Status |
|-------|-------|---------------------|-------------|--------|
| FR-MRP-001-001 | AC-001-01 | Parameters stored in `0_mrp_parameters` table | UT-MRP-001-001-001 | Proposed |
| FR-MRP-001-001 | AC-001-02 | Each parameter has unique `parameter_id` | Unit Test | Proposed |
| FR-MRP-001-001 | AC-001-03 | Parameter values stored as TEXT for flexibility | Manual Inspection | Proposed |
| FR-MRP-001-001 | AC-001-04 | Parameters persist across MRP runs | Integration Test | Proposed |
| FR-MRP-001-001 | AC-001-05 | `planning_horizon_days` defaults to 30 | Unit Test | Proposed |
| FR-MRP-001-001 | AC-001-06 | `time_bucket` defaults to WEEK | Unit Test | Proposed |
| FR-MRP-001-001 | AC-001-07 | `lot_sizing_method` defaults to LFL | Unit Test | Proposed |
| FR-MRP-001-001 | AC-001-08 | `phantom_dissolve` defaults to 1 (enabled) | Unit Test | Proposed |
| FR-MRP-001-001 | AC-001-09 | Parameters can be retrieved by ID | UT-MRP-001-001-001 | Proposed |
| FR-MRP-001-001 | AC-001-10 | Parameters can be updated | Integration Test | Proposed |
| FR-MRP-001-001 | AC-001-11 | Invalid parameter ID returns null | Unit Test | Proposed |

#### FR-MRP-001-002: Demand Aggregation

| FR ID | AC ID | Acceptance Criterion | Test Method | Status |
|-------|-------|---------------------|-------------|--------|
| FR-MRP-001-002 | AC-002-01 | Sales orders aggregated by item and due date | Unit Test | Proposed |
| FR-MRP-001-002 | AC-002-02 | Forecasts aggregated by item and due date | Unit Test | Proposed |
| FR-MRP-001-002 | AC-002-03 | Safety stock levels retrieved per item | Unit Test | Proposed |
| FR-MRP-001-002 | AC-002-04 | Demand types distinguished (SO, WO, FC) | Unit Test | Proposed |
| FR-MRP-001-002 | AC-002-05 | Demands within planning horizon only | Integration Test | Proposed |
| FR-MRP-001-002 | AC-002-06 | Demands stored in `0_mrp_demands` table | UT-MRP-001-001-001 | Proposed |

#### FR-MRP-001-003: BOM Explosion

| FR ID | AC ID | Acceptance Criterion | Test Method | Status |
|-------|-------|---------------------|-------------|--------|
| FR-MRP-001-003 | AC-003-01 | Single-level BOM explosion implemented | UT-MRP-001-001-001 | Proposed |
| FR-MRP-001-003 | AC-003-02 | Multi-level BOM explosion implemented | UT-MRP-001-001-001 | Proposed |
| FR-MRP-001-003 | AC-003-03 | Phantom BOM items dissolved | UT-MRP-001-001-001 | Proposed |
| FR-MRP-001-003 | AC-003-04 | Component quantities multiplied by parent quantity | UT-MRP-001-001-001 | Proposed |
| FR-MRP-001-003 | AC-003-05 | Explosion respects low-level coding | Unit Test | Proposed |
| FR-MRP-001-003 | AC-003-06 | Circular BOM references detected and prevented | Unit Test | Proposed |
| FR-MRP-001-003 | AC-003-07 | Maximum explosion depth of 20 levels enforced | UT-MRP-001-001-001 | Proposed |

#### FR-MRP-001-004: Net Requirements Calculation

| FR ID | AC ID | Acceptance Criterion | Test Method | Status |
|-------|-------|---------------------|-------------|--------|
| FR-MRP-001-004 | AC-004-01 | Net requirement = Gross - On-Hand - On-Order + Safety Stock | Unit Test | Proposed |
| FR-MRP-001-004 | AC-004-02 | On-hand inventory retrieved from `0_stock_master` | Unit Test | Proposed |
| FR-MRP-001-004 | AC-004-03 | On-order quantities from open purchase orders | Unit Test | Proposed |
| FR-MRP-001-004 | AC-004-04 | Scheduled receipts considered in calculation | Integration Test | Proposed |
| FR-MRP-001-004 | AC-004-05 | Negative net requirements converted to zero | Unit Test | Proposed |
| FR-MRP-001-004 | AC-004-06 | Requirements calculated per time bucket | Unit Test | Proposed |

#### FR-MRP-001-005: Lot Sizing Methods

| FR ID | AC ID | Acceptance Criterion | Test Method | Status |
|-------|-------|---------------------|-------------|--------|
| FR-MRP-001-005 | AC-005-01 | Lot-for-Lot (LFL) orders exact quantities | Unit Test | Proposed |
| FR-MRP-001-005 | AC-005-02 | Economic Order Quantity (EOQ) minimizes total cost | Unit Test | Proposed |
| FR-MRP-001-005 | AC-005-03 | Fixed Order Quantity (FOQ) rounds up to fixed size | Unit Test | Proposed |
| FR-MRP-001-005 | AC-005-04 | Period Order Quantity (POQ) groups by period | Unit Test | Proposed |
| FR-MRP-001-005 | AC-005-05 | Lot sizing method configurable per MRP run | Integration Test | Proposed |
| FR-MRP-001-005 | AC-005-06 | EOQ calculation uses annual demand, ordering cost, holding cost | Unit Test | Proposed |

#### FR-MRP-001-006: Planned Order Generation

| FR ID | AC ID | Acceptance Criterion | Test Method | Status |
|-------|-------|---------------------|-------------|--------|
| FR-MRP-001-006 | AC-006-01 | Planned orders stored in `0_mrp_planned_orders` table | Integration Test | Proposed |
| FR-MRP-001-006 | AC-006-02 | Order quantities from lot sizing calculation | Unit Test | Proposed |
| FR-MRP-001-006 | AC-006-03 | Order due dates from net requirements | Unit Test | Proposed |
| FR-MRP-001-006 | AC-006-04 | Start dates calculated from due date minus lead time | Unit Test | Proposed |
| FR-MRP-001-006 | AC-006-05 | Planned orders can be firmable | Integration Test | Proposed |
| FR-MRP-001-006 | AC-006-06 | Order type determined by item (PO for purchased, WO for manufactured) | Unit Test | Proposed |

#### FR-MRP-001-007: Action Messages

| FR ID | AC ID | Acceptance Criterion | Test Method | Status |
|-------|-------|---------------------|-------------|--------|
| FR-MRP-001-007 | AC-007-01 | ORDER messages for new planned orders | Unit Test | Proposed |
| FR-MRP-001-007 | AC-007-02 | RESCHEDULE messages for date changes | Unit Test | Proposed |
| FR-MRP-001-007 | AC-007-03 | CANCEL messages for orders no longer needed | Unit Test | Proposed |
| FR-MRP-001-007 | AC-007-04 | Messages stored in `0_mrp_action_messages` table | Integration Test | Proposed |
| FR-MRP-001-007 | AC-007-05 | Messages prioritized by action type | Unit Test | Proposed |
| FR-MRP-001-007 | AC-007-06 | Messages can be acknowledged | Integration Test | Proposed |

### 2.3 Functional Requirements to Unit Tests

| FR ID | UT ID | Test Name | Status |
|-------|-------|-----------|--------|
| FR-MRP-001-001 | UT-MRP-001-001-001 | BOM Explosion Unit Test | Proposed |
| FR-MRP-001-002 | UT-MRP-001-001-001 | BOM Explosion Unit Test | Proposed |
| FR-MRP-001-003 | UT-MRP-001-001-001 | BOM Explosion Unit Test | Proposed |
| FR-MRP-001-004 | UT-MRP-001-001-001 | BOM Explosion Unit Test | Proposed |
| FR-MRP-001-005 | UT-MRP-001-001-001 | BOM Explosion Unit Test | Proposed |
| FR-MRP-001-006 | UT-MRP-001-001-001 | BOM Explosion Unit Test | Proposed |
| FR-MRP-001-007 | UT-MRP-001-001-001 | BOM Explosion Unit Test | Proposed |

### 2.4 Functional Requirements to UAT Cases

| FR ID | UAT ID | UAT Scenario | Status |
|-------|--------|--------------|--------|
| FR-MRP-001-001 | UAT-MRP-001-01 | MRP Parameters Configuration | Proposed |
| FR-MRP-001-002 | UAT-MRP-001-02 | Demand Aggregation from Multiple Sources | Proposed |
| FR-MRP-001-003 | UAT-MRP-001-03 | BOM Explosion for Multi-Level Product | Proposed |
| FR-MRP-001-004 | UAT-MRP-001-04 | Net Requirements Calculation | Proposed |
| FR-MRP-001-005 | UAT-MRP-001-05 | All Lot Sizing Methods | Proposed |
| FR-MRP-001-006 | UAT-MRP-001-06 | Planned Order Generation | Proposed |
| FR-MRP-001-007 | UAT-MRP-001-07 | Action Message Review and Acknowledge | Proposed |

---

## 3. Database Schema Coverage

| Table | FR Coverage | Description |
|-------|-------------|-------------|
| `0_mrp_parameters` | FR-MRP-001-001 | MRP configuration parameters |
| `0_mrp_demands` | FR-MRP-001-002 | Aggregated demands |
| `0_mrp_planned_orders` | FR-MRP-001-006 | Generated planned orders |
| `0_mrp_action_messages` | FR-MRP-001-007 | Order action messages |

---

## 4. Component Coverage

| Component | Class | FR Coverage |
|-----------|-------|-------------|
| MRPEngine | `MRPEngine.php` | FR-MRP-001-001 through FR-MRP-001-007 |
| DemandAggregator | `DemandAggregator.php` | FR-MRP-001-002 |
| BOMExploder | `BOMExploder.php` | FR-MRP-001-003 |
| NetRequirementsCalculator | `NetRequirementsCalculator.php` | FR-MRP-001-004 |
| LotSizingService | `LotSizingService.php` | FR-MRP-001-005 |
| PlannedOrderGenerator | `PlannedOrderGenerator.php` | FR-MRP-001-006 |
| ActionMessageGenerator | `ActionMessageGenerator.php` | FR-MRP-001-007 |

---

## 5. Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2026-09-07 | opencode | Initial creation |

---

**Related Documents**:

- BR-MRP-001: Business Requirements
- ARCH-MRP-001: Architecture Document
- FR-MRP-001-001 through FR-MRP-001-007: Functional Requirements
- UT-MRP-001-001-001: Unit Test
- UAT-MRP-001-mrp: User Acceptance Test Plan
