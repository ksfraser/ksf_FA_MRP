# UAT-MRP-001 - MRP System UAT Plan

**Business Requirement**: BR-MRP-001
**UAT Plan ID**: UAT-MRP-001
**Status**: Proposed
**Module**: ksf_FA_MRP
**PHP Version**: 7.3+

---

## 1. UAT Overview

### 1.1 Purpose

This UAT Plan defines acceptance testing for the MRP module to verify that all functional requirements are met before production deployment.

### 1.2 Scope

| Category | Items |
|----------|-------|
| In Scope | MRP Parameters, Demand Aggregation, BOM Explosion, Net Requirements, Lot Sizing, Planned Orders, Action Messages |
| Out of Scope | Capacity Planning (CRP), Shop Floor Control, Demand Forecasting |

### 1.3 Success Criteria

| Metric | Target |
|--------|--------|
| Test Case Pass Rate | 100% |
| Critical Defects | 0 Open |
| High Priority Defects | 0 Open |
| Medium Priority Defects | < 3 Open |

---

## 2. Test Environment

### 2.1 Environment Requirements

| Component | Specification |
|-----------|---------------|
| PHP Version | 7.3+ |
| MySQL Version | 5.7+ |
| FrontAccounting | 2.4.19+ |
| Browser | Chrome 90+, Firefox 88+ |
| OS | Linux/Windows/macOS |

### 2.2 Test Data Requirements

| Data | Quantity | Source |
|------|----------|--------|
| Stock Items | 50+ | Test Data Factory |
| BOMs | 20+ multi-level | Test Data Factory |
| Sales Orders | 10 open | Test Data Factory |
| Work Orders | 5 open | Test Data Factory |
| Purchase Orders | 5 open | Test Data Factory |

### 2.3 Test Data Setup

```sql
-- Setup test stock items
INSERT INTO 0_stock_master (stock_id, item_description, units, mb_flag, quantity_on_hand, safety_stock, lead_time)
VALUES 
  ('FG-001', 'Finished Good 1', 'EA', 'M', 100, 10, 5),
  ('RAW-001', 'Raw Material 1', 'KG', 'B', 500, 50, 3),
  ('RAW-002', 'Raw Material 2', 'KG', 'B', 200, 20, 4);

-- Setup BOM
INSERT INTO 0_bom (parent, component, quantity, enabled)
VALUES 
  ('FG-001', 'RAW-001', 2.0, 1),
  ('FG-001', 'RAW-002', 0.5, 1);
```

---

## 3. Test Cases

### 3.1 MRP Parameters (FR-MRP-001-001)

| TC ID | Test Case | Steps | Expected Result | Pass Criteria |
|-------|-----------|-------|----------------|---------------|
| UAT-001-01 | View MRP Parameters | 1. Navigate to MRP > Settings<br>2. View parameter list | Default parameters displayed | All defaults shown |
| UAT-001-02 | Update Planning Horizon | 1. Edit planning_horizon_days<br>2. Set to 45<br>3. Save | Value persisted | Value = 45 after reload |
| UAT-001-03 | Update Lot Sizing Method | 1. Edit lot_sizing_method<br>2. Set to EOQ<br>3. Save | Value persisted | Value = EOQ after reload |

### 3.2 Demand Aggregation (FR-MRP-001-002)

| TC ID | Test Case | Steps | Expected Result | Pass Criteria |
|-------|-----------|-------|----------------|---------------|
| UAT-002-01 | Aggregate Sales Orders | 1. Create 3 sales orders for FG-001<br>2. Run MRP<br>3. View demands | All 3 orders aggregated | Demand qty = sum of orders |
| UAT-002-02 | Aggregate Work Orders | 1. Create 2 work orders for FG-001<br>2. Run MRP<br>3. View demands | Both WOs included | WO demands visible |
| UAT-002-03 | Demand by Due Date | 1. Create orders with different dates<br>2. Run MRP<br>3. View demands | Grouped by date | Date grouping correct |

### 3.3 BOM Explosion (FR-MRP-001-003)

| TC ID | Test Case | Steps | Expected Result | Pass Criteria |
|-------|-----------|-------|----------------|---------------|
| UAT-003-01 | Single Level Explosion | 1. Create demand for FG-001<br>2. Run MRP explosion<br>3. View component reqs | RAW-001 and RAW-002 required | Qty = demand × BOM qty |
| UAT-003-02 | Multi-Level Explosion | 1. Create 3-level BOM<br>2. Create demand for top level<br>3. Run explosion | All levels exploded | Bottom level correct |
| UAT-003-03 | Phantom Dissolution | 1. Create phantom sub-assembly<br>2. Run explosion with phantom_dissolve=1 | Phantom not in output | Components only |
| UAT-003-04 | Phantom Preservation | 1. Create phantom sub-assembly<br>2. Run explosion with phantom_dissolve=0 | Phantom in output | Phantom appears |

### 3.4 Net Requirements (FR-MRP-001-004)

| TC ID | Test Case | Steps | Expected Result | Pass Criteria |
|-------|-----------|-------|----------------|---------------|
| UAT-004-01 | Basic Net Calc | 1. Set on-hand = 50<br>2. Create demand = 100<br>3. Run MRP | Net = 50 | Net = Gross - OnHand |
| UAT-004-02 | With On-Order | 1. Set on-hand = 30<br>2. Set on-order = 40<br>3. Create demand = 100<br>4. Run MRP | Net = 30 | Net = 100 - 30 - 40 |
| UAT-004-03 | Shortage Indicator | 1. Set on-hand = 0<br>2. Create demand = 100<br>3. Run MRP | Shortage = 100 | Shortage flag = true |

### 3.5 Lot Sizing (FR-MRP-001-005)

| TC ID | Test Case | Steps | Expected Result | Pass Criteria |
|-------|-----------|-------|----------------|---------------|
| UAT-005-01 | Lot-for-Lot | 1. Set lot_sizing = LFL<br>2. Create demand = 75<br>3. Run MRP | Order = 75 | Order qty = Net qty |
| UAT-005-02 | Fixed Order Qty | 1. Set lot_sizing = FOQ<br>2. Set reorder_qty = 100<br>3. Create demand = 180<br>4. Run MRP | Orders = 100, 100 | 2 orders of 100 each |
| UAT-005-03 | EOQ Calculation | 1. Set lot_sizing = EOQ<br>2. Set ordering_cost = 50<br>3. Set holding_cost_rate = 0.2<br>4. Create demand<br>5. Run MRP | EOQ calculated | Within 5% of formula |

### 3.6 Planned Orders (FR-MRP-001-006)

| TC ID | Test Case | Steps | Expected Result | Pass Criteria |
|-------|-----------|-------|----------------|---------------|
| UAT-006-01 | Create Planned Order | 1. Run MRP<br>2. View planned orders | Orders created | Orders exist for shortages |
| UAT-006-02 | Start Date Calculation | 1. Set lead_time = 5<br>2. Set due_date = Jan 15<br>3. Run MRP | Start = Jan 10 | Start = Due - Lead |
| UAT-006-03 | Firm Order | 1. Create planned order<br>2. Click Firm<br>3. Run MRP again | Firm order kept | firm = 1, not deleted |

### 3.7 Action Messages (FR-MRP-001-007)

| TC ID | Test Case | Steps | Expected Result | Pass Criteria |
|-------|-----------|-------|----------------|---------------|
| UAT-007-01 | New Order Message | 1. Create new demand<br>2. Run MRP | ORDER message | Message type = ORDER |
| UAT-007-02 | Reschedule Message | 1. Change existing PO date<br>2. Run MRP | RESCHEDULE message | Dates differ shown |
| UAT-007-03 | Cancel Message | 1. Delete sales order<br>2. Run MRP | CANCEL message | Message type = CANCEL |
| UAT-007-04 | Message Priority | 1. Run MRP<br>2. View messages | ORDER/CANCEL first | Sorted by priority |

---

## 4. Test Execution Schedule

### 4.1 Sprint-based Execution

| Sprint | Focus Areas | Duration |
|--------|-------------|----------|
| Sprint 1 | Parameters, Basic Setup | Week 1-2 |
| Sprint 2 | Demand Aggregation, BOM Explosion | Week 3-4 |
| Sprint 3 | Net Requirements, Lot Sizing | Week 5-6 |
| Sprint 4 | Planned Orders, Action Messages | Week 7-8 |
| Sprint 5 | Integration Testing | Week 9-10 |
| Sprint 6 | UAT Sign-off | Week 11-12 |

### 4.2 Entry Criteria

| Criteria | Description |
|----------|-------------|
| EC-01 | All unit tests pass |
| EC-02 | Test environment ready |
| EC-03 | Test data loaded |
| EC-04 | Test cases reviewed |
| EC-05 | UAT team trained |

### 4.3 Exit Criteria

| Criteria | Description |
|----------|-------------|
| XC-01 | All test cases executed |
| XC-02 | All critical/high defects resolved |
| XC-03 | UAT sign-off document signed |
| XC-04 | No blocking issues |

---

## 5. Defect Management

### 5.1 Severity Levels

| Level | Definition | Examples | Target Resolution |
|-------|------------|----------|-------------------|
| Critical | System unusable, data loss | MRP crashes, data corruption | 24 hours |
| High | Major feature broken | Explosion returns wrong values | 3 days |
| Medium | Feature partially works | Lot sizing off by 1% | 1 week |
| Low | Cosmetic/enhancement | UI text incorrect | Next release |

### 5.2 Defect Template

```
Defect ID: DEF-MRP-XXX
Title: [Brief description]
Severity: [Critical/High/Medium/Low]
Description: [Detailed description]
Steps to Reproduce:
  1. [Step 1]
  2. [Step 2]
Expected Result: [What should happen]
Actual Result: [What happened]
Environment: [Test environment details]
Priority: [Must Fix/Should Fix/Could Fix]
Status: [Open/In Progress/Resolved/Closed]
```

---

## 6. Sign-Off

### 6.1 UAT Completion Checklist

| Item | Description | Sign-off Date |
|------|-------------|---------------|
| 1 | All test cases executed | |
| 2 | All critical defects resolved | |
| 3 | All high defects resolved | |
| 4 | Business owner acceptance | |
| 5 | Go-live approval | |

### 6.2 Approval Signatures

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Business Owner | | | |
| Project Manager | | | |
| QA Lead | | | |
| Technical Lead | | | |

---

## 7. Related Documents

| Document | Description |
|----------|-------------|
| BR-MRP-001 | Business Requirements |
| FR-MRP-001-001 to 007 | Functional Requirements |
| RTM-MRP-001 | Requirements Traceability Matrix |
| UT-MRP-001-001-001 | BOM Explosion Unit Test |
