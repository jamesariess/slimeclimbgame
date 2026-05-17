<?php
require __DIR__ . '/api/config.php';
$user = require_admin();
$items = get_shop_items();
$achievements = get_achievements((int) $user['id']);
$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$pageTitle = 'Admin Content - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="admin-layout">
  <section class="simple-panel">
    <p class="kicker">Database content</p>
    <h1>Admin Content</h1>
    <p class="muted">Add shop items and achievements here. They are saved to MySQL and show on player pages automatically.</p>
    <?php if ($message): ?><p class="form-success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="form-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
  </section>

  <section class="simple-panel">
    <h2>Add Shop Item</h2>
    <form class="form-stack compact-form" action="actions/admin_content.php" method="post" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="content_type" value="shop_item">
      <label><span>Name</span><input name="name" required></label>
      <label><span>Slug</span><input name="slug" placeholder="meteor-pink" required></label>
      <label><span>Description</span><input name="description" required></label>
      <label><span>Type</span><input name="item_type" value="skin" required></label>
      <label>
        <span>Category</span>
        <select name="category">
          <option value="skins">Skins</option>
          <option value="boosts">Boosts</option>
          <option value="trails">Trails</option>
          <option value="bundles">Bundles</option>
          <option value="limited">Limited</option>
          <option value="costumes">Costumes</option>
          <option value="seasonal">Seasonal</option>
        </select>
      </label>
      <label>
        <span>Rarity</span>
        <select name="rarity">
          <option value="common">Common</option>
          <option value="rare">Rare</option>
          <option value="epic">Epic</option>
          <option value="legendary">Legendary</option>
          <option value="mythic">Mythic</option>
        </select>
      </label>
      <label><span>Coin price</span><input name="price_coins" type="number" min="0" value="0"></label>
      <label><span>Gem price</span><input name="price_gems" type="number" min="0" value="0"></label>
      <label><span>Sale percent</span><input name="sale_percent" type="number" min="0" max="90" value="0"></label>
      <label><span>Limited until</span><input name="limited_until" type="datetime-local"></label>
      <label><span>Color tone</span><input name="tone" value="green" required></label>
      <label><span>Attack stat</span><input name="stat_attack" type="number" value="0"></label>
      <label><span>Defense stat</span><input name="stat_defense" type="number" value="0"></label>
      <label><span>Power effect</span><input name="power_effect" placeholder="Unlocks ranged slime shots."></label>
      <label class="toggle-row premium-toggle"><span>Stackable item</span><input name="stackable" type="checkbox" value="1"></label>
      <label>
        <span>Visual type</span>
        <select name="visual_type">
          <option value="css_slime">Generated animated slime</option>
          <option value="image">Uploaded image slime</option>
        </select>
      </label>
      <label><span>Upload slime image</span><input name="slime_image" type="file" accept="image/png,image/jpeg,image/webp,image/gif"></label>
      <label><span>Or image path</span><input name="image_path" placeholder="assets/images/shop-slimes/my-slime.png"></label>
      <label>
        <span>Animation</span>
        <select name="animation_style">
          <option value="float">Float</option>
          <option value="bounce">Bounce</option>
          <option value="pulse">Pulse glow</option>
        </select>
      </label>
      <button class="primary" type="submit">Save Shop Item</button>
    </form>
  </section>

  <section class="simple-panel">
    <h2>Add Achievement</h2>
    <form class="form-stack compact-form" action="actions/admin_content.php" method="post">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="content_type" value="achievement">
      <label><span>Name</span><input name="name" required></label>
      <label><span>Slug</span><input name="slug" placeholder="galaxy-climber" required></label>
      <label><span>Description</span><input name="description" required></label>
      <label><span>Coin reward</span><input name="reward_coins" type="number" min="0" value="0"></label>
      <button class="primary" type="submit">Save Achievement</button>
    </form>
  </section>

  <section class="simple-panel">
    <h2>Current Shop Items</h2>
    <div class="admin-list">
      <?php foreach ($items as $item): ?>
        <div><strong><?php echo htmlspecialchars($item['name']); ?></strong><span><?php echo htmlspecialchars($item['slug']); ?> - <?php echo (int) $item['price_coins']; ?> coins - <?php echo htmlspecialchars($item['visual_type']); ?></span></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="simple-panel">
    <h2>Current Achievements</h2>
    <div class="admin-list">
      <?php foreach ($achievements as $achievement): ?>
        <div><strong><?php echo htmlspecialchars($achievement['name']); ?></strong><span><?php echo htmlspecialchars($achievement['slug']); ?> - <?php echo (int) $achievement['reward_coins']; ?> reward coins</span></div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
