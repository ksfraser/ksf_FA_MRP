<?php
/**
 * FA MRP Module
 *
 * Manufacturing Resource Planning with Gantt scheduling
 * Integrates with FA Manufacturing and optionally ksf_FA_ProjectManagement
 */

$module_version = '0.1.0';
$module_shortname = 'ksf_FA_MRP';
$module_to_extensions = 'ksf_FA_MRP.php';

class ksf_FA_MRP extends Module 
{
    public $module_name = 'ksf_FA_MRP';
    public $module_name_ai = "KSF Manufacturing Resource Planning";
    public $module_shortname = "ksf_FA_MRP";
    public $module_title = "KSF Manufacturing Resource Planning (MRP) with Gantt";
    public $module_version = '0.1.0';
    public $dependent_modules = array();
    
    public function __construct() 
    {
        global $db;
        
        $this->dependent_modules = array();
        
        $installed_modules = get_kd_ksf_modules();
        if (isset($installed_modules['ksf_FA_ProjectManagement'])) {
            $this->dependent_modules[] = 'ksf_FA_ProjectManagement';
        }
    }
    
    function get_permissions() 
    {
        return array(
            'MRP_VIEW' => array('shortname' => 'MRP_VIEW', 'title' => 'View MRP'),
            'MRP_MANAGE' => array('shortname' => 'MRP_MANAGE', 'title' => 'Manage MRP'),
            'MRP_VIEW_COSTS' => array('shortname' => 'MRP_VIEW_COSTS', 'title' => 'View Costs'),
            'MRP_ADMIN' => array('shortname' => 'MRP_ADMIN', 'title' => 'MRP Admin'),
        );
    }
    
    function install() 
    {
        $result = parent::install();
        
        $sql = "CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_production_schedule` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        db_query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_workcenter_capacity` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `workcenter_id` VARCHAR(20) NOT NULL,
            `date` DATE NOT NULL,
            `capacity_hours` DECIMAL(6,2) DEFAULT 8.00,
            `utilized_hours` DECIMAL(6,2) DEFAULT 0.00,
            `available` TINYINT(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_workcenter_date` (`workcenter_id`, `date`),
            KEY `idx_date` (`date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        db_query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_material_requirements` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        db_query($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_settings` (
            `setting_id` VARCHAR(50) NOT NULL,
            `setting_value` TEXT,
            PRIMARY KEY (`setting_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        db_query($sql);
        
        mrp_default_settings();
        
        add_extension_comment('KSF MRP', 'KSF Manufacturing Resource Planning');
        add_kd_ksf_module('ksf_FA_MRP', 'MRP Module with Gantt');
        
        return $result;
    }
    
    function remove($force = false) 
    {
        $result = true;
        $tables = array(
            '@TB_PREF@ksf_mrp_production_schedule',
            '@TB_PREF@ksf_mrp_workcenter_capacity',
            '@TB_PREF@ksf_mrp_material_requirements',
            '@TB_PREF@ksf_mrp_settings'
        );
        
        foreach ($tables as $table) {
            $sql = "DROP TABLE IF EXISTS $table";
            db_query($sql);
        }
        
        remove_extension_comment('KSF MRP');
        remove_kd_ksf_module('ksf_FA_MRP');
        
        return $result;
    }
    
    function activate() 
    {
        $result = true;
        return $result;
    }
    
    function deactivate() 
    {
        $result = true;
        return $result;
    }
}

function mrp_default_settings() 
{
    $defaults = array(
        'default_capacity_per_day' => '8',
        'lead_time_days' => '3',
        'auto_link_pm' => '1',
        'gantt_day_width' => '50',
        'gantt_row_height' => '45',
    );
    
    foreach ($defaults as $key => $value) {
        $sql = "INSERT IGNORE INTO `@TB_PREF@ksf_mrp_settings` 
               (setting_id, setting_value) VALUES (" . db_escape($key) . ", " . db_escape($value) . ")";
        db_query($sql);
    }
}

function is_pm_active() 
{
    $result = db_fetch_assoc(db_query(
        "SELECT * FROM `@TB_PREF@kd_ksf_modules` 
        WHERE module_id = 'ksf_FA_ProjectManagement' 
        AND active = 1 LIMIT 1"
    ));
    return !empty($result);
}