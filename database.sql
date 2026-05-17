CREATE DATABASE IF NOT EXISTS slime_climb_galaxy
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE slime_climb_galaxy;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(24) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_saves (
  user_id INT UNSIGNED PRIMARY KEY,
  level INT UNSIGNED NOT NULL DEFAULT 1,
  xp INT UNSIGNED NOT NULL DEFAULT 0,
  coins INT UNSIGNED NOT NULL DEFAULT 100,
  gems INT UNSIGNED NOT NULL DEFAULT 5,
  rank_name VARCHAR(40) NOT NULL DEFAULT 'Rookie Comet',
  current_checkpoint VARCHAR(40) NOT NULL DEFAULT 'Start',
  skins JSON NOT NULL,
  achievements JSON NOT NULL,
  progress JSON NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_player_saves_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shop_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(80) NOT NULL,
  item_type VARCHAR(30) NOT NULL DEFAULT 'skin',
  description VARCHAR(255) NOT NULL DEFAULT '',
  price_coins INT UNSIGNED NOT NULL DEFAULT 0,
  price_gems INT UNSIGNED NOT NULL DEFAULT 0,
  tone VARCHAR(30) NOT NULL DEFAULT 'green',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_inventory (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  equipped TINYINT(1) NOT NULL DEFAULT 0,
  acquired_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_player_item (user_id, item_id),
  CONSTRAINT fk_inventory_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_inventory_item FOREIGN KEY (item_id) REFERENCES shop_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS achievements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  reward_coins INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_achievements (
  user_id INT UNSIGNED NOT NULL,
  achievement_id INT UNSIGNED NOT NULL,
  unlocked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, achievement_id),
  CONSTRAINT fk_player_ach_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_player_ach_achievement FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identity_hash CHAR(64) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts_lookup (identity_hash, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO shop_items
  (slug, name, item_type, description, price_coins, price_gems, tone)
VALUES
  ('nebula-green', 'Nebula Green', 'skin', 'Starter slime skin with a soft galaxy glow.', 0, 0, 'green'),
  ('meteor-pink', 'Meteor Pink', 'skin', 'Bright pink slime skin for comet races.', 120, 0, 'pink'),
  ('solar-gold', 'Solar Gold', 'skin', 'Golden slime skin for high-score climbers.', 180, 0, 'gold'),
  ('void-cyan', 'Void Cyan', 'skin', 'Cool cyan slime skin from the deep nebula.', 240, 0, 'cyan');

INSERT IGNORE INTO achievements
  (slug, name, description, reward_coins)
VALUES
  ('first-launch', 'First Launch', 'Start your first galaxy climb.', 25),
  ('coin-comet', 'Coin Comet', 'Collect a comet coin during a level.', 50),
  ('gravity-master', 'Gravity Master', 'Flip gravity while climbing.', 75),
  ('checkpoint-chaser', 'Checkpoint Chaser', 'Reach a checkpoint planet.', 100),
  ('galaxy-climber', 'Galaxy Climber', 'Reach the highest starter route.', 150);
