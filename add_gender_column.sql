-- Add gender column to tech_support_requests table
ALTER TABLE `tech_support_requests`
ADD COLUMN `gender` ENUM('male', 'female') NOT NULL AFTER `middle_initial`;
