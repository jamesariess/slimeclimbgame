<?php
require __DIR__ . '/../api/config.php';
start_app_session();
verify_csrf_from_post();

$identity = trim((string) ($_POST['identity'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

try {
    if (too_many_login_attempts($identity)) {
        $_SESSION['flash_error'] = 'Too many login attempts. Wait 15 minutes and try again.';
        header('Location: ../login.php');
        exit;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username_identity OR email = :email_identity LIMIT 1');
    $stmt->execute([
        ':username_identity' => $identity,
        ':email_identity' => $identity,
    ]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_login_failure($identity);
        $_SESSION['flash_error'] = 'Invalid username/email or password.';
        header('Location: ../login.php');
        exit;
    }

    clear_login_attempts($identity);
    session_regenerate_id(true);
    $_SESSION['user'] = public_user($user);
    create_default_save(db(), (int) $user['id']);
    header('Location: ../menu.php');
    exit;
} catch (Throwable $error) {
    $_SESSION['flash_error'] = 'Database problem: ' . $error->getMessage() . ' Open setup.php to check the XAMPP MySQL connection.';
    header('Location: ../login.php');
    exit;
}
