<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$items = get_shop_items();
$inventory = get_player_inventory((int) $user['id']);
$activeCategory = $_GET['category'] ?? 'featured';
$validCategories = ['featured', 'skins', 'boosts', 'trails', 'bundles', 'limited', 'seasonal'];
if (!in_array($activeCategory, $validCategories, true)) {
    $activeCategory = 'featured';
}
$featuredItems = array_values(array_filter($items, static fn (array $item): bool => in_array($item['rarity'], ['legendary', 'mythic'], true) || $item['category'] === 'limited'));
$featuredItem = $featuredItems[0] ?? ($items[0] ?? null);
$visibleItems = $activeCategory === 'featured'
    ? $items
    : array_values(array_filter($items, static fn (array $item): bool => $item['category'] === $activeCategory || ($activeCategory === 'skins' && $item['item_type'] === 'skin') || ($activeCategory === 'boosts' && in_array($item['item_type'], ['offense', 'defense', 'tool', 'wings', 'potion'], true))));
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
  <section class="shop-featured rarity-<?php echo htmlspecialchars($featuredItem['rarity'] ?? 'common'); ?>">
    <div class="shop-featured-copy">
      <p class="kicker">Daily rotating shop</p>
      <h1>Slime Shop</h1>
      <p>Limited galaxy cosmetics, combat tools, potions, and seasonal slime gear refresh from the nebula vault.</p>
      <?php if ($message): ?><p class="form-success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
      <?php if ($error): ?><p class="form-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
      <div class="shop-countdown">
        <span>Featured reset</span>
        <strong>23:41:08</strong>
      </div>
    </div>
    <?php if ($featuredItem): ?>
      <div class="featured-showcase">
        <span class="rarity-chip rarity-<?php echo htmlspecialchars($featuredItem['rarity']); ?>"><?php echo htmlspecialchars($featuredItem['rarity']); ?></span>
        <?php if ($featuredItem['visual_type'] === 'image' && !empty($featuredItem['image_path'])): ?>
          <div class="slime-asset featured-asset <?php echo htmlspecialchars($featuredItem['animation_style']); ?>">
            <img src="<?php echo htmlspecialchars($featuredItem['image_path']); ?>" alt="<?php echo htmlspecialchars($featuredItem['name']); ?>">
          </div>
        <?php else: ?>
          <div class="hero-slime featured-slime <?php echo htmlspecialchars($featuredItem['tone']); ?> <?php echo htmlspecialchars($featuredItem['animation_style']); ?>" aria-hidden="true">
            <span class="eye eye-left"></span><span class="eye eye-right"></span><span class="smile"></span>
          </div>
        <?php endif; ?>
        <h2><?php echo htmlspecialchars($featuredItem['name']); ?></h2>
        <p><?php echo htmlspecialchars($featuredItem['power_effect'] ?: $featuredItem['description']); ?></p>
      </div>
    <?php endif; ?>
    <div class="shop-wallet premium-shop-wallet">
      <div><span>Coins</span><strong><?php echo (int) $save['coins']; ?></strong></div>
      <div><span>Gems</span><strong><?php echo (int) $save['gems']; ?></strong></div>
    </div>
  </section>

  <nav class="shop-tabs" aria-label="Shop categories">
    <?php foreach ([
        'featured' => 'Featured',
        'skins' => 'Skins',
        'boosts' => 'Boosts',
        'trails' => 'Trails',
        'bundles' => 'Bundles',
        'limited' => 'Limited',
        'seasonal' => 'Seasonal',
    ] as $category => $label): ?>
      <a class="<?php echo $activeCategory === $category ? 'active' : ''; ?>" href="shop.php?category=<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($label); ?></a>
    <?php endforeach; ?>
  </nav>

  <section class="shop-showcase">
    <?php foreach ($visibleItems as $index => $item): ?>
      <?php $ownedItem = $owned[(int) $item['id']] ?? null; ?>
      <?php $isOwned = $ownedItem !== null; ?>
      <?php $isEquipped = $isOwned && (int) ($ownedItem['equipped'] ?? 0) === 1; ?>
      <?php $coinPrice = effective_coin_price($item); ?>
      <article class="premium-item-card rarity-<?php echo htmlspecialchars($item['rarity']); ?> <?php echo htmlspecialchars($item['tone']); ?> <?php echo $isOwned ? 'owned' : ''; ?> <?php echo $isEquipped ? 'equipped' : ''; ?> <?php echo $index % 5 === 0 ? 'wide-card' : ''; ?>">
        <?php if ((int) $item['sale_percent'] > 0): ?><span class="sale-ribbon">-<?php echo (int) $item['sale_percent']; ?>%</span><?php endif; ?>
        <?php if (!empty($item['limited_until'])): ?><span class="limited-tag">Limited</span><?php endif; ?>
        <div class="item-glow"></div>
        <?php if ($item['visual_type'] === 'image' && !empty($item['image_path'])): ?>
          <div class="slime-asset <?php echo htmlspecialchars($item['animation_style']); ?>">
            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
          </div>
        <?php elseif ($item['item_type'] !== 'skin'): ?>
          <div class="equipment-art <?php echo htmlspecialchars($item['item_type']); ?> <?php echo htmlspecialchars($item['slug']); ?> <?php echo htmlspecialchars($item['animation_style']); ?>" aria-hidden="true">
            <span></span>
          </div>
        <?php else: ?>
          <div class="hero-slime shop-slime <?php echo htmlspecialchars($item['tone']); ?> <?php echo htmlspecialchars($item['animation_style']); ?>" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="item-copy">
          <div class="item-meta-row">
            <span class="card-tag"><?php echo htmlspecialchars($item['item_type']); ?></span>
            <span class="rarity-chip rarity-<?php echo htmlspecialchars($item['rarity']); ?>"><?php echo htmlspecialchars($item['rarity']); ?></span>
          </div>
          <h2><?php echo htmlspecialchars($item['name']); ?></h2>
          <p><?php echo htmlspecialchars($item['description']); ?></p>
          <div class="item-stats">
            <?php if ((int) $item['stat_attack'] !== 0): ?><span>ATK +<?php echo (int) $item['stat_attack']; ?></span><?php endif; ?>
            <?php if ((int) $item['stat_defense'] !== 0): ?><span>DEF +<?php echo (int) $item['stat_defense']; ?></span><?php endif; ?>
            <?php if ((int) $item['stat_jump'] !== 0): ?><span>JMP +<?php echo (int) $item['stat_jump']; ?></span><?php endif; ?>
            <?php if ($item['power_effect'] !== ''): ?><span><?php echo htmlspecialchars($item['power_effect']); ?></span><?php endif; ?>
          </div>
        </div>
        <div class="item-footer">
          <div>
            <strong><?php echo $isEquipped ? 'Equipped' : ($isOwned ? 'Owned' : 'C ' . $coinPrice); ?></strong>
            <span><?php echo $isOwned && (int) $item['stackable'] === 1 ? 'Qty ' . (int) $ownedItem['quantity'] : ((int) $item['price_gems'] > 0 ? 'G ' . (int) $item['price_gems'] : htmlspecialchars($item['category'])); ?></span>
          </div>
          <form action="actions/buy_item.php" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="item_id" value="<?php echo (int) $item['id']; ?>">
            <button class="small premium-buy" type="submit" <?php echo $isOwned && (int) $item['stackable'] !== 1 ? 'disabled' : ''; ?>>
              <?php echo $isEquipped ? 'Equipped' : ($isOwned && (int) $item['stackable'] !== 1 ? 'Owned' : 'Buy'); ?>
            </button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
