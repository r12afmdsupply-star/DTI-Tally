-- Migration script: Add scoring_formula column to games table
-- Run this script to enable per-game scoring formula selection

USE `finalscore`;

-- Add scoring_formula column if it doesn't exist
ALTER TABLE `games`
ADD COLUMN IF NOT EXISTS `scoring_formula` varchar(50) NOT NULL DEFAULT 'legacy' AFTER `judge_event_type`;

SELECT 'scoring_formula column added successfully to games table!' as status;


