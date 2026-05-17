<?php
require __DIR__ . '/api/config.php';
$user = require_admin();
$users = get_all_users();
$message = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$pageTitle = 'Admin Users - Slime Climb Galaxy';
$bodyClass = 'panel-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
?>
<main class="content-wrap">
  <section class="simple-panel">
    <p class="kicker">Admin only</p>
    <h1>User Management</h1>
    <p class="muted">Only admin accounts can view and edit roles. New accounts are users by default. The first registered account named admin becomes admin automatically.</p>
    <?php if ($message): ?><p class="form-success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="form-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
  </section>

  <section class="admin-table">
    <div class="admin-table-row admin-table-head">
      <span>User</span>
      <span>Email</span>
      <span>Role</span>
      <span>Progress</span>
      <span>Action</span>
    </div>
    <?php foreach ($users as $player): ?>
      <div class="admin-table-row">
        <span><?php echo htmlspecialchars($player['username']); ?></span>
        <span><?php echo htmlspecialchars($player['email']); ?></span>
        <span class="role-pill <?php echo htmlspecialchars($player['role']); ?>"><?php echo htmlspecialchars($player['role']); ?></span>
        <span>Lv <?php echo (int) $player['level']; ?> / <?php echo (int) $player['coins']; ?> coins / <?php echo (int) $player['gems']; ?> gems</span>
        <form action="actions/admin_users.php" method="post">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="user_id" value="<?php echo (int) $player['id']; ?>">
          <select name="role">
            <option value="user" <?php echo $player['role'] === 'user' ? 'selected' : ''; ?>>user</option>
            <option value="admin" <?php echo $player['role'] === 'admin' ? 'selected' : ''; ?>>admin</option>
          </select>
          <button class="small primary" type="submit">Save</button>
        </form>
      </div>
    <?php endforeach; ?>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

