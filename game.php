<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$loadout = equipped_loadout((int) $user['id']);
$pageTitle = 'Play - Slime Climb Galaxy';
$bodyClass = 'game-page';
require __DIR__ . '/partials/head.php';
?>
<header class="game-hud">
  <a class="button ghost small" href="home.php">&larr; Hub</a>
  <span>Coins <strong id="hudCoins"><?php echo $save['coins']; ?></strong></span>
  <span>Level <strong id="hudLevel"><?php echo $save['level']; ?></strong></span>
  <span id="hudCheckpoint">Checkpoint: <?php echo htmlspecialchars($save['current_checkpoint']); ?></span>
</header>
<canvas id="gameCanvas"></canvas>
<div class="mobile-controls">
  <button data-control="left">Left</button>
  <button data-control="jump">Jump</button>
  <button data-control="right">Right</button>
  <button data-control="gravity">Flip</button>
</div>
<script>
  window.SLIME_SAVE = <?php echo json_encode($save); ?>;
  window.SLIME_LOADOUT = <?php echo json_encode($loadout); ?>;
  window.CSRF_TOKEN = <?php echo json_encode(csrf_token()); ?>;
</script>
<script src="assets/js/game.js"></script>
</body>
</html>
