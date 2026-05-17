<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$pageTitle = 'Character Hub - Slime Climb Galaxy';
$bodyClass = 'hub-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="hub-layout">
  <section class="profile-panel">
    <div class="avatar-orb"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
    <p><?php echo htmlspecialchars($save['rank']); ?></p>
    <div class="xp-bar"><span style="width: <?php echo min(100, $save['xp'] % 100); ?>%"></span></div>
    <div class="wallet-grid">
      <div><span>Coins</span><strong><?php echo $save['coins']; ?></strong></div>
      <div><span>Gems</span><strong><?php echo $save['gems']; ?></strong></div>
    </div>
  </section>

  <section class="character-stage">
    <div class="ring ring-one"></div>
    <div class="ring ring-two"></div>
    <div class="hero-slime character-slime" aria-hidden="true">
      <span class="eye eye-left"></span>
      <span class="eye eye-right"></span>
      <span class="smile"></span>
    </div>
    <div class="character-name">Nebula Green Slime</div>
    <a class="button primary play-now" href="game.php">Play Level</a>
  </section>

  <section class="hub-actions">
    <a class="hub-tile" href="game.php"><strong>Continue</strong><span><?php echo htmlspecialchars($save['current_checkpoint']); ?></span></a>
    <a class="hub-tile" href="shop.php"><strong>Shop</strong><span>Skins and boosts</span></a>
    <a class="hub-tile" href="inventory.php"><strong>Inventory</strong><span><?php echo count($save['skins']); ?> skins owned</span></a>
    <a class="hub-tile" href="achievements.php"><strong>Achievements</strong><span><?php echo count($save['achievements']); ?> unlocked</span></a>
    <a class="hub-tile" href="rewards.php"><strong>Daily Rewards</strong><span>Coins and gems</span></a>
    <a class="hub-tile muted-tile" href="leaderboard.php"><strong>Leaderboard</strong><span>Coming soon</span></a>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

