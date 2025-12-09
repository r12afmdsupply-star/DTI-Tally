-- Migration script: Add category and judge_event_type columns to games table
-- Run this script to update existing database installations

USE `finalscore`;

-- Add category column if it doesn't exist
ALTER TABLE `games` 
ADD COLUMN IF NOT EXISTS `category` varchar(20) NOT NULL DEFAULT 'scorer' AFTER `description`;

-- Update existing games to have 'scorer' as default category (if needed)
UPDATE `games` SET `category` = 'scorer' WHERE `category` IS NULL OR `category` = '';

-- Add judge_event_type column if it doesn't exist
ALTER TABLE `games` 
ADD COLUMN IF NOT EXISTS `judge_event_type` varchar(100) DEFAULT NULL AFTER `category`;

SELECT 'Category and judge_event_type columns added successfully to games table!' as status;

