<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$inventory = get_player_inventory((int) $user['id']);
$pageTitle = 'Inventory - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="content-wrap">
  <h1>Inventory</h1>
  <div class="card-grid">
    <?php foreach ($inventory as $item): ?>
      <article class="skin-card <?php echo htmlspecialchars($item['tone']); ?>">
        <div class="mini-slime"></div>
        <h2><?php echo htmlspecialchars($item['name']); ?></h2>
        <p><?php echo htmlspecialchars($item['description']); ?></p>
        <p><?php echo (int) $item['equipped'] === 1 ? 'Equipped' : 'Owned'; ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
