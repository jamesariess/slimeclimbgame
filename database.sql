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
  category VARCHAR(40) NOT NULL DEFAULT 'skins',
  rarity ENUM('common', 'rare', 'epic', 'legendary', 'mythic') NOT NULL DEFAULT 'common',
  description VARCHAR(255) NOT NULL DEFAULT '',
  price_coins INT UNSIGNED NOT NULL DEFAULT 0,
  price_gems INT UNSIGNED NOT NULL DEFAULT 0,
  tone VARCHAR(30) NOT NULL DEFAULT 'green',
  stat_attack INT NOT NULL DEFAULT 0,
  stat_defense INT NOT NULL DEFAULT 0,
  power_effect VARCHAR(120) NOT NULL DEFAULT '',
  stackable TINYINT(1) NOT NULL DEFAULT 0,
  visual_type ENUM('css_slime', 'image') NOT NULL DEFAULT 'css_slime',
  image_path VARCHAR(255) NULL,
  animation_style VARCHAR(40) NOT NULL DEFAULT 'float',
  sale_percent INT UNSIGNED NOT NULL DEFAULT 0,
  limited_until DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_inventory (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  equipped TINYINT(1) NOT NULL DEFAULT 0,
  equipped_slot VARCHAR(30) NULL,
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
  (slug, name, item_type, category, rarity, description, price_coins, price_gems, tone, stat_attack, stat_defense, power_effect, stackable, visual_type, image_path, animation_style, sale_percent, limited_until)
VALUES
  ('nebula-green', 'Nebula Green', 'skin', 'skins', 'common', 'Starter slime skin with a soft galaxy glow.', 0, 0, 'green', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', NULL, 'float', 0, NULL),
  ('meteor-pink', 'Meteor Pink', 'skin', 'skins', 'rare', 'Bright pink slime skin for comet races.', 120, 0, 'pink', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', NULL, 'float', 0, NULL),
  ('solar-gold', 'Solar Gold', 'skin', 'limited', 'legendary', 'Golden slime skin for high-score climbers.', 180, 0, 'gold', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', NULL, 'float', 15, NULL),
  ('void-cyan', 'Void Cyan', 'skin', 'seasonal', 'epic', 'Cool cyan slime skin from the deep nebula.', 240, 0, 'cyan', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', NULL, 'float', 0, NULL),
  ('comet-slinger', 'Comet Slinger', 'offense', 'boosts', 'rare', 'Throw charged comet blobs at alien hazards.', 160, 0, 'cyan', 8, 0, 'Unlocks ranged slime shots.', 0, 'css_slime', NULL, 'pulse', 0, NULL),
  ('star-guard-shell', 'Star Guard Shell', 'defense', 'boosts', 'epic', 'A soft orbit shield that cushions enemy hits.', 150, 0, 'gold', 0, 10, 'Reduces incoming damage.', 0, 'css_slime', NULL, 'float', 10, NULL),
  ('gravity-boots', 'Gravity Boots', 'tool', 'bundles', 'mythic', 'Stabilizes wall climbs and gravity switches.', 210, 1, 'pink', 3, 4, 'Improves parkour control.', 0, 'css_slime', NULL, 'bounce', 0, NULL),
  ('mint-burst-potion', 'Mint Burst Potion', 'potion', 'boosts', 'common', 'Temporary jump and speed boost for one climb.', 45, 0, 'green', 0, 0, 'Consumable speed boost.', 1, 'css_slime', NULL, 'pulse', 0, NULL);

INSERT IGNORE INTO achievements
  (slug, name, description, reward_coins)
VALUES
  ('first-launch', 'First Launch', 'Start your first galaxy climb.', 25),
  ('coin-comet', 'Coin Comet', 'Collect a comet coin during a level.', 50),
  ('gravity-master', 'Gravity Master', 'Flip gravity while climbing.', 75),
  ('checkpoint-chaser', 'Checkpoint Chaser', 'Reach a checkpoint planet.', 100),
  ('galaxy-climber', 'Galaxy Climber', 'Reach the highest starter route.', 150);
