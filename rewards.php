<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$today = date('Y-m-d');
$claimed = ($save['progress']['lastDailyReward'] ?? '') === $today;

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
  <section class="simple-panel reward-panel">
    <p class="kicker">Daily rewards</p>
    <h1><?php echo $claimed ? 'Reward Claimed' : 'Claim Today'; ?></h1>
    <p class="muted">Collect 50 coins and 1 gem once per day.</p>
    <form method="post">
      <button class="primary button-xl" type="submit" <?php echo $claimed ? 'disabled' : ''; ?>><?php echo $claimed ? 'Come Back Tomorrow' : 'Claim Reward'; ?></button>
    </form>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

