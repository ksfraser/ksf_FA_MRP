<?php
/**
 * MRP Gantt Chart Page
 */

$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/manufacturing/includes/db/wo_costs_db.inc");
include_once(__DIR__ . "/../includes/mrp_db.inc");

if (!check_fa_access('MRP_VIEW', false)) {
    display_error("Access denied");
    exit;
}

$section = isset($_GET['section']) ? $_GET['section'] : 'gantt';

switch ($section) {
    case 'gantt':
        mrp_gantt_page();
        break;
    case 'schedule':
        mrp_schedule_page();
        break;
    case 'materials':
        mrp_materials_page();
        break;
    case 'capacity':
        mrp_capacity_page();
        break;
    case 'settings':
        mrp_settings_page();
        break;
    default:
        mrp_gantt_page();
}

function mrp_gantt_page() 
{
    page(_("MRP Production Schedule"), false, false, "", "");
    
    mrp_navigation();
    
    echo '<h3>Production Gantt Chart</h3>';
    echo '<div class="toolbar">';
    echo '<a href="?section=gantt&export=json" class="button" target="_blank">Export JSON</a>';
    echo '<a href="?section=gantt&link_pm=1" class="button">Link to PM</a>';
    echo '</div>';
    
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $filters = [];
    if ($status) {
        $filters['status'] = $status;
    }
    
    start_table(TABLESTYLE);
    echo '<tr><th colspan="6">';
    echo '<form method="get">';
    echo '<input type="hidden" name="section" value="gantt">';
    echo 'Filter: <select name="status" onchange="this.form.submit()">';
    echo '<option value="">All</option>';
    foreach (['Scheduled', 'In Progress', 'Completed', 'Delayed'] as $s) {
        $sel = ($status === $s) ? ' selected' : '';
        echo "<option value=\"$s\"$sel>$s</option>";
    }
    echo '</select>';
    echo '</form>';
    echo '</th></tr>';
    end_table();
    
    require_once __DIR__ . '/../../ksf_Gantt/src/Ksfraser/Gantt/Entity/GanttTask.php';
    require_once __DIR__ . '/../../ksf_Gantt/src/Ksfraser/Gantt/Entity/GanttChart.php';
    require_once __DIR__ . '/../../ksf_Gantt/src/Ksfraser/Gantt/Service/GanttRenderer.php';
    
    use Ksfraser\Gantt\Entity\GanttTask;
    use Ksfraser\Gantt\Entity\GanttChart;
    use Ksfraser\Gantt\Service\GanttRenderer;
    
    $chart = new GanttChart('mrp-schedule', 'Production Schedule');
    
    $result = get_mrp_schedule(null, $filters);
    while ($row = db_fetch_assoc($result)) {
        $task = new GanttTask(
            $row['id'],
            $row['item_code'] . ' (WO: ' . $row['workorder_id'] . ')',
            new DateTime($row['start_date']),
            $row['end_date'] ? new DateTime($row['end_date']) : null
        );
        
        $task->setProgress($row['status'] === 'Completed' ? 100 : ($row['status'] === 'In Progress' ? 50 : 0));
        $task->setStatus(strtolower(str_replace(' ', '_', $row['status'])));
        $task->setPriority(strtolower($row['priority']));
        
        if ($row['project_id']) {
            $task->setAssignee('Project: ' . $row['project_id']);
        }
        
        if ($row['workcenter_id']) {
            $sql = "SELECT name FROM `" . TB_PREF . "wo_manufacture` WHERE id = " . db_escape($row['workcenter_id']);
            $wc = db_fetch_assoc(db_query($sql));
            $task->setColor($row['status'] === 'Delayed' ? '#ef4444' : ($row['workcenter_id'] ? '#6366f1' : '#3b82f6'));
        }
        
        $chart->addTask($task);
    }
    
    if (isset($_GET['export']) && $_GET['export'] === 'json') {
        header('Content-Type: application/json');
        echo $chart->toJson();
        page_end();
        return;
    }
    
    if ($chart->getTaskCount() === 0) {
        echo '<p>No production schedules found. ';
        echo 'Create a work order in Manufacturing, or <a href="?section=schedule">add manually</a>.</p>';
        page_end();
        return;
    }
    
    $ganttWidth = get_mrp_setting('gantt_day_width') ?: 50;
    $ganttHeight = get_mrp_setting('gantt_row_height') ?: 45;
    
    $renderer = new GanttRenderer([
        'dayWidth' => $ganttWidth,
        'rowHeight' => $ganttHeight,
        'headerHeight' => 60,
        'sidebarWidth' => 300,
    ]);
    
    echo $renderer->renderHtml($chart);
    
    echo '<br><h4>Production Orders</h4>';
    start_table(TABLESTYLE);
    echo '<tr>';
    echo '<th>Item</th>';
    echo '<th>WO Ref</th>';
    echo '<th>Qty</th>';
    echo '<th>Start</th>';
    echo '<th>End</th>';
    echo '<th>Status</th>';
    echo '<th>PM Link</th>';
    echo '</tr>';
    
    $result = get_mrp_schedule(null, $filters);
    while ($row = db_fetch_assoc($result)) {
        $statusClass = match($row['status']) {
            'Completed' => 'class="success"',
            'In Progress' => 'class="active"',
            'Delayed' => 'class="overdue"',
            default => '',
        };
        
        echo '<tr>';
        echo '<td>' . $row['item_code'] . '</td>';
        echo '<td>' . $row['workorder_id'] . '</td>';
        echo '<td>' . $row['quantity'] . '</td>';
        echo '<td>' . $row['start_date'] . '</td>';
        echo '<td>' . ($row['end_date'] ?: '-') . '</td>';
        echo '<td ' . $statusClass . '>' . $row['status'] . '</td>';
        echo '<td>';
        if ($row['project_id']) {
            echo '<a href="../../ksf_FA_ProjectManagement/pages/?section=tasks&project_id=' . $row['project_id'] . '">';
            echo $row['project_id'];
            if ($row['task_id']) echo ' / ' . $row['task_id'];
            echo '</a>';
        } else {
            echo '<a href="?section=schedule&action=link&id=' . $row['id'] . '">Link</a>';
        }
        echo '</td>';
        echo '</tr>';
    }
    end_table();
    
    page_end();
}

function mrp_schedule_page() 
{
    page(_("Production Schedule"), false, false, "", "");
    
    mrp_navigation();
    
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
    
    if ($action === 'add' || $action === 'edit') {
        mrp_schedule_form($action);
    } else {
        mrp_schedule_list();
    }
    
    page_end();
}

function mrp_schedule_list() 
{
    echo '<h3>Production Schedule</h3>';
    echo '<p><a href="?section=schedule&action=add" class="button">Add Schedule</a></p>';
    
    start_table(TABLESTYLE);
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Item</th>';
    echo '<th>Work Order</th>';
    echo '<th>Qty</th>';
    echo '<th>Start</th>';
    echo '<th>End</th>';
    echo '<th>Status</th>';
    echo '<th>Actions</th>';
    echo '</tr>';
    
    $result = get_mrp_schedule();
    while ($row = db_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['item_code'] . '</td>';
        echo '<td>' . ($row['workorder_id'] ?: '-') . '</td>';
        echo '<td>' . $row['quantity'] . '</td>';
        echo '<td>' . $row['start_date'] . '</td>';
        echo '<td>' . ($row['end_date'] ?: '-') . '</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '<td>';
        echo '<a href="?section=schedule&action=edit&id=' . $row['id'] . '">Edit</a> | ';
        echo '<a href="?section=schedule&action=delete&id=' . $row['id'] . '" onclick="return confirm(\'Delete?\')">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }
    end_table();
}

function mrp_schedule_form($action) 
{
    $data = ['item_code' => '', 'workorder_id' => '', 'quantity' => 1, 'start_date' => date('Y-m-d'), 
            'end_date' => '', 'priority' => 'Normal', 'status' => 'Scheduled', 'workcenter_id' => '', 'notes' => ''];
    
    if ($action === 'edit' && isset($_GET['id'])) {
        $data = get_mrp_schedule($_GET['id']) ?: $data;
    }
    
    echo '<h3>' . ($action === 'add' ? 'Add' : 'Edit') . ' Schedule</h3>';
    echo '<form method="post">';
    start_table(TABLESTYLE2);
    
    echo '<tr><td>Item Code:</td><td>';
    echo item_select('item_code', $data['item_code']);
    echo '</td></tr>';
    
    echo '<tr><td>Work Order (optional):</td><td>';
    echo '<input type="text" name="workorder_id" value="' . $data['workorder_id'] . '">';
    echo '</td></tr>';
    
    echo '<tr><td>Quantity:</td><td>';
    echo '<input type="number" name="quantity" value="' . $data['quantity'] . '" step="0.001">';
    echo '</td></tr>';
    
    echo '<tr><td>Start Date:</td><td>';
    echo '<input type="date" name="start_date" value="' . $data['start_date'] . '">';
    echo '</td></tr>';
    
    echo '<tr><td>End Date:</td><td>';
    echo '<input type="date" name="end_date" value="' . ($data['end_date'] ?: '') . '">';
    echo '</td></tr>';
    
    echo '<tr><td>Work Center:</td><td>';
    $wcs = get_mrp_workcenters();
    echo '<select name="workcenter_id"><option value="">- None -</option>';
    while ($wc = db_fetch_assoc($wcs)) {
        $sel = ($data['workcenter_id'] == $wc['id']) ? ' selected' : '';
        echo '<option value="' . $wc['id'] . '"' . $sel . '>' . $wc['name'] . '</option>';
    }
    echo '</select>';
    echo '</td></tr>';
    
    echo '<tr><td>Priority:</td><td>';
    echo '<select name="priority">';
    foreach (['Low', 'Normal', 'High', 'Critical'] as $p) {
        $sel = ($data['priority'] === $p) ? ' selected' : '';
        echo "<option value=\"$p\"$sel>$p</option>";
    }
    echo '</select>';
    echo '</td></tr>';
    
    echo '<tr><td>Status:</td><td>';
    echo '<select name="status">';
    foreach (['Scheduled', 'In Progress', 'Completed', 'Delayed', 'Cancelled'] as $s) {
        $sel = ($data['status'] === $s) ? ' selected' : '';
        echo "<option value=\"$s\"$sel>$s</option>";
    }
    echo '</select>';
    echo '</td></tr>';
    
    echo '<tr><td>Notes:</td><td>';
    echo '<textarea name="notes" rows="3">' . ($data['notes'] ?? '') . '</textarea>';
    echo '</td></tr>';
    
    echo '<tr><td colspan="2">';
    echo '<button type="submit" class="button">Save</button> ';
    echo '<a href="?section=schedule" class="button">Cancel</a>';
    echo '</td></tr>';
    
    end_table();
    echo '</form>';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $schedule_data = [
            'item_code' => $_POST['item_code'],
            'workorder_id' => $_POST['workorder_id'],
            'quantity' => $_POST['quantity'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'priority' => $_POST['priority'],
            'status' => $_POST['status'],
            'workcenter_id' => $_POST['workcenter_id'],
            'notes' => $_POST['notes'],
            'created_by' => 'current_user',
        ];
        
        if ($action === 'add') {
            add_mrp_schedule($schedule_data);
        } else {
            update_mrp_schedule($_GET['id'], $schedule_data);
        }
        
        meta_no_cache();
        redirect('?section=schedule');
    }
}

function mrp_materials_page() 
{
    page(_("Material Requirements"), false, false, "", "");
    
    mrp_navigation();
    
    echo '<h3>Material Requirements</h3>';
    
    $recalculate = isset($_GET['recalculate']);
    
    if ($recalculate) {
        $sql = "SELECT item, start_date, qty_ordered FROM `" . TB_PREF . "workorders` 
                WHERE released = 1 AND closed = 0";
        $result = db_query($sql);
        
        while ($row = db_fetch_assoc($result)) {
            $required_date = $row['start_date'] ?: date('Y-m-d');
            $item = $row['item'];
            $qty = $row['qty_ordered'];
            
            $bom_sql = "SELECT item FROM `" . TB_PREF . "bom` WHERE parent = " . db_escape($item);
            $has_bom = db_fetch_assoc(db_query($bom_sql));
            
            if ($has_bom) {
                calculate_material_requirements($item, $required_date, $qty);
            }
        }
        display_notification("Material requirements recalculated");
    }
    
    echo '<p><a href="?section=materials&recalculate=1" class="button">Recalculate All</a></p>';
    
    start_table(TABLESTYLE);
    echo '<tr>';
    echo '<th>Item</th>';
    echo '<th>Required Date</th>';
    echo '<th>Required</th>';
    echo '<th>On Hand</th>';
    echo '<th>On Order</th>';
    echo '<th>Shortage</th>';
    echo '<th>Status</th>';
    echo '</tr>';
    
    $shortages = get_material_shortages();
    $has_shortage = false;
    
    $result = db_query("SELECT * FROM `" . TB_PREF . "ksf_mrp_material_requirements` ORDER BY required_date");
    while ($row = db_fetch_assoc($result)) {
        $statusClass = $row['shortage'] > 0 ? 'class="overdue"' : 'class="success"';
        
        echo '<tr>';
        echo '<td>' . $row['item_code'] . '</td>';
        echo '<td>' . $row['required_date'] . '</td>';
        echo '<td>' . $row['quantity_required'] . '</td>';
        echo '<td>' . $row['quantity_on_hand'] . '</td>';
        echo '<td>' . $row['quantity_on_order'] . '</td>';
        echo '<td>' . $row['shortage'] . '</td>';
        echo '<td ' . $statusClass . '>' . $row['status'] . '</td>';
        echo '</tr>';
        
        if ($row['shortage'] > 0) $has_shortage = true;
    }
    end_table();
    
    if ($has_shortage) {
        echo '<p class="overdue">Warning: Material shortages detected. Consider creating purchase requests.</p>';
    }
    
    page_end();
}

function mrp_capacity_page() 
{
    page(_("Capacity Planning"), false, false, "", "");
    
    mrp_navigation();
    
    echo '<h3>Work Center Capacity</h3>';
    
    $wcs = get_mrp_workcenters();
    
    start_table(TABLESTYLE);
    echo '<tr>';
    echo '<th>Work Center</th>';
    echo '<th>Date</th>';
    echo '<th>Capacity</th>';
    echo '<th>Utilized</th>';
    echo '<th>Available</th>';
    echo '</tr>';
    
    $dates = [];
    for ($i = 0; $i < 7; $i++) {
        $dates[] = date('Y-m-d', strtotime('+' . $i . ' days'));
    }
    
    while ($wc = db_fetch_assoc($wcs)) {
        foreach ($dates as $date) {
            $cap = get_workcenter_capacity($wc['id'], $date);
            $capacity = $cap ? $cap['capacity_hours'] : get_mrp_setting('default_capacity_per_day');
            $utilized = $cap ? $cap['utilized_hours'] : 0;
            $available = $capacity - $utilized;
            $statusClass = $available < 0 ? 'class="overdue"' : ($available < 2 ? 'class="warning"' : '');
            
            echo '<tr>';
            echo '<td>' . $wc['name'] . '</td>';
            echo '<td>' . $date . '</td>';
            echo '<td>' . $capacity . ' hrs</td>';
            echo '<td>' . $utilized . ' hrs</td>';
            echo '<td ' . $statusClass . '>' . $available . ' hrs</td>';
            echo '</tr>';
        }
    }
    end_table();
    
    page_end();
}

function mrp_settings_page() 
{
    page(_("MRP Settings"), false, false, "", "");
    
    mrp_navigation();
    
    echo '<h3>MRP Settings</h3>';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $setting_key = substr($key, 8);
                set_mrp_setting($setting_key, $value);
            }
        }
        display_notification("Settings saved");
    }
    
    echo '<form method="post">';
    start_table(TABLESTYLE2);
    
    echo '<tr><td>Daily Capacity (hours):</td><td>';
    echo '<input type="number" name="setting_default_capacity_per_day" value="' . get_mrp_setting('default_capacity_per_day') . '">';
    echo '</td></tr>';
    
    echo '<tr><td>Lead Time (days):</td><td>';
    echo '<input type="number" name="setting_lead_time_days" value="' . get_mrp_setting('lead_time_days') . '">';
    echo '</td></tr>';
    
    echo '<tr><td>Auto-link to PM:</td><td>';
    echo '<input type="checkbox" name="setting_auto_link_pm" value="1" ' . (get_mrp_setting('auto_link_pm') ? 'checked' : '') . '>';
    echo ' (when PM module active)';
    echo '</td></tr>';
    
    echo '<tr><td>Gantt Day Width:</td><td>';
    echo '<input type="number" name="setting_gantt_day_width" value="' . get_mrp_setting('gantt_day_width') . '">';
    echo '</td></tr>';
    
    echo '<tr><td>Gantt Row Height:</td><td>';
    echo '<input type="number" name="setting_gantt_row_height" value="' . get_mrp_setting('gantt_row_height') . '">';
    echo '</td></tr>';
    
    echo '<tr><td colspan="2"><button type="submit" class="button">Save Settings</button></td></tr>';
    
    end_table();
    echo '</form>';
    
    echo '<h4>PM Integration</h4>';
    echo '<p>Work orders can be linked to projects and tasks in ksf_FA_ProjectManagement.</p>';
    echo '<ul>';
    echo '<li>When a work order is created, a task can be auto-created in PM</li>';
    echo '<li>Production schedules show project/task links in Gantt view</li>';
    echo '<li>Resource utilization can reference PM assignments</li>';
    echo '</ul>';
    
    page_end();
}

function mrp_navigation() 
{
    start_table(TABLESTYLE_NOBORDER);
    start_row();
    echo '<ul class="tabs">';
    echo '<li><a href="?section=gantt">' . ($section === 'gantt' ? '<b>Gantt</b>' : 'Gantt') . '</a></li>';
    echo '<li><a href="?section=schedule">' . ($section === 'schedule' ? '<b>Schedule</b>' : 'Schedule') . '</a></li>';
    echo '<li><a href="?section=materials">' . ($section === 'materials' ? '<b>Materials</b>' : 'Materials') . '</a></li>';
    echo '<li><a href="?section=capacity">' . ($section === 'capacity' ? '<b>Capacity</b>' : 'Capacity') . '</a></li>';
    echo '<li><a href="?section=settings">' . ($section === 'settings' ? '<b>Settings</b>' : 'Settings') . '</a></li>';
    echo '</ul>';
    end_row();
    end_table();
}

function item_select($name, $selected = '') 
{
    $sql = "SELECT stock_id, description FROM `" . TB_PREF . "stock_master` 
            WHERE (stock_type = 'M' OR stock_type = 'F') AND inactive = 0 ORDER BY stock_id";
    $result = db_query($sql);
    
    $html = '<select name="' . $name . '">';
    $html .= '<option value="">- Select Item -</option>';
    
    while ($row = db_fetch_assoc($result)) {
        $sel = ($row['stock_id'] === $selected) ? ' selected' : '';
        $html .= '<option value="' . $row['stock_id'] . '"' . $sel . '>';
        $html .= $row['stock_id'] . ' - ' . substr($row['description'], 0, 30);
        $html .= '</option>';
    }
    
    $html .= '</select>';
    return $html;
}

function check_fa_access($permission, $die = true) 
{
    global $db;
    
    $sql = "SELECT * FROM `" . TB_PREF . "ksf_mrp_settings` WHERE setting_id = 'module_enabled'";
    $enabled = db_fetch_assoc(db_query($sql));
    
    return true;
}