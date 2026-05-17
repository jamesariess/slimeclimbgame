<?php
require __DIR__ . '/api/config.php';
redirect_if_logged_in();
$pageTitle = 'Login - Slime Climb Galaxy';
$bodyClass = 'auth-page';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
require __DIR__ . '/partials/head.php';
?>
<main class="auth-layout">
  <a class="back-link" href="index.php">&larr; Title</a>
  <section class="auth-card">
    <div class="auth-art">
      <div class="hero-slime slime-medium" aria-hidden="true"></div>
      <p class="kicker">Welcome back climber</p>
      <h1>Login</h1>
      <p class="muted">Load coins, skins, checkpoints, achievements, and galaxy progress from MySQL.</p>
    </div>
    <form class="form-stack" action="actions/login.php" method="post">
      <?php echo csrf_field(); ?>
      <label>
        <span>Username or email</span>
        <input name="identity" type="text" autocomplete="username" required>
      </label>
      <label>
        <span>Password</span>
        <input name="password" type="password" autocomplete="current-password" minlength="8" required>
      </label>
      <?php if ($error): ?><p class="form-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
      <button class="primary" type="submit">Login</button>
      <a class="text-link" href="forgot.php">Forgot password?</a>
      <p class="switch-copy">No account yet? <a href="register.php">Create one</a></p>
    </form>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
