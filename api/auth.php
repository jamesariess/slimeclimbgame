<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
start_app_session();

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';
$pdo = db();

try {
    if ($action === 'register') {
        $username = trim((string) ($input['username'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');

        if (!preg_match('/^[a-zA-Z0-9_]{3,24}$/', $username)) {
            json_response(['ok' => false, 'message' => 'Username must be 3-24 letters, numbers, or underscores.'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['ok' => false, 'message' => 'Enter a valid email address.'], 422);
        }
        if (strlen($password) < 8) {
            json_response(['ok' => false, 'message' => 'Password must be at least 8 characters.'], 422);
        }

        $pdo->beginTransaction();
        $role = (!admin_exists($pdo) && strtolower($username) === 'admin') ? 'admin' : 'user';
        $stmt = $pdo->prepare('INSERT INTO users (username, email, role, password_hash) VALUES (:username, :email, :role, :password_hash)');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':role' => $role,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $userId = (int) $pdo->lastInsertId();
        create_default_save($pdo, $userId);
        $pdo->commit();

        $_SESSION['user'] = ['id' => $userId, 'username' => $username, 'email' => $email, 'role' => $role];
        json_response(['ok' => true, 'user' => $_SESSION['user']]);
    }

    if ($action === 'login') {
        $identity = trim((string) ($input['identity'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        if (too_many_login_attempts($identity)) {
            json_response(['ok' => false, 'message' => 'Too many login attempts. Wait 15 minutes and try again.'], 429);
        }
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username_identity OR email = :email_identity LIMIT 1');
        $stmt->execute([
            ':username_identity' => $identity,
            ':email_identity' => $identity,
        ]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            record_login_failure($identity);
            json_response(['ok' => false, 'message' => 'Invalid username/email or password.'], 401);
        }

        clear_login_attempts($identity);
        session_regenerate_id(true);
        $_SESSION['user'] = public_user($user);
        json_response(['ok' => true, 'user' => $_SESSION['user']]);
    }

    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        json_response(['ok' => true]);
    }

    if ($action === 'forgot') {
        json_response(['ok' => true, 'message' => 'Password reset is ready for email provider integration. Contact support for this local build.']);
    }

    json_response(['ok' => false, 'message' => 'Unknown auth action.'], 400);
} catch (PDOException $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $message = str_contains($error->getMessage(), 'UNIQUE') ? 'Username or email already exists.' : 'Authentication service error.';
    json_response(['ok' => false, 'message' => $message], 500);
}
