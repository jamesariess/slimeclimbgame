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
  <div class="title-comet comet-one"></div>
  <div class="title-comet comet-two"></div>
  <section class="title-composition">
    <div class="title-slime-orbit" aria-hidden="true">
      <span></span><span></span><span></span>
      <div class="hero-slime slime-large">
        <span class="eye eye-left"></span>
        <span class="eye eye-right"></span>
        <span class="smile"></span>
      </div>
    </div>
    <p class="kicker">Galaxy climbing adventure</p>
    <h1>Slime Climb Galaxy</h1>
    <p class="title-copy">Bounce through neon planets, climb gravity towers, collect comet coins, and build your slime hero.</p>
    <a class="button primary button-xl title-start-button loading-link" href="login.php">Start Game</a>

  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
