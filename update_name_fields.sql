-- SQL script to create/update the tech_support_requests table with separate name fields

-- Create the tech_support_requests table if it doesn't exist
CREATE TABLE IF NOT EXISTS `tech_support_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(50) NOT NULL,
  `surname` VARCHAR(50) NOT NULL,
  `middle_initial` CHAR(1),
  `agency` VARCHAR(100) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other', 'Prefer not to say') NOT NULL,
  `age` INT NOT NULL,
  `region` VARCHAR(50) NOT NULL,
  `province_id` INT NOT NULL,
  `district_id` INT,
  `municipality_id` INT,
  `support_type` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Pending', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add any missing columns if they don't exist
ALTER TABLE `tech_support_requests`
ADD COLUMN IF NOT EXISTS `firstname` VARCHAR(50) NOT NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `surname` VARCHAR(50) NOT NULL AFTER `firstname`,
ADD COLUMN IF NOT EXISTS `middle_initial` CHAR(1) AFTER `surname`,
ADD COLUMN IF NOT EXISTS `agency` VARCHAR(100) NOT NULL AFTER `middle_initial`,
ADD COLUMN IF NOT EXISTS `gender` ENUM('Male', 'Female', 'Other', 'Prefer not to say') NOT NULL AFTER `agency`,
ADD COLUMN IF NOT EXISTS `age` INT NOT NULL AFTER `gender`,
ADD COLUMN IF NOT EXISTS `region` VARCHAR(50) NOT NULL AFTER `age`,
ADD COLUMN IF NOT EXISTS `province_id` INT NOT NULL AFTER `region`,
ADD COLUMN IF NOT EXISTS `district_id` INT AFTER `province_id`,
ADD COLUMN IF NOT EXISTS `municipality_id` INT AFTER `district_id`,
ADD COLUMN IF NOT EXISTS `support_type` VARCHAR(100) NOT NULL AFTER `municipality_id`,
ADD COLUMN IF NOT EXISTS `subject` VARCHAR(200) NOT NULL AFTER `support_type`,
ADD COLUMN IF NOT EXISTS `message` TEXT NOT NULL AFTER `subject`,
ADD COLUMN IF NOT EXISTS `status` ENUM('Pending', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Pending' AFTER `message`,
ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `status`,
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
