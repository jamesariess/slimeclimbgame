<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$items = get_shop_items();
$owned = array_flip($save['skins']);
$pageTitle = 'Shop - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="content-wrap">
  <h1>Shop</h1>
  <div class="card-grid">
    <?php foreach ($items as $item): ?>
      <article class="skin-card <?php echo htmlspecialchars($item['tone']); ?>">
        <div class="mini-slime"></div>
        <h2><?php echo htmlspecialchars($item['name']); ?></h2>
        <p><?php echo htmlspecialchars($item['description']); ?></p>
        <p>
          <?php if (isset($owned[$item['name']])): ?>
            Owned
          <?php else: ?>
            <?php echo (int) $item['price_coins']; ?> coins<?php echo (int) $item['price_gems'] > 0 ? ' + ' . (int) $item['price_gems'] . ' gems' : ''; ?>
          <?php endif; ?>
        </p>
      </article>
    <?php endforeach; ?>
  </div>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
