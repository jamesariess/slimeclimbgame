<?php
require __DIR__ . '/../api/config.php';
$admin = require_admin();
verify_csrf_from_post();

try {
    update_user_role((int) ($_POST['user_id'] ?? 0), (string) ($_POST['role'] ?? 'user'), (int) $admin['id']);
    $_SESSION['flash_success'] = 'User role updated.';
} catch (Throwable $error) {
    $_SESSION['flash_error'] = $error->getMessage();
}

header('Location: ../admin_users.php');
exit;
