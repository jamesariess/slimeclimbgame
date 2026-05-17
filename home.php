<?php
require __DIR__ . '/api/config.php';
$user = require_login();
$save = load_player_save((int) $user['id']);
$loadout = equipped_loadout((int) $user['id']);
$effects = active_effects((int) $user['id']);
$stats = total_loadout_stats($loadout, $effects);
$equippedSkin = $loadout['skin'] ?? null;
$pageTitle = 'Character Hub - Slime Climb Galaxy';
$bodyClass = 'hub-page';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/player_nav.php';
$xpPercent = min(100, $save['xp'] % 100);
$nextLevelXp = 100 - $xpPercent;
$currentCheckpoint = htmlspecialchars($save['current_checkpoint']);
$ownedSkins = count($save['skins']);
$earnedAchievements = count($save['achievements']);
?>
<main class="game-hub">
  <section class="profile-panel hub-glass">
    <div class="profile-topline">
      <div class="avatar-orb"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
      <div>
        <p class="kicker">Pilot profile</p>
        <h2><?php echo htmlspecialchars($user['username']); ?></h2>
        <p class="rank-label"><?php echo htmlspecialchars($save['rank']); ?></p>
      </div>
    </div>
    <div class="level-medallion">
      <span>Level</span>
      <strong><?php echo (int) $save['level']; ?></strong>
    </div>
    <div class="progress-block">
      <div class="progress-copy">
        <span>XP to next unlock</span>
        <strong><?php echo $nextLevelXp; ?> XP</strong>
      </div>
      <div class="xp-bar premium-xp"><span style="width: <?php echo $xpPercent; ?>%"></span></div>
      <p class="unlock-preview">Next: Comet Trail aura at Level <?php echo (int) $save['level'] + 1; ?></p>
    </div>
    <div class="wallet-grid premium-wallet">
      <div><span>Coins</span><strong><?php echo (int) $save['coins']; ?></strong></div>
      <div><span>Gems</span><strong><?php echo (int) $save['gems']; ?></strong></div>
    </div>
    <div class="mini-mission">
      <span>Daily Challenge</span>
      <strong>Collect 25 comet coins</strong>
      <small>Reward: 50 coins + streak charge</small>
    </div>
  </section>

  <section class="character-stage cinematic-stage">
    <div class="stage-light stage-light-a"></div>
    <div class="stage-light stage-light-b"></div>
    <div class="orbit-particles" aria-hidden="true">
      <span></span><span></span><span></span><span></span><span></span><span></span>
    </div>
    <div class="ring ring-one"></div>
    <div class="ring ring-two"></div>
    <div class="ring ring-three"></div>
    <?php if (($equippedSkin['visual_type'] ?? '') === 'image' && !empty($equippedSkin['image_path'])): ?>
      <div class="slime-asset loadout-asset <?php echo htmlspecialchars($equippedSkin['animation_style']); ?>">
        <img src="<?php echo htmlspecialchars($equippedSkin['image_path']); ?>" alt="<?php echo htmlspecialchars($equippedSkin['name']); ?>">
      </div>
    <?php else: ?>
      <div class="hero-slime character-slime <?php echo htmlspecialchars($equippedSkin['tone'] ?? 'green'); ?>" aria-hidden="true">
        <span class="eye eye-left"></span>
        <span class="eye eye-right"></span>
        <span class="smile"></span>
      </div>
    <?php endif; ?>
    <?php if (!empty($loadout['wings'])): ?><div class="slime-wings-attachment" aria-hidden="true"></div><?php endif; ?>
    <?php if (!empty($loadout['defense'])): ?><div class="slime-shield-attachment" aria-hidden="true"></div><?php endif; ?>
    <?php if (!empty($loadout['offense'])): ?><div class="slime-weapon-attachment" aria-hidden="true"></div><?php endif; ?>
    <div class="stage-caption">
      <p class="kicker">Active slime</p>
      <h1><?php echo htmlspecialchars($equippedSkin['name'] ?? 'Nebula Green'); ?></h1>
      <p>Gravity-ready climber tuned for <?php echo $currentCheckpoint; ?>.</p>
    </div>
    <a class="button primary play-now premium-play" href="game.php">
      <span>PLAY</span>
      <small>Continue from <?php echo $currentCheckpoint; ?></small>
    </a>
  </section>

  <section class="hub-actions activity-rail">
    <a class="activity-card featured-run" href="game.php">
      <span class="card-tag">Continue Run</span>
      <strong><?php echo $currentCheckpoint; ?></strong>
      <p>Gravity lanes unstable. Boss gate charging.</p>
      <small>Recommended power: Level <?php echo max(1, (int) $save['level']); ?></small>
    </a>
    <a class="activity-card reward-card" href="rewards.php">
      <span class="card-tag">Streak Reward</span>
      <strong>Daily Drop Ready</strong>
      <p>Claim coins, gems, and mission energy.</p>
    </a>
    <a class="activity-card mission-card" href="achievements.php">
      <span class="card-tag">Mission Tracker</span>
      <strong><?php echo $earnedAchievements; ?> achievements</strong>
      <p>Next objective: reach Orion Peak.</p>
    </a>
    <div class="activity-card status-card">
      <span class="card-tag">Slime Status</span>
      <strong>ATK <?php echo (int) $stats['attack']; ?> / DEF <?php echo (int) $stats['defense']; ?> / JMP +<?php echo (int) $stats['jump']; ?></strong>
      <p>Offense: <?php echo htmlspecialchars($loadout['offense']['name'] ?? 'Empty'); ?></p>
      <p>Defense: <?php echo htmlspecialchars($loadout['defense']['name'] ?? 'Empty'); ?></p>
      <p>Wings: <?php echo htmlspecialchars($loadout['wings']['name'] ?? 'Empty'); ?></p>
      <?php foreach ($effects as $effect): ?><small><?php echo htmlspecialchars($effect['name']); ?> x<?php echo (int) $effect['stacks']; ?></small><?php endforeach; ?>
    </div>
    <div class="rail-grid">
      <a class="activity-card compact-card" href="shop.php"><strong>Shop</strong><span>New skins</span></a>
      <a class="activity-card compact-card" href="inventory.php"><strong>Inventory</strong><span><?php echo $ownedSkins; ?> owned</span></a>
    </div>
    <a class="activity-card event-card muted-tile" href="leaderboard.php">
      <span class="card-tag">Season Event</span>
      <strong>Nebula Cup</strong>
      <p>Multiplayer races opening soon.</p>
    </a>
  </section>
</main>
<?php require __DIR__ . '/partials/foot.php'; ?>
