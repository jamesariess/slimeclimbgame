<?php
require __DIR__ . '/../api/config.php';
start_app_session();
verify_csrf_from_post();

$username = trim((string) ($_POST['username'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');

if (!preg_match('/^[a-zA-Z0-9_]{3,24}$/', $username)) {
    $_SESSION['flash_error'] = 'Username must be 3-24 letters, numbers, or underscores.';
    header('Location: ../register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Enter a valid email address.';
    header('Location: ../register.php');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['flash_error'] = 'Password must be at least 8 characters.';
    header('Location: ../register.php');
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    $userId = (int) $pdo->lastInsertId();
    create_default_save($pdo, $userId);
    $pdo->commit();

    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $userId, 'username' => $username, 'email' => $email];
    header('Location: ../menu.php');
    exit;
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_error'] = str_contains($error->getMessage(), 'Duplicate')
        ? 'Username or email already exists.'
        : 'Database problem: ' . $error->getMessage() . ' Open setup.php to check the XAMPP MySQL connection.';
    header('Location: ../register.php');
    exit;
}
