-- SQL script to alter the tech_support_requests table to remove email and phone columns

-- Remove email column if it exists
ALTER TABLE `tech_support_requests` 
DROP COLUMN IF EXISTS `email`;

-- Remove phone column if it exists
ALTER TABLE `tech_support_requests` 
DROP COLUMN IF EXISTS `phone`;
