<?php
require __DIR__ . '/../api/config.php';
$user = require_login();
verify_csrf_from_post();

try {
    buy_shop_item((int) $user['id'], (int) ($_POST['item_id'] ?? 0));
    $_SESSION['flash_success'] = 'Item purchased.';
} catch (Throwable $error) {
    $_SESSION['flash_error'] = $error->getMessage();
}

header('Location: ../shop.php');
exit;

