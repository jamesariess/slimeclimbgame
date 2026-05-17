<?php
require __DIR__ . '/api/config.php';
if (current_user()) {
    header('Location: menu.php');
    exit;
}
$pageTitle = 'Slime Climb Galaxy';
$bodyClass = 'title-page';
require __DIR__ . '/partials/head.php';
?>
<main class="title-stage">
  <div class="orbit planet-a"></div>
  <div class="orbit planet-b"></div>
  <section class="title-composition">
    <div class="hero-slime slime-large" aria-hidden="true">
      <span class="eye eye-left"></span>
      <span class="eye eye-right"></span>
      <span class="smile"></span>
    </div>
    <p class="kicker">Galaxy climbing adventure</p>
    <h1>Slime Climb Galaxy</h1>
    <p class="title-copy">Bounce through neon planets, climb gravity towers, collect comet coins, and build your slime hero.</p>
    <div class="title-auth-actions" aria-label="Account actions">
      <a class="button primary button-xl" href="login.php">Login</a>
      <a class="button secondary button-xl" href="register.php">Register</a>
    </div>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

