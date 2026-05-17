<?php
require __DIR__ . '/api/config.php';
$pageTitle = 'Setup Check - Slime Climb Galaxy';
$bodyClass = 'panel-page';
$status = [];

try {
    $pdo = db();
    $status[] = ['ok', 'Connected to XAMPP MySQL using database ' . DB_NAME . '.'];
    foreach (['users', 'player_saves', 'shop_items', 'player_inventory', 'achievements', 'player_achievements'] as $table) {
        $pdo->query('SELECT 1 FROM `' . $table . '` LIMIT 1');
        $status[] = ['ok', 'Table ready: ' . $table];
    }
} catch (Throwable $error) {
    $status[] = ['bad', $error->getMessage()];
}

require __DIR__ . '/partials/head.php';
?>
<main class="content-wrap">
  <section class="simple-panel">
    <p class="kicker">Database setup</p>
    <h1>Setup Check</h1>
    <p class="muted">Start Apache and MySQL in XAMPP, then refresh this page. The app will create the database and tables automatically when MySQL allows the default root connection.</p>
    <div class="status-list">
      <?php foreach ($status as [$type, $message]): ?>
        <div class="status-row <?php echo $type; ?>"><?php echo htmlspecialchars($message); ?></div>
      <?php endforeach; ?>
    </div>
    <a class="button primary" href="login.php">Back to Login</a>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>

