<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$items = get_shop_items();
$inventory = get_player_inventory((int) $user['id']);
$owned = [];
foreach ($inventory as $ownedItem) {
    $owned[(int) $ownedItem['id']] = $ownedItem;
}
$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$pageTitle = 'Shop - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="game-subpage shop-screen">
  <section class="subpage-hero shop-hero">
    <div>
      <p class="kicker">Galaxy market</p>
      <h1>Slime Shop</h1>
      <p class="muted">Unlock premium slime skins, future boosts, and collectible cosmetics from the galaxy vault.</p>
      <?php if ($message): ?><p class="form-success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
      <?php if ($error): ?><p class="form-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    </div>
    <div class="shop-wallet">
      <div><span>Coins</span><strong><?php echo (int) $save['coins']; ?></strong></div>
      <div><span>Gems</span><strong><?php echo (int) $save['gems']; ?></strong></div>
    </div>
  </section>
  <section class="shop-showcase">
    <?php foreach ($items as $item): ?>
      <?php $ownedItem = $owned[(int) $item['id']] ?? null; ?>
      <?php $isOwned = $ownedItem !== null; ?>
      <article class="premium-item-card <?php echo htmlspecialchars($item['tone']); ?> <?php echo $isOwned ? 'owned' : ''; ?>">
        <div class="item-glow"></div>
        <?php if ($item['visual_type'] === 'image' && !empty($item['image_path'])): ?>
          <div class="slime-asset <?php echo htmlspecialchars($item['animation_style']); ?>">
            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
          </div>
        <?php else: ?>
          <div class="hero-slime shop-slime <?php echo htmlspecialchars($item['animation_style']); ?>" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="item-copy">
          <span class="card-tag"><?php echo htmlspecialchars($item['item_type']); ?></span>
          <h2><?php echo htmlspecialchars($item['name']); ?></h2>
          <p><?php echo htmlspecialchars($item['description']); ?></p>
          <div class="item-stats">
            <?php if ((int) $item['stat_attack'] !== 0): ?><span>ATK +<?php echo (int) $item['stat_attack']; ?></span><?php endif; ?>
            <?php if ((int) $item['stat_defense'] !== 0): ?><span>DEF +<?php echo (int) $item['stat_defense']; ?></span><?php endif; ?>
            <?php if ($item['power_effect'] !== ''): ?><span><?php echo htmlspecialchars($item['power_effect']); ?></span><?php endif; ?>
          </div>
        </div>
        <div class="item-footer">
          <div>
            <strong><?php echo $isOwned ? 'Owned' : (int) $item['price_coins'] . ' coins'; ?></strong>
            <span><?php echo $isOwned && (int) $item['stackable'] === 1 ? 'Qty ' . (int) $ownedItem['quantity'] : ((int) $item['price_gems'] > 0 ? (int) $item['price_gems'] . ' gems' : htmlspecialchars($item['item_type'])); ?></span>
          </div>
          <form action="actions/buy_item.php" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="item_id" value="<?php echo (int) $item['id']; ?>">
            <button class="small primary" type="submit" <?php echo $isOwned && (int) $item['stackable'] !== 1 ? 'disabled' : ''; ?>>
              <?php echo $isOwned && (int) $item['stackable'] !== 1 ? 'Owned' : 'Buy'; ?>
            </button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
