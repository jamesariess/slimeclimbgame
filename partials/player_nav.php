<?php $navUser = current_user(); ?>
<header class="player-nav">
  <a class="nav-brand" href="menu.php">
    <span class="mini-slime"></span>
    <span>Slime Climb Galaxy</span>
  </a>
  <nav>
    <a href="home.php">Home</a>
    <a href="shop.php">Shop</a>
    <a href="inventory.php">Inventory</a>
    <a href="achievements.php">Achievements</a>
    <a href="settings.php">Settings</a>
    <?php if (is_admin($navUser)): ?>
      <a href="admin_panel.php">Admin</a>
    <?php endif; ?>
    <form action="actions/logout.php" method="post">
      <?php echo csrf_field(); ?>
      <button class="ghost small" type="submit">Logout</button>
    </form>
  </nav>
</header>
