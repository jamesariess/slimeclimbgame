<?php
require __DIR__ . '/../api/config.php';
$user = require_admin();
verify_csrf_from_post();

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
        $imagePath = trim((string) ($_POST['image_path'] ?? ''));
        $uploadedPath = handle_slime_image_upload($slug);
        if ($uploadedPath !== '') {
            $imagePath = $uploadedPath;
        }
        if ($imagePath !== '' && !preg_match('#^assets/images/(shop-slimes|items)/[a-zA-Z0-9._/-]+$#', $imagePath)) {
            throw new RuntimeException('Image path must be inside assets/images/shop-slimes/ or assets/images/items/.');
        }
        $visualType = (string) ($_POST['visual_type'] ?? 'css_slime');
        if (!in_array($visualType, ['css_slime', 'image'], true)) {
            $visualType = 'css_slime';
        }
        if ($visualType === 'image' && $imagePath === '') {
            throw new RuntimeException('Choose an image or use generated animated slime.');
        }
        $animationStyle = (string) ($_POST['animation_style'] ?? 'float');
        if (!in_array($animationStyle, ['float', 'bounce', 'pulse'], true)) {
            $animationStyle = 'float';
        }
        $category = (string) ($_POST['category'] ?? 'skins');
        if (!in_array($category, ['skins', 'boosts', 'trails', 'bundles', 'limited', 'seasonal'], true)) {
            $category = 'skins';
        }
        $rarity = (string) ($_POST['rarity'] ?? 'common');
        if (!in_array($rarity, ['common', 'rare', 'epic', 'legendary', 'mythic'], true)) {
            $rarity = 'common';
        }
        $limitedUntil = trim((string) ($_POST['limited_until'] ?? ''));
        $limitedUntil = $limitedUntil !== '' ? str_replace('T', ' ', $limitedUntil) . ':00' : null;

        $stmt = db()->prepare(
            'INSERT INTO shop_items
                (slug, name, item_type, category, rarity, description, price_coins, price_gems, tone, stat_attack, stat_defense, stat_jump, power_effect, stackable, visual_type, image_path, animation_style, sale_percent, limited_until)
             VALUES
                (:slug, :name, :item_type, :category, :rarity, :description, :price_coins, :price_gems, :tone, :stat_attack, :stat_defense, :stat_jump, :power_effect, :stackable, :visual_type, :image_path, :animation_style, :sale_percent, :limited_until)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                item_type = VALUES(item_type),
                category = VALUES(category),
                rarity = VALUES(rarity),
                description = VALUES(description),
                price_coins = VALUES(price_coins),
                price_gems = VALUES(price_gems),
                tone = VALUES(tone),
                stat_attack = VALUES(stat_attack),
                stat_defense = VALUES(stat_defense),
                stat_jump = VALUES(stat_jump),
                power_effect = VALUES(power_effect),
                stackable = VALUES(stackable),
                visual_type = VALUES(visual_type),
                image_path = VALUES(image_path),
                animation_style = VALUES(animation_style),
                sale_percent = VALUES(sale_percent),
                limited_until = VALUES(limited_until),
                is_active = 1'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':name' => $name,
            ':item_type' => allowed_item_type((string) ($_POST['item_type'] ?? 'skin')),
            ':category' => $category,
            ':rarity' => $rarity,
            ':description' => substr($description, 0, 255),
            ':price_coins' => max(0, (int) ($_POST['price_coins'] ?? 0)),
            ':price_gems' => max(0, (int) ($_POST['price_gems'] ?? 0)),
            ':tone' => substr((string) ($_POST['tone'] ?? 'green'), 0, 30),
            ':stat_attack' => max(0, (int) ($_POST['stat_attack'] ?? 0)),
            ':stat_defense' => max(0, (int) ($_POST['stat_defense'] ?? 0)),
            ':stat_jump' => max(0, (int) ($_POST['stat_jump'] ?? 0)),
            ':power_effect' => substr((string) ($_POST['power_effect'] ?? ''), 0, 120),
            ':stackable' => !empty($_POST['stackable']) ? 1 : 0,
            ':visual_type' => $visualType,
            ':image_path' => $imagePath !== '' ? $imagePath : null,
            ':animation_style' => $animationStyle,
            ':sale_percent' => min(90, max(0, (int) ($_POST['sale_percent'] ?? 0))),
            ':limited_until' => $limitedUntil,
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

function allowed_item_type(string $itemType): string
{
    return in_array($itemType, ['skin', 'offense', 'defense', 'tool', 'wings', 'potion'], true) ? $itemType : 'skin';
}

function handle_slime_image_upload(string $slug): string
{
    if (empty($_FILES['slime_image']) || ($_FILES['slime_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($_FILES['slime_image']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    if ($_FILES['slime_image']['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Image must be 2MB or smaller.');
    }

    $tmp = $_FILES['slime_image']['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $extensions = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Use PNG, JPG, WEBP, or GIF images only.');
    }

    $dir = __DIR__ . '/../assets/images/shop-slimes';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $filename = $slug . '-' . bin2hex(random_bytes(4)) . '.' . $extensions[$mime];
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Could not save uploaded image.');
    }

    return 'assets/images/shop-slimes/' . $filename;
}
