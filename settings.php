<?php
require __DIR__ . '/api/config.php';
require_login();
$pageTitle = 'Settings - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="settings-grid">
  <section class="simple-panel">
    <p class="kicker">Game settings</p>
    <h1>Settings</h1>
    <label class="toggle-row"><span>Music</span><input type="checkbox" checked></label>
    <label class="toggle-row"><span>Sound effects</span><input type="checkbox" checked></label>
    <label class="toggle-row"><span>Glow UI</span><input type="checkbox" checked></label>
    <label><span>UI brightness</span><input type="range" min="40" max="100" value="82"></label>
  </section>
  <section class="simple-panel">
    <p class="kicker">Controls</p>
    <h2>Keyboard</h2>
    <div class="control-grid">
      <span>A / Left</span><strong>Move left</strong>
      <span>D / Right</span><strong>Move right</strong>
      <span>W / Space</span><strong>Jump</strong>
      <span>G</span><strong>Flip gravity</strong>
    </div>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

