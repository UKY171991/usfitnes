-- Add missing fields to tests table to match the comprehensive test entry form

ALTER TABLE `tests` 
ADD COLUMN `test_code` varchar(20) DEFAULT NULL AFTER `test_name`,
ADD COLUMN `shortcut` varchar(10) DEFAULT NULL AFTER `test_code`,
ADD COLUMN `rate` decimal(10,2) DEFAULT NULL AFTER `price`,
ADD COLUMN `report_heading` varchar(255) DEFAULT NULL AFTER `rate`,
ADD COLUMN `specimen` varchar(100) DEFAULT NULL AFTER `sample_type`,
ADD COLUMN `default_result` text DEFAULT NULL AFTER `specimen`,
ADD COLUMN `min_value` decimal(10,2) DEFAULT NULL AFTER `normal_range`,
ADD COLUMN `max_value` decimal(10,2) DEFAULT NULL AFTER `min_value`,
ADD COLUMN `individual_method` text DEFAULT NULL AFTER `method`,
ADD COLUMN `auto_suggestion` tinyint(1) DEFAULT 0 AFTER `individual_method`,
ADD COLUMN `age_gender_wise_ref` tinyint(1) DEFAULT 0 AFTER `auto_suggestion`,
ADD COLUMN `print_new_page` tinyint(1) DEFAULT 0 AFTER `age_gender_wise_ref`,
ADD COLUMN `sub_heading` tinyint(1) DEFAULT 0 AFTER `print_new_page`;

-- Update test_parameters table to add missing fields if needed
ALTER TABLE `test_parameters` 
ADD COLUMN `min_value` decimal(10,2) DEFAULT NULL AFTER `reference_range`,
ADD COLUMN `max_value` decimal(10,2) DEFAULT NULL AFTER `min_value`,
ADD COLUMN `default_result` text DEFAULT NULL AFTER `max_value`,
ADD COLUMN `specimen` varchar(100) DEFAULT NULL AFTER `default_result`,
ADD COLUMN `testcode` varchar(20) DEFAULT NULL AFTER `specimen`;
