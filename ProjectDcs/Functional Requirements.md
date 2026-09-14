# Functional Requirements - ksf_FA_MRP

## Overview

This document details the functional requirements for the Manufacturing Resource Planning (MRP) module (ksf_FA_MRP), which provides production scheduling with Gantt visualization, work center capacity tracking, and material requirements planning integrated with FA Manufacturing.

## Scope

The module handles:
- Production schedule management with Gantt visualization
- Work center capacity tracking and planning
- Material requirements calculation from Bill of Materials
- Optional integration with ksf_FA_ProjectManagement

---

## FR-1: Production Schedule Management

### FR-1.1: Create Production Schedule

**Description**: Users shall be able to create production schedule entries manually.

**Requirements**:
- FR-1.1.1: System shall accept item_code (required)
- FR-1.1.2: System shall accept quantity (required, default 1)
- FR-1.1.3: System shall accept start_date (required)
- FR-1.1.4: System shall accept end_date (optional)
- FR-1.1.5: System shall accept workorder_id reference (optional)
- FR-1.1.6: System shall accept workcenter_id assignment (optional)
- FR-1.1.7: System shall accept priority (default: Normal)
- FR-1.1.8: System shall accept status (default: Scheduled)
- FR-1.1.9: System shall accept notes (optional)
- FR-1.1.10: System shall track created_by user

**Acceptance Criteria**:
- [ ] Schedule entry created with all required fields
- [ ] Optional fields stored correctly
- [ ] Default values applied when not specified
- [ ] Created timestamp recorded

### FR-1.2: View Production Schedule

**Description**: Users shall be able to view production schedule as Gantt chart and table.

**Requirements**:
- FR-1.2.1: System shall display Gantt chart with visual timeline
- FR-1.2.2: System shall display schedule as table list
- FR-1.2.3: System shall show item_code, quantity, dates
- FR-1.2.4: System shall show status with color coding
- FR-1.2.5: System shall show priority with visual indicator
- FR-1.2.6: System shall filter by status
- FR-1.2.7: System shall show work center information
- FR-1.2.8: System shall show PM links when available

**Acceptance Criteria**:
- [ ] Gantt chart renders correctly with ksf_Gantt
- [ ] Table displays all columns
- [ ] Status colors: Completed=green, In Progress=blue, Delayed=red
- [ ] Filter by status works correctly

### FR-1.3: Edit Production Schedule

**Description**: Users shall be able to modify existing schedule entries.

**Requirements**:
- FR-1.3.1: System shall pre-populate form with existing values
- FR-1.3.2: System shall allow updating all fields
- FR-1.3.3: System shall validate required fields
- FR-1.3.4: System shall update timestamp on modification

**Acceptance Criteria**:
- [ ] Form pre-fills with current values
- [ ] Changes saved to database
- [ ] Updated timestamp recorded

### FR-1.4: Delete Production Schedule

**Description**: Users shall be able to delete schedule entries.

**Requirements**:
- FR-1.4.1: System shall require confirmation before deletion
- FR-1.4.2: System shall remove schedule entry
- FR-1.4.3: System shall update related capacity if workcenter assigned

**Acceptance Criteria**:
- [ ] Confirmation dialog appears
- [ ] Schedule entry removed from database

### FR-1.5: Production Status Management

**Description**: System shall support production status workflow.

**Requirements**:
- FR-1.5.1: System shall support status values: Scheduled, In Progress, Completed, Delayed, Cancelled
- FR-1.5.2: System shall allow status changes by authorized users
- FR-1.5.3: System shall display status with appropriate color coding
- FR-1.5.4: Status changes reflected in Gantt chart

**Acceptance Criteria**:
- [ ] Status dropdown shows all valid values
- [ ] Status changes saved
- [ ] Color coding: Completed=green (#22c55e), In Progress=blue (#3b82f6), Delayed=red (#ef4444)

---

## FR-2: Work Center Capacity Management

### FR-2.1: View Work Center Capacity

**Description**: Users shall be able to view work center capacity utilization.

**Requirements**:
- FR-2.1.1: System shall list all work centers from wo_manufacture
- FR-2.1.2: System shall show daily capacity (default 8 hours)
- FR-2.1.3: System shall show utilized hours
- FR-2.1.4: System shall calculate available hours (capacity - utilized)
- FR-2.1.5: System shall display 7-day rolling view
- FR-2.1.6: System shall highlight low availability (< 2 hours)

**Acceptance Criteria**:
- [ ] Work centers from FA displayed
- [ ] Capacity calculations correct
- [ ] Available hours shown correctly
- [ ] Warning for low availability

### FR-2.2: Update Work Center Capacity

**Description**: System shall automatically track capacity utilization.

**Requirements**:
- FR-2.2.1: System shall track utilized hours per date
- FR-2.2.2: System shall update when schedule assigned to workcenter
- FR-2.2.3: System shall maintain workcenter-date combinations
- FR-2.2.4: System shall allow manual capacity adjustment

**Acceptance Criteria**:
- [ ] Utilization updates on schedule changes
- [ ] Capacity records persisted

### FR-2.3: Capacity Alerts

**Description**: System shall alert when capacity is exceeded.

**Requirements**:
- FR-2.3.1: System shall highlight when available < 0
- FR-2.3.2: System shall show overcapacity warning
- FR-2.3.3: System shall suggest alternative dates/workcenters (future)

**Acceptance Criteria**:
- [ ] Overcapacity highlighted in red
- [ ] Warning message displayed

---

## FR-3: Material Requirements Planning

### FR-3.1: Calculate Material Requirements

**Description**: System shall calculate material requirements from BOM.

**Requirements**:
- FR-3.1.1: System shall query BOM for parent item
- FR-3.1.2: System shall multiply BOM quantity by production quantity
- FR-3.1.3: System shall get on-hand quantity from stock_master
- FR-3.1.4: System shall get on-order quantity from purch_order_details
- FR-3.1.5: System shall calculate shortage (required - on_hand - on_order)
- FR-3.1.6: System shall store requirements in material_requirements table

**Acceptance Criteria**:
- [ ] BOM queries return correct items
- [ ] Quantities calculated correctly
- [ ] Shortage calculated correctly (if negative, set to 0)

### FR-3.2: View Material Requirements

**Description**: Users shall be able to view material requirements.

**Requirements**:
- FR-3.2.1: System shall display all material requirements
- FR-3.2.2: System shall show required_date
- FR-3.2.3: System shall show quantity_required
- FR-3.2.4: System shall show quantity_on_hand
- FR-3.2.5: System shall show quantity_on_order
- FR-3.2.6: System shall show shortage amount
- FR-3.2.7: System shall sort by required_date

**Acceptance Criteria**:
- [ ] Table displays all columns
- [ ] Shortages highlighted in red
- [ ] OK status shown in green

### FR-3.3: Recalculate Materials

**Description**: Users shall be able to recalculate all material requirements.

**Requirements**:
- FR-3.3.1: System shall iterate all released work orders
- FR-3.3.2: System shall get start_date as required_date
- FR-3.3.3: System shall check if BOM exists
- FR-3.3.4: System shall recalculate and update requirements
- FR-3.3.5: System shall display notification when complete

**Acceptance Criteria**:
- [ ] All work orders processed
- [ ] Requirements updated
- [ ] Notification displayed

### FR-3.4: Shortage Alerts

**Description**: System shall highlight material shortages.

**Requirements**:
- FR-3.4.1: System shall set status to "Shortage" when shortage > 0
- FR-3.4.2: System shall set status to "OK" when shortage = 0
- FR-3.4.3: System shall show warning message when shortages exist
- FR-3.4.4: System shall differentiate shortage vs pending status

**Acceptance Criteria**:
- [ ] Shortage items marked correctly
- [ ] Warning displayed in UI

---

## FR-4: PM Integration

### FR-4.1: Check PM Module Status

**Description**: System shall check if PM module is active.

**Requirements**:
- FR-4.1.1: System shall query kd_ksf_modules for PM status
- FR-4.1.2: System shall return true only if active = 1
- FR-4.1.3: System shall use is_pm_active() function

**Acceptance Criteria**:
- [ ] Returns true when PM active
- [ ] Returns false when PM inactive

### FR-4.2: Link Schedule to Project

**Description**: Users shall be able to link production to PM projects.

**Requirements**:
- FR-4.2.1: System shall accept project_id for linking
- FR-4.2.2: System shall accept optional task_id
- FR-4.2.3: System shall update schedule record
- FR-4.2.4: System shall show project link in Gantt

**Acceptance Criteria**:
- [ ] Project link saved to database
- [ ] Project shows in Gantt as assignee

### FR-4.3: Auto-Create PM Task

**Description**: System shall auto-create PM task when work order created.

**Requirements**:
- FR-4.3.1: System shall check auto_link_pm setting
- FR-4.3.2: System shall check if PM is active
- FR-4.3.3: System shall create task in fa_pm_tasks
- FR-4.3.4: System shall use item_code as task name
- FR-4.3.5: System shall use workorder start/end dates
- FR-4.3.6: System shall link to workorder_id

**Acceptance Criteria**:
- [ ] Task created in PM when enabled
- [ ] Task linked to schedule
- [ ] Task not created when PM inactive

### FR-4.4: Link PM Option in UI

**Description**: Users shall be able to link schedules via UI.

**Requirements**:
- FR-4.4.1: System shall show "Link to PM" button
- FR-4.4.2: System shall provide project selection
- FR-4.4.3: System shall provide task selection
- FR-4.4.4: System shall save links to schedule

**Acceptance Criteria**:
- [ ] Button visible when PM active
- [ ] Links save correctly

---

## FR-5: Settings & Configuration

### FR-5.1: Module Settings

**Description**: System shall provide configuration options.

**Requirements**:
- FR-5.1.1: System shall allow setting default_capacity_per_day
- FR-5.1.2: System shall allow setting lead_time_days
- FR-5.1.3: System shall allow setting auto_link_pm (checkbox)
- FR-5.1.4: System shall allow setting gantt_day_width
- FR-5.1.5: System shall allow setting gantt_row_height
- FR-5.1.6: System shall persist settings to database

**Acceptance Criteria**:
- [ ] All settings accessible
- [ ] Settings persist across sessions
- [ ] Settings saved on form submission

### FR-5.2: Default Settings

**Description**: System shall initialize with default settings.

**Requirements**:
- FR-5.2.1: System shall set default_capacity_per_day = 8
- FR-5.2.2: System shall set lead_time_days = 3
- FR-5.2.3: System shall set auto_link_pm = 1 (enabled)
- FR-5.2.4: System shall set gantt_day_width = 50
- FR-5.2.5: System shall set gantt_row_height = 45
- FR-5.2.6: Settings initialized on install

**Acceptance Criteria**:
- [ ] Default values in settings table
- [ ] No duplicate entries (using INSERT IGNORE)

---

## FR-6: Import/Export

### FR-6.1: Export Schedule to JSON

**Description**: Users shall be able to export schedule data.

**Requirements**:
- FR-6.1.1: System shall provide Export JSON button
- FR-6.1.2: System shall return JSON format
- FR-6.1.3: System shall include all schedule fields
- FR-6.1.4: System shall set proper Content-Type header

**Acceptance Criteria**:
- [ ] Button visible in UI
- [ ] Valid JSON returned
- [ ] Download works

---

## FR-7: Integration with FA Manufacturing

### FR-7.1: Work Order Integration

**Description**: System shall integrate with FA Manufacturing work orders.

**Requirements**:
- FR-7.1.1: System shall read workorders table
- FR-7.1.2: System shall read wo_manufacture for workcenters
- FR-7.1.3: System shall read bom for materials
- FR-7.1.4: System shall read stock_master for inventory

**Acceptance Criteria**:
- [ ] FA data accessible
- [ ] Integration seamless

---

## FR-8: Dashboard & Reporting

### FR-8.1: Production Summary

**Description**: System shall show production statistics.

**Requirements**:
- FR-8.1.1: System shall show total schedules count
- FR-8.1.2: System shall show schedules by status
- FR-8.1.3: System shall show active production count

**Acceptance Criteria**:
- [ ] Counts displayed in header/table

---

## Appendix: Requirement ID Index

| ID | Description |
|----|-------------|
| FR-1.1 | Create Production Schedule |
| FR-1.2 | View Production Schedule |
| FR-1.3 | Edit Production Schedule |
| FR-1.4 | Delete Production Schedule |
| FR-1.5 | Production Status Management |
| FR-2.1 | View Work Center Capacity |
| FR-2.2 | Update Work Center Capacity |
| FR-2.3 | Capacity Alerts |
| FR-3.1 | Calculate Material Requirements |
| FR-3.2 | View Material Requirements |
| FR-3.3 | Recalculate Materials |
| FR-3.4 | Shortage Alerts |
| FR-4.1 | Check PM Module Status |
| FR-4.2 | Link Schedule to Project |
| FR-4.3 | Auto-Create PM Task |
| FR-4.4 | Link PM Option in UI |
| FR-5.1 | Module Settings |
| FR-5.2 | Default Settings |
| FR-6.1 | Export Schedule to JSON |
| FR-7.1 | Work Order Integration |
| FR-8.1 | Production Summary |
