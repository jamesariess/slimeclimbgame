<?php
require __DIR__ . '/api/config.php';
require_login();
$pageTitle = 'Settings - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="game-subpage settings-screen">
  <section class="subpage-hero settings-hero">
    <p class="kicker">Game settings</p>
    <h1>Settings</h1>
    <p class="muted">Tune your galaxy cockpit, audio mix, interface glow, and climbing controls.</p>
  </section>
  <section class="settings-console">
    <div class="console-panel">
      <p class="kicker">Audio</p>
      <label class="toggle-row premium-toggle"><span>Music</span><input type="checkbox" checked></label>
      <label class="toggle-row premium-toggle"><span>Sound effects</span><input type="checkbox" checked></label>
      <label><span>Galaxy music volume</span><input type="range" min="0" max="100" value="72"></label>
    </div>
    <div class="console-panel">
      <p class="kicker">Visuals</p>
      <label class="toggle-row premium-toggle"><span>Glow UI</span><input type="checkbox" checked></label>
      <label class="toggle-row premium-toggle"><span>Particle effects</span><input type="checkbox" checked></label>
      <label><span>UI brightness</span><input type="range" min="40" max="100" value="82"></label>
    </div>
    <div class="console-panel controls-panel">
      <p class="kicker">Controls</p>
      <h2>Keyboard</h2>
      <div class="control-grid premium-controls">
        <span>A / Left</span><strong>Move left</strong>
        <span>D / Right</span><strong>Move right</strong>
        <span>W / Space</span><strong>Jump</strong>
        <span>G</span><strong>Flip gravity</strong>
      </div>
    </div>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
