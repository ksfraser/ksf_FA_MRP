# Test Plan - ksf_FA_MRP

## Overview

This document outlines the test strategy, test types, test cases, and acceptance criteria for the Manufacturing Resource Planning (MRP) module.

---

## 1. Test Strategy

### 1.1 Test Objectives

- Verify all functional requirements are met
- Ensure data integrity and consistency
- Validate integration with FA Manufacturing
- Confirm ksf_Gantt integration works correctly
- Verify optional PM integration
- Achieve code quality standards

### 1.2 Test Levels

| Level | Description | Coverage Target |
|-------|-------------|-----------------|
| Unit Testing | Individual function/method testing | Core database functions |
| Integration Testing | Module integration with FA | All integrations |
| System Testing | End-to-end workflows | Critical paths |
| User Acceptance Testing | Business user validation | All use cases |

### 1.3 Test Types

| Type | Description |
|------|-------------|
| Functional Testing | Feature verification |
| Regression Testing | Existing functionality |
| Security Testing | Permission and access |
| Integration Testing | FA, ksf_Gantt, PM |
| UI/UX Testing | User interface |

---

## 2. Test Environment

### 2.1 Environment Requirements

- FrontAccounting 2.4.0+ installed
- FA Manufacturing module enabled
- ksf_Gantt library installed
- PHP 8.0+
- MySQL 5.7+
- Web browser (Chrome/Firefox/Edge)

### 2.2 Test Data

**Required Test Data**:
- At least 3 work orders in FA Manufacturing
- At least 2 work centers in wo_manufacture
- At least 1 item with BOM defined
- At least 1 item with stock quantity
- Optional: ksf_FA_ProjectManagement installed

---

## 3. Test Cases

### 3.1 Production Schedule Tests

#### TC-PS-001: Create Production Schedule

| Field | Value |
|-------|-------|
| Test ID | TC-PS-001 |
| Description | Create new production schedule manually |
| Preconditions | User has MRP_MANAGE permission |
| Steps | 1. Navigate to MRP Schedule > Schedule |
| | 2. Click "Add Schedule" |
| | 3. Fill required fields |
| | 4. Click Save |
| Expected Result | Schedule saved to database, appears in list |
| Pass Criteria | Schedule visible in Gantt and table |

#### TC-PS-002: View Gantt Chart

| Field | Value |
|-------|-------|
| Test ID | TC-PS-002 |
| Description | View production schedule as Gantt |
| Preconditions | Schedules exist, ksf_Gantt installed |
| Steps | 1. Navigate to MRP Schedule |
| Expected Result | Gantt chart renders with tasks |
| Pass Criteria | Chart displays with correct colors |

#### TC-PS-003: Filter Schedule by Status

| Field | Value |
|-------|-------|
| Test ID | TC-PS-003 |
| Description | Filter Gantt by production status |
| Preconditions | Schedules with different statuses |
| Steps | 1. Navigate to Gantt |
| | 2. Select status filter |
| Expected Result | Only schedules with selected status shown |
| Pass Criteria | Filtering works correctly |

#### TC-PS-004: Edit Production Schedule

| Field | Value |
|-------|-------|
| Test ID | TC-PS-004 |
| Description | Modify existing schedule |
| Preconditions | Schedule exists |
| Steps | 1. Click Schedule tab |
| | 2. Click Edit on schedule |
| | 3. Modify fields |
| | 4. Save |
| Expected Result | Schedule updated |
| Pass Criteria | Changes reflected |

#### TC-PS-005: Delete Production Schedule

| Field | Value |
|-------|-------|
| Test ID | TC-PS-005 |
| Description | Delete schedule entry |
| Preconditions | Test schedule exists |
| Steps | 1. Edit schedule |
| | 2. Click Delete |
| | 3. Confirm |
| Expected Result | Schedule removed |
| Pass Criteria | Schedule not in list |

#### TC-PS-006: Export Schedule to JSON

| Field | Value |
|-------|-------|
| Test ID | TC-PS-006 |
| Description | Export schedule data as JSON |
| Preconditions | Schedules exist |
| Steps | 1. Click "Export JSON" |
| Expected Result | JSON file downloads |
| Pass Criteria | Valid JSON returned |

---

### 3.2 Work Center Capacity Tests

#### TC-WC-001: View Work Center Capacity

| Field | Value |
|-------|-------|
| Test ID | TC-WC-001 |
| Description | View capacity for all work centers |
| Preconditions | Work centers exist in FA |
| Steps | 1. Navigate to Capacity tab |
| | 2. View 7-day view |
| Expected Result | Work centers displayed with capacity |
| Pass Criteria | All work centers shown with calculations |

#### TC-WC-002: Capacity Calculation

| Field | Value |
|-------|-------|
| Test ID | TC-WC-002 |
| Description | Verify capacity calculation |
| Preconditions | Some schedules assigned to workcenter |
| Steps | 1. View capacity |
| | 2. Check calculation (capacity - utilized) |
| Expected Result | Available hours calculated |
| Pass Criteria | Math correct |

#### TC-WC-003: Low Capacity Warning

| Field | Value |
|-------|-------|
| Test ID | TC-WC-003 |
| Description | Warning when available < 2 hours |
| Preconditions | Workcenter near capacity |
| Steps | 1. Navigate to Capacity |
| Expected Result | Warning/highlight shown |
| Pass Criteria | Warning visible |

---

### 3.3 Material Requirements Tests

#### TC-MR-001: View Material Requirements

| Field | Value |
|-------|-------|
| Test ID | TC-MR-001 |
| Description | View material requirements list |
| Preconditions | BOM exists for manufactured items |
| Steps | 1. Navigate to Materials tab |
| Expected Result | Requirements displayed |
| Pass Criteria | Table shows all columns |

#### TC-MR-002: Recalculate Materials

| Field | Value |
|-------|-------|
| Test ID | TC-MR-002 |
| Description | Recalculate all material requirements |
| Preconditions | Released work orders exist |
| Steps | 1. Navigate to Materials |
| | 2. Click "Recalculate All" |
| Expected Result | All requirements updated |
| Pass Criteria | Success notification |

#### TC-MR-003: Material Shortage Detection

| Field | Value |
|-------|-------|
| Test ID | TC-MR-003 |
| Description | Shortages highlighted correctly |
| Preconditions | Item with shortage (on_hand + on_order < required) |
| Steps | 1. Navigate to Materials |
| Expected Result | Shortage items in red |
| Pass Criteria | Shortage correctly calculated |

---

### 3.4 PM Integration Tests

#### TC-PM-001: Check PM Active

| Field | Value |
|-------|-------|
| Test ID | TC-PM-001 |
| Description | Verify PM check function |
| Preconditions | PM module installed (or not) |
| Steps | 1. Check is_pm_active() |
| Expected Result | Returns correct status |
| Pass Criteria | True when PM active, false otherwise |

#### TC-PM-002: Link Schedule to Project

| Field | Value |
|-------|-------|
| Test ID | TC-PM-002 |
| Description | Link production to PM project |
| Preconditions | PM active, project exists |
| Steps | 1. Edit schedule |
| | 2. Enter project_id |
| | 3. Save |
| Expected Result | Project link saved |
| Pass Criteria | Project shows in schedule |

#### TC-PM-003: Auto-Create PM Task

| Field | Value |
|-------|-------|
| Test ID | TC-PM-003 |
| Description | Task auto-created when WO created |
| Preconditions | PM active, auto_link_pm enabled |
| Steps | 1. Create work order in Mfg |
| Expected Result | PM task created |
| Pass Criteria | Task in fa_pm_tasks |

---

### 3.5 Settings Tests

#### TC-ST-001: View Settings

| Field | Value |
|-------|-------|
| Test ID | TC-ST-001 |
| Description | View and edit module settings |
| Preconditions | User has MRP_ADMIN |
| Steps | 1. Navigate to Settings tab |
| Expected Result | Settings form displayed |
| Pass Criteria | Current values shown |

#### TC-ST-002: Save Settings

| Field | Value |
|-------|-------|
| Test ID | TC-ST-002 |
| Description | Save settings changes |
| Preconditions | Settings page loaded |
| Steps | 1. Change setting value |
| | 2. Click Save |
| Expected Result | Settings saved |
| Pass Criteria | Values persisted |

---

### 3.6 Integration Tests

#### TC-INT-001: FA Work Orders Integration

| Field | Value |
|-------|-------|
| Test ID | TC-INT-001 |
| Description | Read work orders from FA Manufacturing |
| Preconditions | Work orders exist in FA Mfg |
| Steps | 1. View schedule |
| Expected Result | Work orders displayed |
| Pass Criteria | Data from FA read |

#### TC-INT-002: Work Centers Integration

| Field | Value |
|-------|-------|
| Test ID | TC-INT-002 |
| Description | Use work centers from FA |
| Preconditions | Work centers in wo_manufacture |
| Steps | 1. View capacity or schedule |
| Expected Result | FA work centers shown |
| Pass Criteria | Data from FA displayed |

#### TC-INT-003: BOM Integration

| Field | Value |
|-------|-------|
| Test ID | TC-INT-003 |
| Description | Read BOM from FA |
| Preconditions | Item has BOM |
| Steps | 1. Calculate materials |
| Expected Result | BOM items read |
| Pass Criteria | Material requirements correct |

---

### 3.7 Security Tests

#### TC-SC-001: Permission - View MRP

| Field | Value |
|-------|-------|
| Test ID | TC-SC-001 |
| Description | Access denied without MRP_VIEW |
| Preconditions | User lacks permission |
| Steps | 1. User attempts access |
| Expected Result | Access denied |
| Pass Criteria | Error message shown |

#### TC-SC-002: Permission - Manage MRP

| Field | Value |
|-------|-------|
| Test ID | TC-SC-002 |
| Description | Manage denied without MRP_MANAGE |
| Preconditions | User lacks permission |
| Steps | 1. User attempts create/edit |
| Expected Result | Access denied |
| Pass Criteria | Error message shown |

---

## 4. Test Execution

### 4.1 Execution Order

1. Integration tests (FA, ksf_Gantt, PM)
2. System tests (all workflows)
3. Unit tests (database functions)
4. UAT

### 4.2 Test Results Template

| Test ID | Test Name | Status | Notes |
|---------|-----------|--------|-------|
| TC-PS-001 | Create Production Schedule | PASS/FAIL | |
| TC-PS-002 | View Gantt Chart | PASS/FAIL | |

### 4.3 Defect Reporting

| Field | Description |
|-------|-------------|
| Defect ID | Unique identifier |
| Test ID | Related test case |
| Severity | Critical/Major/Minor |
| Description | Detailed description |
| Steps to Reproduce | Reproduction steps |
| Expected Result | What should happen |
| Actual Result | What actually happened |

---

## 5. Acceptance Criteria

### 5.1 Functional Acceptance

| Requirement ID | Description | Test Coverage |
|----------------|-------------|---------------|
| FR-1.1 | Create Production Schedule | TC-PS-001 |
| FR-1.2 | View Production Schedule | TC-PS-002 |
| FR-1.3 | Edit Production Schedule | TC-PS-004 |
| FR-1.4 | Delete Production Schedule | TC-PS-005 |
| FR-2.1 | View Work Center Capacity | TC-WC-001 |
| FR-3.1 | Calculate Material Requirements | TC-MR-002 |
| FR-3.2 | View Material Requirements | TC-MR-001 |
| FR-4.1 | Check PM Module Status | TC-PM-001 |
| FR-4.2 | Link Schedule to Project | TC-PM-002 |
| FR-5.1 | Module Settings | TC-ST-001 |

### 5.2 Non-Functional Acceptance

| Criteria | Target |
|----------|--------|
| Page Load Time | < 3 seconds |
| Database Queries | < 10 per page |
| Browser Compatibility | Chrome, Firefox, Edge |
| Access Control | All permissions enforced |
| Data Validation | All inputs validated |

---

## 6. Test Deliverables

| Deliverable | Description |
|-------------|-------------|
| Test Cases | This document |
| Test Data | Sample data for testing |
| Test Results | Execution results log |
| Defect Log | Issues found during testing |
| Test Summary | Final pass/fail report |

---

## 7. Test Schedule

| Phase | Duration | Activities |
|-------|----------|-----------|
| Environment Setup | 1 day | Install required modules |
| Integration Testing | 2 days | FA/ksf_Gantt/PM tests |
| System Testing | 2 days | End-to-end workflows |
| UAT | 3 days | User acceptance |
| Bug Fixing | Ongoing | Fix and retest |

---

## 8. Risk Management

### 8.1 Test Risks

| Risk | Mitigation |
|------|-------------|
| ksf_Gantt not installed | Verify installation first |
| Test data not available | Create sample data |
| PM module not active | Test both with/without PM |
| Scope creep | Track changes to requirements |
