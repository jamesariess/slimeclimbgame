<?php
require __DIR__ . '/api/config.php';
require_login();
$pageTitle = 'Leaderboard - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="content-wrap">
  <section class="simple-panel">
    <p class="kicker">Online races</p>
    <h1>Leaderboard</h1>
    <p class="muted">This page is separated for future multiplayer and ranking features.</p>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

