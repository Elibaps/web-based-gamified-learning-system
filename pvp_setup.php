<?php
/**
 * pvp_setup.php
 * Creates necessary PvP tables if they don't exist
 * Run this once to initialize the database schema
 */
session_start();
include 'db.php';

// Create PvP Queue table
$create_queue = "
CREATE TABLE IF NOT EXISTS `pvp_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `matched` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`),
  KEY `idx_matched` (`matched`),
  CONSTRAINT `fk_queue_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);
";

// Create PvP Matches table
$create_matches = "
CREATE TABLE IF NOT EXISTS `pvp_matches` (
  `match_id` int(11) NOT NULL AUTO_INCREMENT,
  `player1_id` int(11) NOT NULL,
  `player2_id` int(11) NOT NULL,
  `player1_username` varchar(50) NOT NULL,
  `player2_username` varchar(50) NOT NULL,
  `player1_score` int(11) NOT NULL DEFAULT 0,
  `player2_score` int(11) NOT NULL DEFAULT 0,
  `winner_id` int(11),
  `status` enum('waiting', 'active', 'completed') NOT NULL DEFAULT 'waiting',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime,
  `match_token` varchar(64),
  PRIMARY KEY (`match_id`),
  KEY `idx_players` (`player1_id`, `player2_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_match_p1`
    FOREIGN KEY (`player1_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_p2`
    FOREIGN KEY (`player2_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);
";

// Create Match Questions table (track which questions are used in each match)
$create_match_questions = "
CREATE TABLE IF NOT EXISTS `match_questions` (
  `match_q_id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `answer` varchar(100) NOT NULL,
  `question_order` int(11) NOT NULL,
  PRIMARY KEY (`match_q_id`),
  KEY `idx_match` (`match_id`),
  CONSTRAINT `fk_match_q_match`
    FOREIGN KEY (`match_id`) REFERENCES `pvp_matches` (`match_id`) ON DELETE CASCADE
);
";

// Create Player Answers table (track answers for each match)
$create_player_answers = "
CREATE TABLE IF NOT EXISTS `player_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `answered_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`answer_id`),
  UNIQUE KEY `unique_answer` (`match_id`, `player_id`, `question_id`),
  KEY `idx_match` (`match_id`),
  CONSTRAINT `fk_answer_match`
    FOREIGN KEY (`match_id`) REFERENCES `pvp_matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_answer_player`
    FOREIGN KEY (`player_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);
";

try {
    mysqli_query($conn, $create_queue) or throw new Exception(mysqli_error($conn));
    mysqli_query($conn, $create_matches) or throw new Exception(mysqli_error($conn));
    mysqli_query($conn, $create_match_questions) or throw new Exception(mysqli_error($conn));
    mysqli_query($conn, $create_player_answers) or throw new Exception(mysqli_error($conn));
    
    echo "✓ PvP tables created successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
