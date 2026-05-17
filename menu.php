<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$pageTitle = 'Main Menu - Slime Climb Galaxy';
$bodyClass = 'menu-page';
require __DIR__ . '/partials/head.php';
?>
<main class="menu-shell">
  <section class="menu-hero">
    <div>
      <p class="kicker">Save loaded</p>
      <h1>Ready, <?php echo htmlspecialchars($user['username']); ?>?</h1>
      <p class="muted">Your galaxy profile is online. Start opens the character hub before the level begins.</p>
    </div>
    <div class="menu-actions">
      <a class="button primary button-xl" href="home.php">Start</a>
      <a class="button secondary button-xl" href="settings.php">Settings</a>
      <form action="actions/logout.php" method="post">
        <button class="ghost button-xl" type="submit">Logout</button>
      </form>
    </div>
  </section>
  <section class="quick-save-strip">
    <div><span>Level</span><strong><?php echo $save['level']; ?></strong></div>
    <div><span>Coins</span><strong><?php echo $save['coins']; ?></strong></div>
    <div><span>Gems</span><strong><?php echo $save['gems']; ?></strong></div>
    <div><span>Rank</span><strong><?php echo htmlspecialchars($save['rank']); ?></strong></div>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

