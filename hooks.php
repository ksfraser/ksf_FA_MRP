<?php
/**
 * MRP Module Installation Hooks
 */

$path_to_root = "../..";

function ksf_FA_MRP_install() 
{
    global $db;
    
    $sql = "CREATE TABLE IF NOT EXISTS `" . TB_PREF . "ksf_mrp_production_schedule` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `workorder_id` VARCHAR(20) DEFAULT NULL,
        `item_code` VARCHAR(20) NOT NULL,
        `quantity` DECIMAL(15,3) NOT NULL,
        `start_date` DATE NOT NULL,
        `end_date` DATE DEFAULT NULL,
        `scheduled_hours` DECIMAL(10,2) DEFAULT 0,
        `priority` VARCHAR(20) DEFAULT 'Normal',
        `status` VARCHAR(30) DEFAULT 'Scheduled',
        `workcenter_id` INT(11) DEFAULT NULL,
        `project_id` VARCHAR(20) DEFAULT NULL,
        `task_id` VARCHAR(20) DEFAULT NULL,
        `notes` TEXT,
        `created_by` VARCHAR(100) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_workorder` (`workorder_id`),
        KEY `idx_item` (`item_code`),
        KEY `idx_dates` (`start_date`, `end_date`),
        KEY `idx_project_task` (`project_id`, `task_id`),
        KEY `idx_workcenter` (`workcenter_id`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    db_query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS `" . TB_PREF . "ksf_mrp_workcenter_capacity` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `workcenter_id` VARCHAR(20) NOT NULL,
        `date` DATE NOT NULL,
        `capacity_hours` DECIMAL(6,2) DEFAULT 8.00,
        `utilized_hours` DECIMAL(6,2) DEFAULT 0.00,
        `available` TINYINT(1) DEFAULT 1,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_workcenter_date` (`workcenter_id`, `date`),
        KEY `idx_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    db_query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS `" . TB_PREF . "ksf_mrp_material_requirements` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `item_code` VARCHAR(20) NOT NULL,
        `required_date` DATE NOT NULL,
        `quantity_required` DECIMAL(15,3) NOT NULL,
        `quantity_on_hand` DECIMAL(15,3) DEFAULT 0,
        `quantity_on_order` DECIMAL(15,3) DEFAULT 0,
        `shortage` DECIMAL(15,3) DEFAULT 0,
        `workorder_id` VARCHAR(20) DEFAULT NULL,
        `priority` VARCHAR(20) DEFAULT 'Normal',
        `status` VARCHAR(30) DEFAULT 'Pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_item` (`item_code`),
        KEY `idx_required_date` (`required_date`),
        KEY `idx_workorder` (`workorder_id`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    db_query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS `" . TB_PREF . "ksf_mrp_settings` (
        `setting_id` VARCHAR(50) NOT NULL,
        `setting_value` TEXT,
        PRIMARY KEY (`setting_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    db_query($sql);
    
    set_mrp_default_settings();
    
    add_menu_item("Manufacturing", "?page=*", "manufacturing", "ksf_MRP", "MRP Schedule", "MRP_GANTT");
    add_menu_item("Manufacturing", "?page=*", "manufacturing", "ksf_MRP", "Material Requirements", "MRP_MATERIAL");
    add_menu_item("Manufacturing", "?page=*", "manufacturing", "ksf_MRP", "Capacity Planning", "MRP_CAPACITY");
}

function ksf_FA_MRP_uninstall() 
{
    global $db;
    
    db_query("DROP TABLE IF EXISTS `" . TB_PREF . "ksf_mrp_production_schedule`");
    db_query("DROP TABLE IF EXISTS `" . TB_PREF . "ksf_mrp_workcenter_capacity`");
    db_query("DROP TABLE IF EXISTS `" . TB_PREF . "ksf_mrp_material_requirements`");
    db_query("DROP TABLE IF EXISTS `" . TB_PREF . "ksf_mrp_settings`");
}

function set_mrp_default_settings() 
{
    $defaults = [
        'default_capacity_per_day' => '8',
        'lead_time_days' => '3',
        'auto_link_pm' => '1',
        'gantt_day_width' => '50',
        'gantt_row_height' => '45',
    ];
    
    foreach ($defaults as $key => $value) {
        db_query("INSERT IGNORE INTO `" . TB_PREF . "ksf_mrp_settings` 
                (setting_id, setting_value) VALUES (" . db_escape($key) . ", " . db_escape($value) . ")");
    }
}

function ksf_FA_MRP_workorder_created($workorder_id, $data) 
{
    if (!is_pm_active()) return;
    
    $auto_link = get_mrp_setting('auto_link_pm');
    if (!$auto_link) return;
    
    $sql = "SELECT * FROM `" . TB_PREF . "workorders` WHERE id = " . db_escape($workorder_id);
    $result = db_fetch_assoc(db_query($sql));
    
    if (!$result) return;
    
    $end_date = date('Y-m-d', strtotime('+' . get_mrp_setting('lead_time_days') . ' days'));
    
    db_query("INSERT INTO `" . TB_PREF . "ksf_mrp_production_schedule` 
            (workorder_id, item_code, quantity, start_date, end_date, created_at) 
            VALUES (" . db_escape($result['id']) . ", " . db_escape($result['item']) . ", 
            " . db_escape($result['qty']) . ", " . db_escape($result['date']) . ", 
            " . db_escape($end_date) . ", NOW())");
}

function is_pm_active() 
{
    $result = db_fetch_assoc(db_query(
        "SELECT module_id FROM `" . TB_PREF . "kd_ksf_modules` 
        WHERE module_id = 'ksf_FA_ProjectManagement' 
        AND active = 1 LIMIT 1"
    ));
    return !empty($result);
}

function get_mrp_setting($key) 
{
    $result = db_fetch_assoc(db_query(
        "SELECT setting_value FROM `" . TB_PREF . "ksf_mrp_settings` 
        WHERE setting_id = " . db_escape($key)
    ));
    return $result ? $result['setting_value'] : null;
}