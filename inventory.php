<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$inventory = get_player_inventory((int) $user['id']);
$loadout = equipped_loadout((int) $user['id']);
$featured = $loadout['skin'] ?? ($inventory[0] ?? null);
$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$pageTitle = 'Inventory - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="game-subpage inventory-screen">
  <section class="subpage-hero inventory-hero">
    <div>
      <p class="kicker">Loadout vault</p>
      <h1>Inventory</h1>
      <p class="muted">Review owned skins and equipped cosmetics before launching your next climb.</p>
      <?php if ($message): ?><p class="form-success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
      <?php if ($error): ?><p class="form-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    </div>
    <div class="vault-count">
      <span>Owned Items</span>
      <strong><?php echo count($inventory); ?></strong>
    </div>
  </section>
  <section class="loadout-layout">
    <div class="loadout-preview">
      <div class="ring ring-one"></div>
      <?php if (($featured['visual_type'] ?? '') === 'image' && !empty($featured['image_path'])): ?>
        <div class="slime-asset loadout-asset <?php echo htmlspecialchars($featured['animation_style']); ?>">
          <img src="<?php echo htmlspecialchars($featured['image_path']); ?>" alt="<?php echo htmlspecialchars($featured['name']); ?>">
        </div>
      <?php else: ?>
        <div class="hero-slime character-slime" aria-hidden="true"></div>
      <?php endif; ?>
      <p class="kicker">Equipped</p>
      <h2><?php echo htmlspecialchars($featured['name'] ?? 'Nebula Green'); ?></h2>
      <div class="loadout-slots">
        <span>Offense: <?php echo htmlspecialchars($loadout['offense']['name'] ?? 'Empty'); ?></span>
        <span>Defense: <?php echo htmlspecialchars($loadout['defense']['name'] ?? 'Empty'); ?></span>
        <span>Tool: <?php echo htmlspecialchars($loadout['tool']['name'] ?? 'Empty'); ?></span>
      </div>
    </div>
    <div class="inventory-grid">
    <?php foreach ($inventory as $item): ?>
      <article class="inventory-card <?php echo htmlspecialchars($item['tone']); ?>">
        <?php if ($item['visual_type'] === 'image' && !empty($item['image_path'])): ?>
          <div class="slime-asset inventory-asset <?php echo htmlspecialchars($item['animation_style']); ?>">
            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
          </div>
        <?php else: ?>
          <div class="mini-slime"></div>
        <?php endif; ?>
        <div>
          <span class="card-tag"><?php echo htmlspecialchars($item['item_type']); ?></span>
          <h2><?php echo htmlspecialchars($item['name']); ?></h2>
        </div>
        <p><?php echo htmlspecialchars($item['description']); ?></p>
        <div class="item-stats">
          <?php if ((int) $item['stat_attack'] !== 0): ?><span>ATK +<?php echo (int) $item['stat_attack']; ?></span><?php endif; ?>
          <?php if ((int) $item['stat_defense'] !== 0): ?><span>DEF +<?php echo (int) $item['stat_defense']; ?></span><?php endif; ?>
          <?php if ($item['power_effect'] !== ''): ?><span><?php echo htmlspecialchars($item['power_effect']); ?></span><?php endif; ?>
          <?php if ((int) $item['quantity'] > 1): ?><span>Qty <?php echo (int) $item['quantity']; ?></span><?php endif; ?>
        </div>
        <div class="inventory-actions">
          <strong><?php echo (int) $item['equipped'] === 1 ? 'Equipped' : 'Owned'; ?></strong>
          <?php if (equipment_slot_for_type($item['item_type'])): ?>
            <form action="actions/equip_item.php" method="post">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="item_id" value="<?php echo (int) $item['id']; ?>">
              <button class="small primary" type="submit" <?php echo (int) $item['equipped'] === 1 ? 'disabled' : ''; ?>>Equip</button>
            </form>
          <?php else: ?>
            <span class="consumable-note">Consumable</span>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
