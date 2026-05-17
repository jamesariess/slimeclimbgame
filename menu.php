<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$pageTitle = 'Main Menu - Slime Climb Galaxy';
$bodyClass = 'menu-page';
require __DIR__ . '/partials/head.php';
?>
<main class="menu-shell">
  <section class="menu-hero premium-menu-hero">
    <div>
      <p class="kicker">Save loaded</p>
      <h1>Ready, <?php echo htmlspecialchars($user['username']); ?>?</h1>
      <p class="muted">Your galaxy profile is online. Start opens the character hub with your loadout, daily rewards, and next mission.</p>
      <div class="menu-save-pills">
        <span>Level <?php echo $save['level']; ?></span>
        <span><?php echo htmlspecialchars($save['rank']); ?></span>
        <span><?php echo htmlspecialchars($save['current_checkpoint']); ?></span>
      </div>
    </div>
    <div class="menu-slime-card" aria-hidden="true">
      <div class="hero-slime slime-medium"></div>
      <span></span><span></span><span></span>
    </div>
    <div class="menu-actions">
      <a class="button primary button-xl loading-link" href="home.php">Start</a>
      <a class="button secondary button-xl loading-link" href="settings.php">Settings</a>
      <?php if (is_admin($user)): ?>
        <a class="button secondary button-xl loading-link" href="admin_panel.php">Admin Panel</a>
      <?php endif; ?>
      <form action="actions/logout.php" method="post">
        <?php echo csrf_field(); ?>
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
