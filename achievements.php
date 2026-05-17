<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$achievements = get_achievements((int) $user['id']);
$earnedCount = count(array_filter($achievements, static fn (array $achievement): bool => (int) $achievement['earned'] === 1));
$totalCount = max(1, count($achievements));
$earnedPercent = min(100, (int) round(($earnedCount / $totalCount) * 100));
$pageTitle = 'Achievements - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="game-subpage achievements-screen">
  <section class="subpage-hero achievements-hero">
    <div>
      <p class="kicker">Trophy constellation</p>
      <h1>Achievements</h1>
      <p class="muted">Track galaxy milestones, unlock rewards, and complete your slime legend.</p>
      <div class="achievement-progress">
        <span style="width: <?php echo $earnedPercent; ?>%"></span>
      </div>
    </div>
    <div class="vault-count">
      <span>Unlocked</span>
      <strong><?php echo $earnedCount; ?>/<?php echo count($achievements); ?></strong>
    </div>
  </section>
  <section class="achievement-constellation">
    <?php foreach ($achievements as $achievement): ?>
      <?php $earned = (int) $achievement['earned'] === 1; ?>
      <article class="trophy-card <?php echo $earned ? 'earned' : 'locked'; ?>">
        <div class="trophy-orb"><span></span></div>
        <span class="card-tag"><?php echo $earned ? 'Unlocked' : 'Locked'; ?></span>
        <h2><?php echo htmlspecialchars($achievement['name']); ?></h2>
        <p><?php echo htmlspecialchars($achievement['description']); ?></p>
        <small><?php echo (int) $achievement['reward_coins']; ?> coin reward</small>
      </article>
    <?php endforeach; ?>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
