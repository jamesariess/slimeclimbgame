<?php
require __DIR__ . '/../api/config.php';
$user = require_login();
verify_csrf_from_post();

if (strtolower($user['username']) !== 'admin') {
    header('Location: ../menu.php');
    exit;
}

$type = (string) ($_POST['content_type'] ?? '');
$slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
$name = trim((string) ($_POST['name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));

if (!preg_match('/^[a-z0-9-]{3,60}$/', $slug) || $name === '') {
    $_SESSION['flash_error'] = 'Use a valid name and a slug with lowercase letters, numbers, and dashes.';
    header('Location: ../admin_content.php');
    exit;
}

try {
    if ($type === 'shop_item') {
        $stmt = db()->prepare(
            'INSERT INTO shop_items
                (slug, name, item_type, description, price_coins, price_gems, tone)
             VALUES
                (:slug, :name, :item_type, :description, :price_coins, :price_gems, :tone)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                item_type = VALUES(item_type),
                description = VALUES(description),
                price_coins = VALUES(price_coins),
                price_gems = VALUES(price_gems),
                tone = VALUES(tone),
                is_active = 1'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':name' => $name,
            ':item_type' => substr((string) ($_POST['item_type'] ?? 'skin'), 0, 30),
            ':description' => substr($description, 0, 255),
            ':price_coins' => max(0, (int) ($_POST['price_coins'] ?? 0)),
            ':price_gems' => max(0, (int) ($_POST['price_gems'] ?? 0)),
            ':tone' => substr((string) ($_POST['tone'] ?? 'green'), 0, 30),
        ]);
        $_SESSION['flash_success'] = 'Shop item saved.';
    } elseif ($type === 'achievement') {
        $stmt = db()->prepare(
            'INSERT INTO achievements
                (slug, name, description, reward_coins)
             VALUES
                (:slug, :name, :description, :reward_coins)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                description = VALUES(description),
                reward_coins = VALUES(reward_coins),
                is_active = 1'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':name' => $name,
            ':description' => substr($description, 0, 255),
            ':reward_coins' => max(0, (int) ($_POST['reward_coins'] ?? 0)),
        ]);
        $_SESSION['flash_success'] = 'Achievement saved.';
    }
} catch (Throwable $error) {
    $_SESSION['flash_error'] = $error->getMessage();
}

header('Location: ../admin_content.php');
exit;
