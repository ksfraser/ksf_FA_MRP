<?php
/**
 * FA_MRP Module Hooks for FrontAccounting
 */

define('SS_MRP', 127 << 8);

class hooks_fa_mrp extends hooks {
    var $module_name = 'fa_mrp';

    function install_options($app) {
        global $path_to_root;

        switch($app->id) {
            case 'Manufacturing':
                $app->add_lapp_function(0, _("Production Schedule"),
                    $path_to_root."/modules/".$this->module_name."/schedule.php", 'SA_MRP_VIEW', MENU_ENTRY);
                $app->add_lapp_function(1, _("Material Requirements"),
                    $path_to_root."/modules/".$this->module_name."/requirements.php", 'SA_MRP_VIEW', MENU_ENTRY);
                $app->add_lapp_function(2, _("Capacity Planning"),
                    $path_to_root."/modules/".$this->module_name."/capacity.php", 'SA_MRP_MANAGE', MENU_ENTRY);
                $app->add_rapp_function(3, _("MRP Settings"),
                    $path_to_root."/modules/".$this->module_name."/settings.php", 'SA_MRP_MANAGE', MENU_MAINTENANCE);
                break;
        }
    }

    function install_access() {
        $security_sections[SS_MRP] = _("MRP Management");
        $security_areas['SA_MRP_VIEW'] = array(SS_MRP | 1, _("View MRP"));
        $security_areas['SA_MRP_MANAGE'] = array(SS_MRP | 2, _("Manage MRP"));
        return array($security_areas, $security_sections);
    }

    function activate_extension($company, $check_only=true) {
        $updates = array('sql/update.sql' => array($this->module_name));
        $ok = $this->update_databases($company, $updates, $check_only);
        if ($check_only || !$ok) {
            return $ok;
        }
        $this->ensure_mrp_schema();
        return $ok;
    }

    private function table_exists($table) {
        $sql = "SHOW TABLES LIKE " . db_escape($table);
        $res = db_query($sql, 'Failed checking table existence');
        return db_num_rows($res) > 0;
    }

    private function ensure_mrp_schema() {
        $tables = array(
            TB_PREF . "fa_mrp_production_schedule" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_mrp_production_schedule` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            TB_PREF . "fa_mrp_workcenter_capacity" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_mrp_workcenter_capacity` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `workcenter_id` VARCHAR(20) NOT NULL,
                    `date` DATE NOT NULL,
                    `capacity_hours` DECIMAL(6,2) DEFAULT 8.00,
                    `utilized_hours` DECIMAL(6,2) DEFAULT 0.00,
                    `available` TINYINT(1) DEFAULT 1,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_workcenter_date` (`workcenter_id`, `date`),
                    KEY `idx_date` (`date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            TB_PREF . "fa_mrp_material_requirements" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_mrp_material_requirements` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            TB_PREF . "fa_mrp_settings" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_mrp_settings` (
                    `setting_id` VARCHAR(50) NOT NULL,
                    `setting_value` TEXT,
                    PRIMARY KEY (`setting_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        foreach ($tables as $table_name => $sql) {
            db_query($sql, "Could not create MRP table: $table_name");
        }

        $this->set_mrp_default_settings();
    }

    private function set_mrp_default_settings() {
        $defaults = array(
            'default_capacity_per_day' => '8',
            'lead_time_days' => '3',
            'auto_link_pm' => '1',
            'gantt_day_width' => '50',
            'gantt_row_height' => '45',
        );

        foreach ($defaults as $key => $value) {
            db_query("INSERT IGNORE INTO `" . TB_PREF . "fa_mrp_settings` 
                    (setting_id, setting_value) VALUES (" . db_escape($key) . ", " . db_escape($value) . ")");
        }
    }

    function db_prevoid($trans_type, $trans_no) {
        // Handle voiding if MRP tracks financial transactions
    }
}
?>
