<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'slime_climb_galaxy';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, pdo_options());
    } catch (PDOException $error) {
        if ($error->getCode() !== 1049 && !str_contains($error->getMessage(), 'Unknown database')) {
            throw $error;
        }
        $serverDsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
        $server = new PDO($serverDsn, DB_USER, DB_PASS, pdo_options());
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo = new PDO($dsn, DB_USER, DB_PASS, pdo_options());
    }
    migrate($pdo);
    return $pdo;
}

function pdo_options(): array
{
    return [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
}

function migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(24) NOT NULL UNIQUE,
            email VARCHAR(120) NOT NULL UNIQUE,
            role ENUM("user", "admin") NOT NULL DEFAULT "user",
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
    ensure_column($pdo, 'users', 'role', 'ALTER TABLE users ADD role ENUM("user", "admin") NOT NULL DEFAULT "user" AFTER email');
    $pdo->exec('UPDATE users SET role = "admin" WHERE username = "admin"');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS player_saves (
            user_id INT UNSIGNED PRIMARY KEY,
            level INT UNSIGNED NOT NULL DEFAULT 1,
            xp INT UNSIGNED NOT NULL DEFAULT 0,
            coins INT UNSIGNED NOT NULL DEFAULT 100,
            gems INT UNSIGNED NOT NULL DEFAULT 5,
            rank_name VARCHAR(40) NOT NULL DEFAULT "Rookie Comet",
            current_checkpoint VARCHAR(40) NOT NULL DEFAULT "Start",
            skins JSON NOT NULL,
            achievements JSON NOT NULL,
            progress JSON NOT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_player_saves_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS shop_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(60) NOT NULL UNIQUE,
            name VARCHAR(80) NOT NULL,
            item_type VARCHAR(30) NOT NULL DEFAULT "skin",
            description VARCHAR(255) NOT NULL DEFAULT "",
            price_coins INT UNSIGNED NOT NULL DEFAULT 0,
            price_gems INT UNSIGNED NOT NULL DEFAULT 0,
            tone VARCHAR(30) NOT NULL DEFAULT "green",
            stat_attack INT NOT NULL DEFAULT 0,
            stat_defense INT NOT NULL DEFAULT 0,
            power_effect VARCHAR(120) NOT NULL DEFAULT "",
            stackable TINYINT(1) NOT NULL DEFAULT 0,
            visual_type ENUM("css_slime", "image") NOT NULL DEFAULT "css_slime",
            image_path VARCHAR(255) NULL,
            animation_style VARCHAR(40) NOT NULL DEFAULT "float",
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
    ensure_column($pdo, 'shop_items', 'visual_type', 'ALTER TABLE shop_items ADD visual_type ENUM("css_slime", "image") NOT NULL DEFAULT "css_slime" AFTER tone');
    ensure_column($pdo, 'shop_items', 'stat_attack', 'ALTER TABLE shop_items ADD stat_attack INT NOT NULL DEFAULT 0 AFTER tone');
    ensure_column($pdo, 'shop_items', 'stat_defense', 'ALTER TABLE shop_items ADD stat_defense INT NOT NULL DEFAULT 0 AFTER stat_attack');
    ensure_column($pdo, 'shop_items', 'power_effect', 'ALTER TABLE shop_items ADD power_effect VARCHAR(120) NOT NULL DEFAULT "" AFTER stat_defense');
    ensure_column($pdo, 'shop_items', 'stackable', 'ALTER TABLE shop_items ADD stackable TINYINT(1) NOT NULL DEFAULT 0 AFTER power_effect');
    ensure_column($pdo, 'shop_items', 'image_path', 'ALTER TABLE shop_items ADD image_path VARCHAR(255) NULL AFTER visual_type');
    ensure_column($pdo, 'shop_items', 'animation_style', 'ALTER TABLE shop_items ADD animation_style VARCHAR(40) NOT NULL DEFAULT "float" AFTER image_path');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS player_inventory (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
    ensure_column($pdo, 'player_inventory', 'quantity', 'ALTER TABLE player_inventory ADD quantity INT UNSIGNED NOT NULL DEFAULT 1 AFTER item_id');
    ensure_column($pdo, 'player_inventory', 'equipped_slot', 'ALTER TABLE player_inventory ADD equipped_slot VARCHAR(30) NULL AFTER equipped');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS achievements (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(60) NOT NULL UNIQUE,
            name VARCHAR(80) NOT NULL,
            description VARCHAR(255) NOT NULL DEFAULT "",
            reward_coins INT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS player_achievements (
            user_id INT UNSIGNED NOT NULL,
            achievement_id INT UNSIGNED NOT NULL,
            unlocked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, achievement_id),
            CONSTRAINT fk_player_ach_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_player_ach_achievement FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS login_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            identity_hash CHAR(64) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_login_attempts_lookup (identity_hash, ip_address, attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    seed_game_content($pdo);
}

function seed_game_content(PDO $pdo): void
{
    $items = [
        ['nebula-green', 'Nebula Green', 'skin', 'Starter slime skin with a soft galaxy glow.', 0, 0, 'green', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', null, 'float'],
        ['meteor-pink', 'Meteor Pink', 'skin', 'Bright pink slime skin for comet races.', 120, 0, 'pink', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', null, 'float'],
        ['solar-gold', 'Solar Gold', 'skin', 'Golden slime skin for high-score climbers.', 180, 0, 'gold', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', null, 'float'],
        ['void-cyan', 'Void Cyan', 'skin', 'Cool cyan slime skin from the deep nebula.', 240, 0, 'cyan', 0, 0, 'Cosmetic slime body.', 0, 'css_slime', null, 'float'],
        ['comet-slinger', 'Comet Slinger', 'offense', 'Throw charged comet blobs at alien hazards.', 160, 0, 'cyan', 8, 0, 'Unlocks ranged slime shots.', 0, 'css_slime', null, 'pulse'],
        ['star-guard-shell', 'Star Guard Shell', 'defense', 'A soft orbit shield that cushions enemy hits.', 150, 0, 'gold', 0, 10, 'Reduces incoming damage.', 0, 'css_slime', null, 'float'],
        ['gravity-boots', 'Gravity Boots', 'tool', 'Stabilizes wall climbs and gravity switches.', 210, 1, 'pink', 3, 4, 'Improves parkour control.', 0, 'css_slime', null, 'bounce'],
        ['mint-burst-potion', 'Mint Burst Potion', 'potion', 'Temporary jump and speed boost for one climb.', 45, 0, 'green', 0, 0, 'Consumable speed boost.', 1, 'css_slime', null, 'pulse'],
    ];
    $itemStmt = $pdo->prepare(
        'INSERT IGNORE INTO shop_items
            (slug, name, item_type, description, price_coins, price_gems, tone, stat_attack, stat_defense, power_effect, stackable, visual_type, image_path, animation_style)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($items as $item) {
        $itemStmt->execute($item);
    }

    $achievements = [
        ['first-launch', 'First Launch', 'Start your first galaxy climb.', 25],
        ['coin-comet', 'Coin Comet', 'Collect a comet coin during a level.', 50],
        ['gravity-master', 'Gravity Master', 'Flip gravity while climbing.', 75],
        ['checkpoint-chaser', 'Checkpoint Chaser', 'Reach a checkpoint planet.', 100],
        ['galaxy-climber', 'Galaxy Climber', 'Reach the highest starter route.', 150],
    ];
    $achievementStmt = $pdo->prepare(
        'INSERT IGNORE INTO achievements
            (slug, name, description, reward_coins)
         VALUES (?, ?, ?, ?)'
    );
    foreach ($achievements as $achievement) {
        $achievementStmt->execute($achievement);
    }
}

function start_app_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function csrf_token(): string
{
    start_app_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(?string $token): void
{
    start_app_session();
    if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(419);
        exit('Security token expired. Go back and try again.');
    }
}

function verify_csrf_from_post(): void
{
    verify_csrf_token((string) ($_POST['csrf_token'] ?? ''));
}

function verify_csrf_from_request(?array $jsonInput = null): void
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $token = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? ($jsonInput['csrf_token'] ?? '');
    verify_csrf_token((string) $token);
}

function public_user(array $user): array
{
    return [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user',
    ];
}

function current_user(): ?array
{
    start_app_session();
    if (empty($_SESSION['user']['id'])) {
        return null;
    }

    try {
        $stmt = db()->prepare('SELECT id, username, email, role FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int) $_SESSION['user']['id']]);
        $user = $stmt->fetch();
        if (!$user) {
            $_SESSION = [];
            return null;
        }
        $_SESSION['user'] = public_user($user);
    } catch (Throwable $error) {
        return $_SESSION['user'];
    }

    return $_SESSION['user'];
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function is_admin(?array $user = null): bool
{
    $user ??= current_user();
    return ($user['role'] ?? 'user') === 'admin';
}

function require_admin(): array
{
    $user = require_login();
    if (!is_admin($user)) {
        http_response_code(403);
        require __DIR__ . '/../partials/head.php';
        echo '<main class="content-wrap"><section class="simple-panel"><p class="kicker">Admin only</p><h1>Access denied</h1><p class="muted">Only admin accounts can view this page.</p><a class="button primary" href="menu.php">Back to Menu</a></section></main>';
        require __DIR__ . '/../partials/foot.php';
        exit;
    }
    return $user;
}

function admin_exists(PDO $pdo): bool
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"');
    return (int) $stmt->fetchColumn() > 0;
}

function get_all_users(): array
{
    return db()->query(
        'SELECT u.id, u.username, u.email, u.role, u.created_at,
                COALESCE(ps.level, 1) AS level,
                COALESCE(ps.coins, 0) AS coins,
                COALESCE(ps.gems, 0) AS gems
         FROM users u
         LEFT JOIN player_saves ps ON ps.user_id = u.id
         ORDER BY u.created_at DESC'
    )->fetchAll();
}

function update_user_role(int $targetUserId, string $role, int $actingUserId): void
{
    if (!in_array($role, ['user', 'admin'], true)) {
        throw new InvalidArgumentException('Invalid role.');
    }
    if ($targetUserId === $actingUserId && $role !== 'admin') {
        throw new InvalidArgumentException('You cannot remove your own admin access.');
    }
    $stmt = db()->prepare('UPDATE users SET role = :role WHERE id = :id');
    $stmt->execute([
        ':role' => $role,
        ':id' => $targetUserId,
    ]);
}

function ensure_column(PDO $pdo, string $table, string $column, string $alterSql): void
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name'
    );
    $stmt->execute([
        ':table_name' => $table,
        ':column_name' => $column,
    ]);
    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec($alterSql);
    }
}

function redirect_if_logged_in(): void
{
    if (current_user()) {
        header('Location: menu.php');
        exit;
    }
}

function create_default_save(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO player_saves
            (user_id, skins, achievements, progress)
         VALUES
            (:user_id, :skins, :achievements, :progress)'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':skins' => json_encode(['Nebula Green']),
        ':achievements' => json_encode([]),
        ':progress' => json_encode(new stdClass()),
    ]);
    ensure_starter_inventory($pdo, $userId);
}

function login_identity_hash(string $identity): string
{
    return hash('sha256', strtolower(trim($identity)));
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'), 0, 45);
}

function too_many_login_attempts(string $identity): bool
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE identity_hash = :identity_hash
           AND ip_address = :ip_address
           AND attempted_at > (NOW() - INTERVAL 15 MINUTE)'
    );
    $stmt->execute([
        ':identity_hash' => login_identity_hash($identity),
        ':ip_address' => client_ip(),
    ]);
    return (int) $stmt->fetchColumn() >= 8;
}

function record_login_failure(string $identity): void
{
    $stmt = db()->prepare(
        'INSERT INTO login_attempts (identity_hash, ip_address)
         VALUES (:identity_hash, :ip_address)'
    );
    $stmt->execute([
        ':identity_hash' => login_identity_hash($identity),
        ':ip_address' => client_ip(),
    ]);
}

function clear_login_attempts(string $identity): void
{
    $stmt = db()->prepare(
        'DELETE FROM login_attempts
         WHERE identity_hash = :identity_hash AND ip_address = :ip_address'
    );
    $stmt->execute([
        ':identity_hash' => login_identity_hash($identity),
        ':ip_address' => client_ip(),
    ]);
}

function ensure_starter_inventory(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO player_inventory (user_id, item_id, equipped)
         SELECT :user_id, id, 1 FROM shop_items WHERE slug = "nebula-green"'
    );
    $stmt->execute([':user_id' => $userId]);
}

function get_shop_items(): array
{
    $stmt = db()->query(
        'SELECT id, slug, name, item_type, description, price_coins, price_gems, tone,
                stat_attack, stat_defense, power_effect, stackable,
                visual_type, image_path, animation_style
         FROM shop_items
         WHERE is_active = 1
         ORDER BY price_coins, id'
    );
    return $stmt->fetchAll();
}

function get_player_inventory(int $userId): array
{
    $pdo = db();
    ensure_starter_inventory($pdo, $userId);
    $stmt = $pdo->prepare(
        'SELECT si.id, si.slug, si.name, si.item_type, si.description, si.tone,
                si.stat_attack, si.stat_defense, si.power_effect, si.stackable,
                si.visual_type, si.image_path, si.animation_style,
                pi.quantity, pi.equipped, pi.equipped_slot, pi.acquired_at
         FROM player_inventory pi
         INNER JOIN shop_items si ON si.id = pi.item_id
         WHERE pi.user_id = :user_id
         ORDER BY pi.equipped DESC, pi.acquired_at DESC'
    );
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function get_inventory_item(int $userId, int $itemId): ?array
{
    $stmt = db()->prepare(
        'SELECT si.*, pi.quantity, pi.equipped, pi.equipped_slot
         FROM player_inventory pi
         INNER JOIN shop_items si ON si.id = pi.item_id
         WHERE pi.user_id = :user_id AND pi.item_id = :item_id
         LIMIT 1'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':item_id' => $itemId,
    ]);
    $item = $stmt->fetch();
    return $item ?: null;
}

function buy_shop_item(int $userId, int $itemId): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $itemStmt = $pdo->prepare('SELECT * FROM shop_items WHERE id = :id AND is_active = 1 FOR UPDATE');
        $itemStmt->execute([':id' => $itemId]);
        $item = $itemStmt->fetch();
        if (!$item) {
            throw new RuntimeException('Shop item not found.');
        }

        $saveStmt = $pdo->prepare('SELECT coins, gems FROM player_saves WHERE user_id = :user_id FOR UPDATE');
        $saveStmt->execute([':user_id' => $userId]);
        $save = $saveStmt->fetch();
        if (!$save) {
            create_default_save($pdo, $userId);
            $saveStmt->execute([':user_id' => $userId]);
            $save = $saveStmt->fetch();
        }

        if ((int) $save['coins'] < (int) $item['price_coins'] || (int) $save['gems'] < (int) $item['price_gems']) {
            throw new RuntimeException('Not enough coins or gems.');
        }

        $owned = get_inventory_item($userId, $itemId);
        if ($owned && (int) $item['stackable'] !== 1) {
            throw new RuntimeException('You already own this item.');
        }

        $pdo->prepare(
            'UPDATE player_saves
             SET coins = coins - :coins, gems = gems - :gems
             WHERE user_id = :user_id'
        )->execute([
            ':coins' => (int) $item['price_coins'],
            ':gems' => (int) $item['price_gems'],
            ':user_id' => $userId,
        ]);

        $pdo->prepare(
            'INSERT INTO player_inventory (user_id, item_id, quantity, equipped, equipped_slot)
             VALUES (:user_id, :item_id, 1, 0, NULL)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
        )->execute([
            ':user_id' => $userId,
            ':item_id' => $itemId,
        ]);

        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function equipment_slot_for_type(string $itemType): ?string
{
    return match ($itemType) {
        'skin' => 'skin',
        'offense' => 'offense',
        'defense' => 'defense',
        'tool' => 'tool',
        default => null,
    };
}

function equip_inventory_item(int $userId, int $itemId): void
{
    $item = get_inventory_item($userId, $itemId);
    if (!$item) {
        throw new RuntimeException('You do not own this item.');
    }
    $slot = equipment_slot_for_type((string) $item['item_type']);
    if (!$slot) {
        throw new RuntimeException('This item cannot be equipped.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'UPDATE player_inventory pi
             INNER JOIN shop_items si ON si.id = pi.item_id
             SET pi.equipped = 0, pi.equipped_slot = NULL
             WHERE pi.user_id = :user_id AND si.item_type = :item_type'
        )->execute([
            ':user_id' => $userId,
            ':item_type' => $item['item_type'],
        ]);

        $pdo->prepare(
            'UPDATE player_inventory
             SET equipped = 1, equipped_slot = :slot
             WHERE user_id = :user_id AND item_id = :item_id'
        )->execute([
            ':slot' => $slot,
            ':user_id' => $userId,
            ':item_id' => $itemId,
        ]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function equipped_loadout(int $userId): array
{
    $loadout = ['skin' => null, 'offense' => null, 'defense' => null, 'tool' => null];
    foreach (get_player_inventory($userId) as $item) {
        if ((int) $item['equipped'] === 1 && $item['equipped_slot']) {
            $loadout[$item['equipped_slot']] = $item;
        }
    }
    return $loadout;
}

function get_achievements(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT a.id, a.slug, a.name, a.description, a.reward_coins,
                CASE WHEN pa.user_id IS NULL THEN 0 ELSE 1 END AS earned,
                pa.unlocked_at
         FROM achievements a
         LEFT JOIN player_achievements pa
           ON pa.achievement_id = a.id AND pa.user_id = :user_id
         WHERE a.is_active = 1
         ORDER BY a.id'
    );
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function award_achievement_by_name(int $userId, string $name): void
{
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    $stmt = db()->prepare(
        'INSERT IGNORE INTO player_achievements (user_id, achievement_id)
         SELECT :user_id, id FROM achievements
         WHERE slug = :slug OR name = :name'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':slug' => $slug,
        ':name' => $name,
    ]);
}

function current_save_row(int $userId): array
{
    $pdo = db();
    create_default_save($pdo, $userId);
    $stmt = $pdo->prepare('SELECT * FROM player_saves WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch();
}

function load_player_save(int $userId): array
{
    $row = current_save_row($userId);

    return [
        'level' => (int) $row['level'],
        'xp' => (int) $row['xp'],
        'coins' => (int) $row['coins'],
        'gems' => (int) $row['gems'],
        'rank' => $row['rank_name'],
        'current_checkpoint' => $row['current_checkpoint'],
        'skins' => array_column(get_player_inventory($userId), 'name'),
        'achievements' => array_values(array_map(
            static fn (array $achievement): string => $achievement['name'],
            array_filter(get_achievements($userId), static fn (array $achievement): bool => (int) $achievement['earned'] === 1)
        )),
        'progress' => json_decode($row['progress'], true) ?: [],
    ];
}

function save_player_progress(int $userId, array $save): void
{
    $current = current_save_row($userId);
    $currentLevel = (int) $current['level'];
    $currentXp = (int) $current['xp'];
    $currentCoins = (int) $current['coins'];
    $currentGems = (int) $current['gems'];
    $newLevel = max(1, min($currentLevel + 1, min(99, (int) ($save['level'] ?? $currentLevel))));
    $newXp = max($currentXp, min($currentXp + 150, (int) ($save['xp'] ?? $currentXp)));
    $newCoins = max(0, min($currentCoins + 75, (int) ($save['coins'] ?? $currentCoins)));
    $newGems = max(0, min($currentGems + 1, (int) ($save['gems'] ?? $currentGems)));
    $allowedCheckpoints = ['Start', 'Lunar Gate', 'Orion Peak'];
    $checkpoint = (string) ($save['current_checkpoint'] ?? $current['current_checkpoint']);
    if (!in_array($checkpoint, $allowedCheckpoints, true)) {
        $checkpoint = $current['current_checkpoint'];
    }
    $rankName = rank_for_level($newLevel);
    $allowedAchievements = allowed_progress_achievements($save, $current, [
        'level' => $newLevel,
        'xp' => $newXp,
        'coins' => $newCoins,
        'checkpoint' => $checkpoint,
    ]);

    $stmt = db()->prepare(
        'UPDATE player_saves
         SET level = :level, xp = :xp, coins = :coins, gems = :gems,
             rank_name = :rank_name, current_checkpoint = :current_checkpoint,
             skins = :skins, achievements = :achievements, progress = :progress
         WHERE user_id = :user_id'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':level' => $newLevel,
        ':xp' => $newXp,
        ':coins' => $newCoins,
        ':gems' => $newGems,
        ':rank_name' => $rankName,
        ':current_checkpoint' => $checkpoint,
        ':skins' => $current['skins'],
        ':achievements' => json_encode($allowedAchievements),
        ':progress' => json_encode((object) ($save['progress'] ?? [])),
    ]);

    foreach ($allowedAchievements as $achievementName) {
        award_achievement_by_name($userId, $achievementName);
    }
}

function rank_for_level(int $level): string
{
    if ($level >= 10) {
        return 'Galaxy Master';
    }
    if ($level >= 5) {
        return 'Nebula Climber';
    }
    if ($level >= 2) {
        return 'Moon Hopper';
    }
    return 'Rookie Comet';
}

function allowed_progress_achievements(array $postedSave, array $current, array $validated): array
{
    $posted = array_flip(array_map('strval', $postedSave['achievements'] ?? []));
    $allowed = [];

    if (isset($posted['First Launch'])) {
        $allowed[] = 'First Launch';
    }
    if (isset($posted['Coin Comet']) && $validated['coins'] > (int) $current['coins']) {
        $allowed[] = 'Coin Comet';
    }
    if (isset($posted['Gravity Master'])) {
        $allowed[] = 'Gravity Master';
    }
    if (isset($posted['Checkpoint Chaser']) && $validated['checkpoint'] !== $current['current_checkpoint']) {
        $allowed[] = 'Checkpoint Chaser';
    }
    if (isset($posted['Galaxy Climber']) && $validated['checkpoint'] === 'Orion Peak') {
        $allowed[] = 'Galaxy Climber';
    }

    return array_values(array_unique($allowed));
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function require_user(): int
{
    $user = current_user();
    if (!$user) {
        json_response(['ok' => false, 'message' => 'Authentication required.'], 401);
    }
    return (int) $user['id'];
}
