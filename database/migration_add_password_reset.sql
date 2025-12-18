-- Migration: Add Password Reset Fields to Users Table
-- Date: 2025-12-16
-- Description: Menambahkan kolom email, reset_token, dan reset_token_expires untuk fitur lupa password

-- Add email column if not exists
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) DEFAULT NULL AFTER `full_name`,
ADD UNIQUE KEY IF NOT EXISTS `email` (`email`);

-- Add reset token columns if not exists
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(100) DEFAULT NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token`;

-- Update existing admin user with email (optional - update with your email)
-- UPDATE `users` SET `email` = 'admin@example.com' WHERE `username` = 'admin';

COMMIT;
