<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$loadout = equipped_loadout((int) $user['id']);
$effects = active_effects((int) $user['id']);
$baseStats = total_loadout_stats($loadout, []);
$stats = total_loadout_stats($loadout, $effects);
$pageTitle = 'Play - Slime Climb Galaxy';
$bodyClass = 'game-page';
require __DIR__ . '/partials/head.php';
?>
<header class="game-hud">
  <a class="button ghost small" href="home.php">&larr; Hub</a>
  <span>Coins <strong id="hudCoins"><?php echo $save['coins']; ?></strong></span>
  <span>Level <strong id="hudLevel"><?php echo $save['level']; ?></strong></span>
  <span>HP <strong id="hudHp">100</strong></span>
  <span>ATK <strong id="hudAtk"><?php echo (int) $stats['attack']; ?></strong></span>
  <span>DEF <strong id="hudDef"><?php echo (int) $stats['defense']; ?></strong></span>
  <span>Climb <strong id="hudDistance">0m</strong></span>
  <span id="hudCheckpoint">Checkpoint: <?php echo htmlspecialchars($save['current_checkpoint']); ?></span>
</header>
<canvas id="gameCanvas"></canvas>
<div class="mobile-controls">
  <button data-control="left">Left</button>
  <button data-control="jump">Jump</button>
  <button data-control="right">Right</button>
  <button data-control="gravity">Flip</button>
  <button data-control="attack">Attack</button>
</div>
<script>
  window.SLIME_SAVE = <?php echo json_encode($save); ?>;
  window.SLIME_LOADOUT = <?php echo json_encode($loadout); ?>;
  window.SLIME_EFFECTS = <?php echo json_encode($effects); ?>;
  window.SLIME_BASE_STATS = <?php echo json_encode($baseStats); ?>;
  window.SLIME_STATS = <?php echo json_encode($stats); ?>;
  window.CSRF_TOKEN = <?php echo json_encode(csrf_token()); ?>;
</script>
<script src="assets/js/game-stages.js"></script>
<script src="assets/js/game.js"></script>
</body>
</html>
