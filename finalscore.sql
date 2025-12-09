-- DTI Sports Fest Score System - Complete Database Setup
-- Database: finalscore
-- This file contains everything needed for the score system

-- Create database
CREATE DATABASE IF NOT EXISTS `finalscore` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `finalscore`;

-- Drop existing tables if they exist (to avoid conflicts)
DROP TABLE IF EXISTS `judge_scores`;
DROP TABLE IF EXISTS `scores`;
DROP TABLE IF EXISTS `games`;
DROP TABLE IF EXISTS `teams`;

-- Teams table with code column
CREATE TABLE `teams` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `code` varchar(50) NOT NULL,
    `color` varchar(7) NOT NULL DEFAULT '#2563eb',
    `members` text,
    `total_points` int(11) DEFAULT 0,
    `games_played` int(11) DEFAULT 0,
    `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_team_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Games table
CREATE TABLE `games` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `color` varchar(7) NOT NULL DEFAULT '#2563eb',
    `description` text,
    `category` varchar(20) NOT NULL DEFAULT 'scorer',
    `judge_event_type` varchar(100) DEFAULT NULL,
    `authorized_judges` json DEFAULT NULL,
    `points_system` json,
    `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_game_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scores table (for regular game scores)
CREATE TABLE `scores` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `team_id` int(11) NOT NULL,
    `game_id` int(11) NOT NULL,
    `placement` varchar(50) NOT NULL,
    `points` int(11) NOT NULL,
    `scorer` varchar(255) NOT NULL,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_scores_team` (`team_id`),
    KEY `fk_scores_game` (`game_id`),
    CONSTRAINT `fk_scores_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_scores_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `judge_scores` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `judge_name` varchar(255) NOT NULL,
    `game_id` int(11) DEFAULT NULL,
    `team_id` int(11) NOT NULL,
    `criteria1` int(11) NOT NULL DEFAULT 0,
    `criteria2` int(11) NOT NULL DEFAULT 0,
    `criteria3` int(11) NOT NULL DEFAULT 0,
    `criteria4` int(11) NOT NULL DEFAULT 0,
    `criteria5` int(11) NOT NULL DEFAULT 0,
    `total_score` int(11) NOT NULL DEFAULT 0,
    `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_judge_scores_team` (`team_id`),
    KEY `fk_judge_scores_game` (`game_id`),
    KEY `idx_judge_name` (`judge_name`),
    KEY `idx_timestamp` (`timestamp`),
    CONSTRAINT `fk_judge_scores_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_judge_scores_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX `idx_scores_team_game` ON `scores` (`team_id`, `game_id`);
CREATE INDEX `idx_scores_timestamp` ON `scores` (`timestamp`);
CREATE INDEX `idx_judge_scores_team_timestamp` ON `judge_scores` (`team_id`, `timestamp`);

-- Create views for easier data access
CREATE VIEW `team_rankings` AS
SELECT 
    t.id,
    t.name,
    t.code,
    t.color,
    t.total_points,
    t.games_played,
    ROW_NUMBER() OVER (ORDER BY t.total_points DESC) as rank_position
FROM teams t
ORDER BY t.total_points DESC;

CREATE VIEW `team_code_rankings` AS
SELECT 
    t.code,
    GROUP_CONCAT(t.name SEPARATOR ', ') as team_names,
    SUM(t.total_points) as total_points,
    SUM(t.games_played) as total_games,
    COUNT(t.id) as team_count,
    ROW_NUMBER() OVER (ORDER BY SUM(t.total_points) DESC) as rank_position
FROM teams t
GROUP BY t.code
ORDER BY SUM(t.total_points) DESC;

CREATE VIEW `judge_score_summary` AS
SELECT 
    js.judge_name,
    js.game_id,
    g.name as game_name,
    g.category as game_category,
    t.name as team_name,
    t.code as team_code,
    t.color as team_color,
    js.criteria1,
    js.criteria2,
    js.criteria3,
    js.criteria4,
    js.criteria5,
    js.total_score,
    js.percentage,
    js.timestamp
FROM judge_scores js
JOIN teams t ON js.team_id = t.id
LEFT JOIN games g ON js.game_id = g.id
ORDER BY js.timestamp DESC;

-- Create a combined scoring view
-- Note: Judge scores are calculated as average per game (total_score / number of judges)
-- Using a simpler approach that MySQL views can handle
CREATE VIEW `combined_team_scores` AS
SELECT 
    t.id,
    t.name,
    t.code,
    t.color,
    COALESCE(regular_scores.total_points, 0) as regular_points,
    COALESCE(judge_scores.total_judge_points, 0) as judge_points,
    COALESCE(regular_scores.total_points, 0) + COALESCE(judge_scores.total_judge_points, 0) as total_points,
    COALESCE(regular_scores.games_count, 0) + COALESCE(judge_scores.games_count, 0) as total_games
FROM teams t
LEFT JOIN (
    SELECT 
        team_id,
        SUM(points) as total_points,
        COUNT(*) as games_count
    FROM scores
    GROUP BY team_id
) regular_scores ON t.id = regular_scores.team_id
LEFT JOIN (
    SELECT 
        team_id,
        SUM(final_score) as total_judge_points,
        COUNT(DISTINCT game_id) as games_count
    FROM (
        SELECT 
            js.team_id,
            js.game_id,
            SUM(js.total_score) / NULLIF(COUNT(DISTINCT js.judge_name), 0) as final_score
        FROM judge_scores js
        GROUP BY js.team_id, js.game_id
    ) game_finals
    GROUP BY team_id
) judge_scores ON t.id = judge_scores.team_id
ORDER BY total_points DESC;

-- Note: Judge scores are calculated as average per game (total_score / number of judges)
UPDATE teams t
LEFT JOIN (
    SELECT 
        team_id,
        SUM(points) as total_points,
        COUNT(*) as games_count
    FROM scores
    GROUP BY team_id
) regular_scores ON t.id = regular_scores.team_id
LEFT JOIN (
    SELECT 
        team_id,
        SUM(final_score) as total_judge_points,
        COUNT(*) as judge_games
    FROM (
        SELECT 
            team_id,
            game_id,
            SUM(total_score) / NULLIF(COUNT(DISTINCT judge_name), 0) as final_score
        FROM judge_scores
        GROUP BY team_id, game_id
    ) game_finals
    GROUP BY team_id
) judge_scores ON t.id = judge_scores.team_id
SET 
    t.total_points = COALESCE(regular_scores.total_points, 0) + COALESCE(judge_scores.total_judge_points, 0),
    t.games_played = COALESCE(regular_scores.games_count, 0) + COALESCE(judge_scores.judge_games, 0);

SELECT 'Database schema created successfully. No sample data inserted.' as status;
