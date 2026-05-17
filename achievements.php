<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$achievements = get_achievements((int) $user['id']);
$pageTitle = 'Achievements - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="content-wrap">
  <h1>Achievements</h1>
  <div class="achievement-grid">
    <?php foreach ($achievements as $achievement): ?>
      <?php $earned = (int) $achievement['earned'] === 1; ?>
      <article class="achievement-card <?php echo $earned ? 'earned' : ''; ?>">
        <strong><?php echo $earned ? 'Unlocked' : 'Locked'; ?></strong>
        <span><?php echo htmlspecialchars($achievement['name']); ?></span>
        <p><?php echo htmlspecialchars($achievement['description']); ?></p>
        <small><?php echo (int) $achievement['reward_coins']; ?> coin reward</small>
      </article>
    <?php endforeach; ?>
  </div>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
