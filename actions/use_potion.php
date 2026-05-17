<?php
require __DIR__ . '/../api/config.php';
$user = require_login();
verify_csrf_from_post();

try {
    use_potion_item((int) $user['id'], (int) ($_POST['item_id'] ?? 0));
    $_SESSION['flash_success'] = 'Potion effect stacked.';
} catch (Throwable $error) {
    $_SESSION['flash_error'] = $error->getMessage();
}

header('Location: ../inventory.php');
exit;

