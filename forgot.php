<?php
require __DIR__ . '/api/config.php';
$pageTitle = 'Forgot Password - Slime Climb Galaxy';
$bodyClass = 'auth-page';
require __DIR__ . '/partials/head.php';
?>
<main class="auth-layout">
  <a class="back-link" href="login.php">&larr; Login</a>
  <section class="simple-panel">
    <p class="kicker">Account recovery</p>
    <h1>Forgot Password</h1>
    <p class="muted">This local XAMPP build is ready for email reset integration. For now, update the account from phpMyAdmin or add an SMTP provider later.</p>
    <a class="button primary" href="login.php">Back to Login</a>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

