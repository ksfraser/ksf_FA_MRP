<?php
/**
 * MRP Database Functions
 */

function add_mrp_schedule($data) 
{
    $sql = "INSERT INTO `" . TB_PREF . "ksf_mrp_production_schedule` 
            (workorder_id, item_code, quantity, start_date, end_date, scheduled_hours, 
             priority, status, workcenter_id, project_id, task_id, notes, created_by) 
            VALUES (" 
            . db_escape($data['workorder_id']) . ", " 
            . db_escape($data['item_code']) . ", " 
            . db_escape($data['quantity']) . ", " 
            . db_escape($data['start_date']) . ", " 
            . db_escape($data['end_date']) . ", " 
            . db_escape($data['scheduled_hours']) . ", " 
            . db_escape($data['priority']) . ", " 
            . db_escape($data['status']) . ", " 
            . db_escape($data['workcenter_id']) . ", " 
            . db_escape($data['project_id']) . ", " 
            . db_escape($data['task_id']) . ", " 
            . db_escape($data['notes']) . ", " 
            . db_escape($data['created_by']) . ")";
    db_query($sql);
    return db_insert_id();
}

function get_mrp_schedule($id = null, $filters = []) 
{
    $where = "1=1";
    
    if ($id) {
        $where .= " AND id = " . db_escape($id);
    }
    
    if (!empty($filters['status'])) {
        $where .= " AND status = " . db_escape($filters['status']);
    }
    
    if (!empty($filters['item_code'])) {
        $where .= " AND item_code = " . db_escape($filters['item_code']);
    }
    
    if (!empty($filters['project_id'])) {
        $where .= " AND project_id = " . db_escape($filters['project_id']);
    }
    
    if (!empty($filters['start_date'])) {
        $where .= " AND start_date >= " . db_escape($filters['start_date']);
    }
    
    if (!empty($filters['end_date'])) {
        $where .= " AND end_date <= " . db_escape($filters['end_date']);
    }
    
    $sql = "SELECT * FROM `" . TB_PREF . "ksf_mrp_production_schedule` WHERE $where ORDER BY start_date";
    
    return $id ? db_fetch_assoc(db_query($sql)) : db_query($sql);
}

function update_mrp_schedule($id, $data) 
{
    $sets = [];
    foreach ($data as $key => $value) {
        $sets[] = "$key = " . db_escape($value);
    }
    
    if (empty($sets)) return false;
    
    $sql = "UPDATE `" . TB_PREF . "ksf_mrp_production_schedule` 
           SET " . implode(', ', $sets) . " 
           WHERE id = " . db_escape($id);
    db_query($sql);
    return true;
}

function delete_mrp_schedule($id) 
{
    $sql = "DELETE FROM `" . TB_PREF . "ksf_mrp_production_schedule` WHERE id = " . db_escape($id);
    db_query($sql);
}

function link_schedule_to_project($schedule_id, $project_id, $task_id = null) 
{
    if (!is_pm_active()) return false;
    
    $sql = "UPDATE `" . TB_PREF . "ksf_mrp_production_schedule` 
           SET project_id = " . db_escape($project_id) . ", 
               task_id = " . db_escape($task_id) . " 
           WHERE id = " . db_escape($schedule_id);
    db_query($sql);
    
    if ($task_id) {
        db_query("INSERT IGNORE INTO `" . TB_PREF . "fa_pm_tasks` (task_id, name, start_date, end_date, status) 
                SELECT " . db_escape($schedule_id) . ", CONCAT('MRP: ', item_code), start_date, end_date, 'Not Started' 
                FROM `" . TB_PREF . "ksf_mrp_production_schedule` 
                WHERE id = " . db_escape($schedule_id));
    }
    
    return true;
}

function get_schedule_count($status = null) 
{
    $sql = "SELECT COUNT(*) as cnt FROM `" . TB_PREF . "ksf_mrp_production_schedule`";
    
    if ($status) {
        $sql .= " WHERE status = " . db_escape($status);
    }
    
    $result = db_fetch_assoc(db_query($sql));
    return $result['cnt'];
}

function get_mrp_workcenters() 
{
    $sql = "SELECT * FROM `" . TB_PREF . "wo_manufacture` ORDER BY id";
    return db_query($sql);
}

function get_workcenter_capacity($workcenter_id, $date) 
{
    $sql = "SELECT * FROM `" . TB_PREF . "ksf_mrp_workcenter_capacity` 
           WHERE workcenter_id = " . db_escape($workcenter_id) . " 
           AND date = " . db_escape($date);
    return db_fetch_assoc(db_query($sql));
}

function update_workcenter_capacity($workcenter_id, $date, $utilized_hours) 
{
    $existing = get_workcenter_capacity($workcenter_id, $date);
    
    if ($existing) {
        $sql = "UPDATE `" . TB_PREF . "ksf_mrp_workcenter_capacity` 
               SET utilized_hours = " . db_escape($utilized_hours) . " 
               WHERE workcenter_id = " . db_escape($workcenter_id) . " 
               AND date = " . db_escape($date);
    } else {
        $capacity = get_mrp_setting('default_capacity_per_day');
        $sql = "INSERT INTO `" . TB_PREF . "ksf_mrp_workcenter_capacity` 
                (workcenter_id, date, capacity_hours, utilized_hours) 
                VALUES (" . db_escape($workcenter_id) . ", " . db_escape($date) . ", 
                " . db_escape($capacity) . ", " . db_escape($utilized_hours) . ")";
    }
    db_query($sql);
}

function calculate_material_requirements($item_code, $required_date, $quantity) 
{
    $sql = "SELECT bom.item, bom.quantity, stock.qtyOnHand as on_hand, 
                   (SELECT SUM(purch_order_details.qty_ordered - purch_order_details.qty_received) 
                    FROM `" . TB_PREF . "purch_order_details` 
                    WHERE purch_order_details.item_code = bom.item) as on_order
            FROM `" . TB_PREF . "bom` bom
            LEFT JOIN `" . TB_PREF . "stock_master` stock ON bom.item = stock.stock_id
            WHERE bom.parent = " . db_escape($item_code);
    
    $result = db_query($sql);
    $requirements = [];
    
    while ($row = db_fetch_assoc($result)) {
        $required_qty = $row['quantity'] * $quantity;
        $on_hand = $row['on_hand'] ?: 0;
        $on_order = $row['on_order'] ?: 0;
        $shortage = max(0, $required_qty - $on_hand - $on_order);
        
        $requirements[] = [
            'item_code' => $row['item'],
            'required_date' => $required_date,
            'quantity_required' => $required_qty,
            'quantity_on_hand' => $on_hand,
            'quantity_on_order' => $on_order,
            'shortage' => $shortage,
        ];
        
        db_query("INSERT INTO `" . TB_PREF . "ksf_mrp_material_requirements` 
                (item_code, required_date, quantity_required, quantity_on_hand, quantity_on_order, shortage, status) 
                VALUES (" . db_escape($row['item']) . ", " . db_escape($required_date) . ", 
                " . db_escape($required_qty) . ", " . db_escape($on_hand) . ", " . db_escape($on_order) . ", 
                " . db_escape($shortage) . ", '" . ($shortage > 0 ? 'Shortage' : 'OK') . "')
                ON DUPLICATE KEY UPDATE 
                quantity_required = " . db_escape($required_qty) . ",
                quantity_on_hand = " . db_escape($on_hand) . ",
                quantity_on_order = " . db_escape($on_order) . ",
                shortage = " . db_escape($shortage) . ",
                status = '" . ($shortage > 0 ? 'Shortage' : 'OK') . "'");
    }
    
    return $requirements;
}

function get_material_shortages() 
{
    $sql = "SELECT * FROM `" . TB_PREF . "ksf_mrp_material_requirements` 
           WHERE shortage > 0 ORDER BY required_date";
    return db_query($sql);
}

function get_mrp_setting($key) 
{
    $result = db_fetch_assoc(db_query(
        "SELECT setting_value FROM `" . TB_PREF . "ksf_mrp_settings` 
        WHERE setting_id = " . db_escape($key)
    ));
    return $result ? $result['setting_value'] : null;
}

function set_mrp_setting($key, $value) 
{
    $sql = "INSERT INTO `" . TB_PREF . "ksf_mrp_settings` 
            (setting_id, setting_value) VALUES (" . db_escape($key) . ", " . db_escape($value) . ") 
            ON DUPLICATE KEY UPDATE setting_value = " . db_escape($value);
    db_query($sql);
}

function is_pm_active() 
{
    $result = db_fetch_assoc(db_query(
        "SELECT module_id FROM `" . TB_PREF . "kd_ksf_modules` 
        WHERE module_id = 'ksf_FA_ProjectManagement' AND active = 1 LIMIT 1"
    ));
    return !empty($result);
}