<?php
require __DIR__ . '/api/config.php';
redirect_if_logged_in();
$pageTitle = 'Register - Slime Climb Galaxy';
$bodyClass = 'auth-page';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
require __DIR__ . '/partials/head.php';
?>
<main class="auth-layout">
  <a class="back-link loading-link" href="index.php">&larr; Title</a>
  <section class="auth-card premium-auth-card">
    <div class="auth-art register-art">
      <div class="auth-badge-row"><span>Starter coins</span><span>Cloud save</span></div>
      <div class="hero-slime slime-medium pink-slime auth-slime" aria-hidden="true"></div>
      <p class="kicker">New galaxy save</p>
      <h1>Register</h1>
      <p class="muted">Create your online profile and start with beginner coins, gems, and the Nebula Green skin.</p>
    </div>
    <form class="form-stack" action="actions/register.php" method="post">
      <?php echo csrf_field(); ?>
      <label>
        <span>Username</span>
        <input name="username" type="text" autocomplete="username" minlength="3" maxlength="24" required>
      </label>
      <label>
        <span>Email</span>
        <input name="email" type="email" autocomplete="email" required>
      </label>
      <label>
        <span>Password</span>
        <input name="password" type="password" autocomplete="new-password" minlength="8" required>
      </label>
      <?php if ($error): ?><p class="form-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
      <button class="primary auth-submit" type="submit">Create Account</button>
      <p class="switch-copy">Already registered? <a href="login.php">Login</a></p>
    </form>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
