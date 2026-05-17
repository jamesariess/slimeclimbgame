<?php
$pageTitle = $pageTitle ?? 'Slime Climb Galaxy';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/game-ui.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/auth-menu.css">
  <link rel="stylesheet" href="assets/css/home.css">
  <link rel="stylesheet" href="assets/css/shop-polish.css">
  <link rel="stylesheet" href="assets/css/achievements-polish.css">
</head>
<body class="<?php echo htmlspecialchars($bodyClass ?? ''); ?>">
  <div class="starfield" aria-hidden="true"></div>
  <div class="nebula-layer" aria-hidden="true"></div>
  <div class="page-loader" aria-hidden="true">
    <div class="loader-card">
      <div class="loader-orbit">
        <span></span><span></span><span></span>
        <div class="loader-slime"></div>
      </div>
      <strong>Preparing your climb...</strong>
      <p>Charging jump boots, checking rewards, and syncing your slime save.</p>
      <div class="loader-progress"><span></span></div>
    </div>
  </div>
