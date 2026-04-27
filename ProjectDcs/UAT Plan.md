# UAT Plan - ksf_FA_MRP

## Overview

This document defines the User Acceptance Test (UAT) cases for the Manufacturing Resource Planning (MRP) module. UAT validates that the system meets business requirements and is ready for production deployment.

---

## 1. UAT Objectives

### 1.1 Goals

- Validate production scheduling workflows function correctly
- Confirm Gantt visualization works properly
- Ensure work center capacity tracking is accurate
- Verify material requirements calculations
- Validate PM integration works correctly
- Obtain sign-off for production deployment

### 1.2 Success Criteria

- All critical test cases pass
- No high-severity defects open
- User acceptance obtained
- Sign-off documented

---

## 2. UAT Scope

### 2.1 In Scope

- Gantt production schedule management
- Work center capacity tracking
- Material requirements planning
- Settings and configuration
- FA Manufacturing integration
- ksf_Gantt integration
-PM integration (optional)
- Security and permissions

### 2.2 Out of Scope

- Performance stress testing
- Security penetration testing
- Browser compatibility (covered in QA)
- Offline/mobile access

---

## 3. UAT User Roles

| Role | Description | Tests Executed |
|------|-------------|----------------|
| Production Manager | Manages schedules and capacity | PS-001 through PS-006, WC-001 |
| Inventory Manager | Reviews materials and shortages | MR-001 through MR-003 |
| Administrator | System configuration | ST-001, ST-002 |

---

## 4. UAT Test Cases

### 4.1 Production Schedule (PS)

#### UAT-PS-001: Create Production Schedule

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PS-001 |
| Scenario | Create production schedule manually |
| Preconditions | User has MRP_MANAGE permission |
| Test Steps | 1. Login as Production Manager |
| | 2. Navigate to Manufacturing > MRP Schedule |
| | 3. Click Schedule tab |
| | 4. Click Add Schedule |
| | 5. Enter: Item = "ITEM-001", Qty = 100, Start = today |
| | 6. Select Priority = "High" |
| | 7. Click Save |
| Expected Result | Success message, schedule appears in list |
| Acceptance Criteria | [ ] Schedule saved to database |
| | [ ] Schedule visible in Gantt |
| | [ ] Schedule visible in table |
| Result | PASS/FAIL |
| Notes | |

#### UAT-PS-002: View Gantt Chart

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PS-002 |
| Scenario | View production schedule as Gantt |
| Preconditions | Schedule exists from UAT-PS-001 |
| Test Steps | 1. Navigate to MRP Schedule |
| | 2. View Gantt chart |
| Expected Result | Gantt renders with task bars |
| Acceptance Criteria | [ ] Gantt displays correctly |
| | [ ] Task bars show start/end dates |
| | [ ] Status colors display |
| Result | PASS/FAIL |
| Notes | |

#### UAT-PS-003: Filter by Status

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PS-003 |
| Scenario | Filter Gantt by production status |
| Preconditions | Schedules with different statuses |
| Test Steps | 1. Navigate to Gantt |
| | 2. Select "Completed" from filter |
| Expected Result | Only completed schedules shown |
| Acceptance Criteria | [ ] Filtering works |
| | [ ] Correct schedules shown |
| Result | PASS/FAIL |
| Notes | |

#### UAT-PS-004: Edit Schedule

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PS-004 |
| Scenario | Modify existing schedule |
| Preconditions | Schedule exists |
| Test Steps | 1. Go to Schedule tab |
| | 2. Click Edit on test schedule |
| | 3. Change priority to "Critical" |
| | 4. Change status to "In Progress" |
| | 5. Save |
| Expected Result | Changes saved successfully |
| Acceptance Criteria | [ ] Priority updated |
| | [ ] Status updated |
| | [ ] Updated timestamp changed |
| Result | PASS/FAIL |
| Notes | |

#### UAT-PS-005: Delete Schedule

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PS-005 |
| Scenario | Delete a schedule |
| Preconditions | Test schedule exists |
| Test Steps | 1. Edit schedule |
| | 2. Click Delete |
| | 3. Confirm deletion |
| Expected Result | Schedule removed |
| Acceptance Criteria | [ ] Schedule not in list |
| | [ ] Schedule not in Gantt |
| Result | PASS/FAIL |
| Notes | |

#### UAT-PS-006: Export to JSON

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PS-006 |
| Scenario | Export schedule to JSON |
| Preconditions | Schedules exist |
| Test Steps | 1. Click "Export JSON" |
| | 2. Save file |
| Expected Result | JSON file downloads |
| Acceptance Criteria | [ ] Valid JSON format |
| | [ ] Data matches schedule |
| Result | PASS/FAIL |
| Notes | |

---

### 4.2 Work Center Capacity (WC)

#### UAT-WC-001: View Capacity

| Field | Value |
|-------|-------|
| Test Case ID | UAT-WC-001 |
| Scenario | View work center capacity |
| Preconditions | Work centers exist in FA |
| Test Steps | 1. Navigate to Capacity tab |
| | 2. View capacity table |
| Expected Result | All work centers shown |
| Acceptance Criteria | [ ] All work centers displayed |
| | [ ] 7-day view shows |
| | [ ] Capacity calculations visible |
| Result | PASS/FAIL |
| Notes | |

#### UAT-WC-002: Capacity Calculations

| Field | Value |
|-------|-------|
| Test Case ID | UAT-WC-002 |
| Scenario | Verify capacity math |
| Preconditions | Schedules assigned to workcenter |
| Test Steps | 1. Navigate to Capacity |
| | 2. Check calculations |
| Expected Result | Available = Capacity - Utilized |
| Acceptance Criteria | [ ] Math correct |
| | [ ] Available hours shown |
| Result | PASS/FAIL |
| Notes | |

#### UAT-WC-003: Low Capacity Warning

| Field | Value |
|-------|-------|
| Test Case ID | UAT-WC-003 |
| Scenario | Warning when capacity low |
| Preconditions | Workcenter with low availability |
| Test Steps | 1. View Capacity |
| Expected Result | Alert for low capacity |
| Acceptance Criteria | [ ] Warning visible |
| | [ ] < 2 hours highlighted |
| Result | PASS/FAIL |
| Notes | |

---

### 4.3 Material Requirements (MR)

#### UAT-MR-001: View Materials

| Field | Value |
|-------|-------|
| Test Case ID | UAT-MR-001 |
| Scenario | View material requirements |
| Preconditions | BOM exists for item |
| Test Steps | 1. Navigate to Materials tab |
| | 2. View requirements table |
| Expected Result | Requirements displayed |
| Acceptance Criteria | [ ] Table shows all columns |
| | [ ] Sorted by required_date |
| Result | PASS/FAIL |
| Notes | |

#### UAT-MR-002: Recalculate Materials

| Field | Value |
|-------|-------|
| Test Case ID | UAT-MR-002 |
| Scenario | Recalculate all requirements |
| Preconditions | Work orders exist |
| Test Steps | 1. Navigate to Materials |
| | 2. Click Recalculate All |
| Expected Result | All requirements updated |
| Acceptance Criteria | [ ] Success message |
| | [ ] Data refreshed |
| Result | PASS/FAIL |
| Notes | |

#### UAT-MR-003: Shortage Alert

| Field | Value |
|-------|-------|
| Test Case ID | UAT-MR-003 |
| Scenario | Verify shortage highlighting |
| Preconditions | Item with shortage |
| Test Steps | 1. Navigate to Materials |
| | 2. Check for shortage items |
| Expected Result | Shortages in red |
| Acceptance Criteria | [ ] Shortage > 0 in red |
| | [ ] Status shows "Shortage" |
| Result | PASS/FAIL |
| Notes | |

---

### 4.4 PM Integration (PMI)

#### UAT-PMI-001: Check PM Status

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PMI-001 |
| Scenario | Check PM module status |
| Preconditions | PM module (optional) installed |
| Test Steps | 1. Use is_pm_active() function |
| Expected Result | Correct status returned |
| Acceptance Criteria | [ ] Returns correct value |
| | [ ] True when PM active |
| Result | PASS/FAIL |
| Notes | Skip if PM not installed |

#### UAT-PMI-002: Link to Project

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PMI-002 |
| Scenario | Link schedule to PM project |
| Preconditions | PM active, project exists |
| Test Steps | 1. Edit schedule |
| | 2. Enter project_id |
| | 3. Save |
| Expected Result | Project link saved |
| Acceptance Criteria | [ ] Project in schedule |
| | [ ] Project shows in Gantt |
| Result | PASS/FAIL |
| Notes | Skip if PM not installed |

---

### 4.5 Settings (ST)

#### UAT-ST-001: View Settings

| Field | Value |
|-------|-------|
| Test Case ID | UAT-ST-001 |
| Scenario | View module settings |
| Preconditions | User has MRP_ADMIN |
| Test Steps | 1. Navigate to Settings tab |
| Expected Result | Settings form populated |
| Acceptance Criteria | [ ] All settings shown |
| | [ ] Current values displayed |
| Result | PASS/FAIL |
| Notes | |

#### UAT-ST-002: Save Settings

| Field | Value |
|-------|-------|
| Test Case ID | UAT-ST-002 |
| Scenario | Save settings changes |
| Preconditions | Settings loaded |
| Test Steps | 1. Change lead_time_days to 5 |
| | 2. Click Save |
| Expected Result | Settings saved |
| Acceptance Criteria | [ ] Success notification |
| | [ ] Value persists |
| Result | PASS/FAIL |
| Notes | |

---

### 4.6 Integration (INT)

#### UAT-INT-001: FA Work Orders

| Field | Value |
|-------|-------|
| Test Case ID | UAT-INT-001 |
| Scenario | Import work orders from FA |
| Preconditions | Work orders in FA |
| Test Steps | 1. View schedule |
| Expected Result | Work orders visible |
| Acceptance Criteria | [ ] From workorders table |
| | [ ] Date/item data correct |
| Result | PASS/FAIL |
| Notes | Requires FA Manufacturing |

#### UAT-INT-002: Work Centers

| Field | Value |
|-------|-------|
| Test Case ID | UAT-INT-002 |
| Scenario | Use FA work centers |
| Preconditions | Work centers in FA |
| Test Steps | 1. View Capacity |
| Expected Result | FA work centers |
| Acceptance Criteria | [ ] From wo_manufacture |
| | [ ] Names correct |
| Result | PASS/FAIL |
| Notes | Requires FA Manufacturing |

#### UAT-INT-003: ksf_Gantt Integration

| Field | Value |
|-------|-------|
| Test Case ID | UAT-INT-003 |
| Scenario | Gantt rendering |
| Preconditions | ksf_Gantt installed |
| Test Steps | 1. View Gantt |
| Expected Result | Gantt displays |
| Acceptance Criteria | [ ] ksf_Gantt renders |
| | [ ] No errors |
| Result | PASS/FAIL |
| Notes | Requires ksf_Gantt library |

---

### 4.7 Security (SC)

#### UAT-SC-001: View Permission

| Field | Value |
|-------|-------|
| Test Case ID | UAT-SC-001 |
| Scenario | Access denied without permission |
| Preconditions | User without MRP_VIEW |
| Test Steps | 1. User accesses MRP |
| Expected Result | Access denied message |
| Acceptance Criteria | [ ] Error shown |
| | [ ] No data visible |
| Result | PASS/FAIL |
| Notes | |

#### UAT-SC-002: Manage Permission

| Field | Value |
|-------|-------|
| Test Case ID | UAT-SC-002 |
| Scenario | Manage denied without permission |
| Preconditions | User without MRP_MANAGE |
| Test Steps | 1. User attempts create |
| Expected Result | Access denied |
| Acceptance Criteria | [ ] Error shown |
| | [ ] Create blocked |
| Result | PASS/FAIL |
| Notes | |

---

## 5. UAT Execution

### 5.1 Execution Checklist

- [ ] All test cases reviewed
- [ ] Test environment ready
- [ ] Test data loaded
- [ ] Test users configured
- [ ] Test cases executed
- [ ] Results documented
- [ ] Defects logged

### 5.2 Sign-off

| Role | Name | Date | Signature |
|------|------|------|----------|
| Production Manager | | | |
| Inventory Manager | | | |
| QA Lead | | | |

---

## 6. Test Results Summary

### 6.1 Results Summary

| Category | Total | Passed | Failed | Pass Rate |
|----------|-------|--------|--------|----------|
| Production Schedule | 6 | | | |
| Work Center Capacity | 3 | | | |
| Material Requirements | 3 | | | |
| PM Integration | 2 | | | |
| Settings | 2 | | | |
| Integration | 3 | | | |
| Security | 2 | | | |
| **TOTAL** | **21** | | | |

### 6.2 Defects Found

| Defect ID | Test Case | Severity | Description | Status |
|-----------|----------|----------|-------------|--------|
| | | | | |

---

## 7. UAT Completion

### 7.1 Completion Criteria

- [ ] All critical test cases pass
- [ ] No high-severity defects open
- [ ] All test data cleaned up
- [ ] Sign-off obtained

### 7.2 Final Sign-off

This module is approved for production deployment.

| Role | Name | Date | Signature |
|------|------|------|----------|
| Business Owner | | | |
| Production Manager | | | |
| QA Lead | | |
