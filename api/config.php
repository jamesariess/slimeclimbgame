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
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

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
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS player_inventory (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            item_id INT UNSIGNED NOT NULL,
            equipped TINYINT(1) NOT NULL DEFAULT 0,
            acquired_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_player_item (user_id, item_id),
            CONSTRAINT fk_inventory_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_inventory_item FOREIGN KEY (item_id) REFERENCES shop_items(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

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

    seed_game_content($pdo);
}

function seed_game_content(PDO $pdo): void
{
    $items = [
        ['nebula-green', 'Nebula Green', 'skin', 'Starter slime skin with a soft galaxy glow.', 0, 0, 'green'],
        ['meteor-pink', 'Meteor Pink', 'skin', 'Bright pink slime skin for comet races.', 120, 0, 'pink'],
        ['solar-gold', 'Solar Gold', 'skin', 'Golden slime skin for high-score climbers.', 180, 0, 'gold'],
        ['void-cyan', 'Void Cyan', 'skin', 'Cool cyan slime skin from the deep nebula.', 240, 0, 'cyan'],
    ];
    $itemStmt = $pdo->prepare(
        'INSERT IGNORE INTO shop_items
            (slug, name, item_type, description, price_coins, price_gems, tone)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
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
        session_start();
    }
}

function public_user(array $user): array
{
    return [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
    ];
}

function current_user(): ?array
{
    start_app_session();
    return $_SESSION['user'] ?? null;
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
        'SELECT id, slug, name, item_type, description, price_coins, price_gems, tone
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
        'SELECT si.id, si.slug, si.name, si.item_type, si.description, si.tone, pi.equipped, pi.acquired_at
         FROM player_inventory pi
         INNER JOIN shop_items si ON si.id = pi.item_id
         WHERE pi.user_id = :user_id
         ORDER BY pi.equipped DESC, pi.acquired_at DESC'
    );
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
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

function load_player_save(int $userId): array
{
    $pdo = db();
    create_default_save($pdo, $userId);
    $stmt = $pdo->prepare('SELECT * FROM player_saves WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch();

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
    $stmt = db()->prepare(
        'UPDATE player_saves
         SET level = :level, xp = :xp, coins = :coins, gems = :gems,
             rank_name = :rank_name, current_checkpoint = :current_checkpoint,
             skins = :skins, achievements = :achievements, progress = :progress
         WHERE user_id = :user_id'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':level' => max(1, min(99, (int) ($save['level'] ?? 1))),
        ':xp' => max(0, (int) ($save['xp'] ?? 0)),
        ':coins' => max(0, (int) ($save['coins'] ?? 0)),
        ':gems' => max(0, (int) ($save['gems'] ?? 0)),
        ':rank_name' => substr((string) ($save['rank'] ?? 'Rookie Comet'), 0, 40),
        ':current_checkpoint' => substr((string) ($save['current_checkpoint'] ?? 'Start'), 0, 40),
        ':skins' => json_encode(array_values($save['skins'] ?? ['Nebula Green'])),
        ':achievements' => json_encode(array_values($save['achievements'] ?? [])),
        ':progress' => json_encode((object) ($save['progress'] ?? [])),
    ]);

    foreach (($save['achievements'] ?? []) as $achievementName) {
        award_achievement_by_name($userId, (string) $achievementName);
    }
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
