<?php
require __DIR__ . '/api/config.php';
$user = require_admin();
$pageTitle = 'Admin Panel - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="content-wrap">
  <section class="simple-panel">
    <p class="kicker">Admin only</p>
    <h1>Admin Panel</h1>
    <p class="muted">Manage players, roles, shop content, and achievements from database-backed pages.</p>
  </section>
  <div class="card-grid">
    <a class="hub-tile" href="admin_users.php">
      <strong>Users</strong>
      <span>View players and change user/admin roles.</span>
    </a>
    <a class="hub-tile" href="admin_content.php">
      <strong>Game Content</strong>
      <span>Add shop items and achievements.</span>
    </a>
    <a class="hub-tile" href="setup.php">
      <strong>Database Status</strong>
      <span>Check MySQL tables and setup.</span>
    </a>
  </div>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

