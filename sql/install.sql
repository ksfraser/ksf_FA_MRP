-- MRP Module SQL Schema

-- Production Schedule
CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_production_schedule` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Work Center Capacity
CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_workcenter_capacity` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `workcenter_id` VARCHAR(20) NOT NULL,
    `date` DATE NOT NULL,
    `capacity_hours` DECIMAL(6,2) DEFAULT 8.00,
    `utilized_hours` DECIMAL(6,2) DEFAULT 0.00,
    `available` TINYINT(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_workcenter_date` (`workcenter_id`, `date`),
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Material Requirements
CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_material_requirements` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings
CREATE TABLE IF NOT EXISTS `@TB_PREF@ksf_mrp_settings` (
    `setting_id` VARCHAR(50) NOT NULL,
    `setting_value` TEXT,
    PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Settings
INSERT INTO `@TB_PREF@ksf_mrp_settings` (`setting_id`, `setting_value`) VALUES
('default_capacity_per_day', '8'),
('lead_time_days', '3'),
('auto_link_pm', '1'),
('gantt_day_width', '50'),
('gantt_row_height', '45');