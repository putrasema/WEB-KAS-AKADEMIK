-- Add payment settings to the settings table
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('monthly_spp_amount', '30000'),
('spp_deadline_day', '10')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
