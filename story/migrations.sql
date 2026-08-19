-- story/migrations.sql
-- Add player_items and story_objects tables for Story Mode persistence

CREATE TABLE IF NOT EXISTS player_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_key VARCHAR(100) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  metadata JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  CONSTRAINT fk_player_items_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS story_objects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  object_key VARCHAR(100) NOT NULL,
  area VARCHAR(100) DEFAULT NULL,
  state VARCHAR(32) NOT NULL DEFAULT 'broken',
  data JSON DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (user_id, object_key),
  INDEX (user_id),
  CONSTRAINT fk_story_objects_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
