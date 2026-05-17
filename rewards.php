<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$today = date('Y-m-d');
$claimed = ($save['progress']['lastDailyReward'] ?? '') === $today;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_from_post();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$claimed) {
    $save['coins'] += 50;
    $save['gems'] += 1;
    $save['progress']['lastDailyReward'] = $today;
    save_player_progress((int) $user['id'], $save);
    header('Location: rewards.php');
    exit;
}

$pageTitle = 'Daily Rewards - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="reward-layout">
  <section class="simple-panel reward-panel <?php echo $claimed ? 'claimed' : 'ready'; ?>">
    <p class="kicker">Daily rewards</p>
    <h1><?php echo $claimed ? 'Reward Claimed' : 'Claim Today'; ?></h1>
    <div class="daily-chest <?php echo $claimed ? 'open' : ''; ?>" aria-hidden="true">
      <span class="chest-lid"></span>
      <span class="chest-body"></span>
      <span class="reward-spark spark-a"></span>
      <span class="reward-spark spark-b"></span>
      <span class="reward-spark spark-c"></span>
    </div>
    <p class="muted">Collect 50 coins and 1 gem once per day.</p>
    <form method="post">
      <?php echo csrf_field(); ?>
      <button class="primary button-xl" type="submit" <?php echo $claimed ? 'disabled' : ''; ?>><?php echo $claimed ? 'Come Back Tomorrow' : 'Claim Reward'; ?></button>
    </form>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
