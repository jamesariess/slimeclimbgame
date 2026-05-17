<?php
require __DIR__ . '/../api/config.php';
start_app_session();

$identity = trim((string) ($_POST['identity'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

try {
    $stmt = db()->prepare('SELECT * FROM users WHERE username = :identity OR email = :identity LIMIT 1');
    $stmt->execute([':identity' => $identity]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['flash_error'] = 'Invalid username/email or password.';
        header('Location: ../login.php');
        exit;
    }

    $_SESSION['user'] = public_user($user);
    create_default_save(db(), (int) $user['id']);
    header('Location: ../menu.php');
    exit;
} catch (Throwable $error) {
    $_SESSION['flash_error'] = 'Database problem: ' . $error->getMessage() . ' Open setup.php to check the XAMPP MySQL connection.';
    header('Location: ../login.php');
    exit;
}
