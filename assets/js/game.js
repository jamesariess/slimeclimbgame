const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");

const save = {
  level: 1,
  xp: 0,
  coins: 100,
  gems: 5,
  rank: "Rookie Comet",
  current_checkpoint: "Start",
  skins: ["Nebula Green"],
  achievements: [],
  progress: {},
  ...(window.SLIME_SAVE || {}),
};

const stageConfig = window.SLIME_STAGE_CONFIG;
const loadout = window.SLIME_LOADOUT || {};
const activeEffects = window.SLIME_EFFECTS || [];
const baseStats = window.SLIME_BASE_STATS || {};
const serverStats = window.SLIME_STATS || {};
const equippedSkin = loadout.skin || {};
const effectStartedAt = performance.now();
const keys = new Set();
const touch = new Set();
const attacks = [];

const slimePalette = {
  green: "#67ff93",
  cyan: "#45efff",
  pink: "#ff5fc8",
  gold: "#ffd166",
};

const world = buildWorld(stageConfig);
let equipmentStats = {
  attack: Number(serverStats.attack || baseStats.attack || 4),
  defense: Number(serverStats.defense || baseStats.defense || 0),
  jumpBoost: Number(serverStats.jump || baseStats.jump || 0),
};

let skinImage = null;
if (equippedSkin.visual_type === "image" && equippedSkin.image_path) {
  skinImage = new Image();
  skinImage.src = equippedSkin.image_path;
}

const player = {
  x: world.start.x,
  y: world.start.y,
  w: 44,
  h: 34,
  vx: 0,
  vy: 0,
  jumps: 0,
  grounded: false,
  hp: 100,
  facing: 1,
  invulnerableUntil: 0,
  attackCooldownUntil: 0,
  checkpoint: { ...world.start },
  bestY: world.start.y,
  loopCount: Number(save.progress?.climbLoops || 0),
};

applySavedCheckpoint();
resetPlayer();

function buildWorld(config) {
  const stairs = [];
  for (const stair of config.stairs || []) {
    for (let i = 0; i < stair.steps; i += 1) {
      stairs.push({
        x: stair.x + i * stair.stepW,
        y: stair.y - i * stair.rise,
        w: stair.stepW,
        h: stair.stepH,
        stair: true,
      });
    }
  }

  return {
    ...config.world,
    start: { ...config.world.start },
    platforms: [...config.platforms, ...stairs],
    checkpoints: config.checkpoints || [],
    sceneryZones: config.sceneryZones || [],
    coins: (config.coins || []).map((coin, index) => ({ ...coin, id: index, r: 12, collected: false })),
    enemies: (config.enemies || []).map((enemy) => ({
      ...enemy,
      maxHp: enemy.hp,
      alive: true,
      baseX: enemy.x,
      dir: enemy.dir || 1,
    })),
  };
}

function applySavedCheckpoint() {
  const checkpoint = world.checkpoints.find((point) => point.name === save.current_checkpoint);
  if (checkpoint) {
    player.checkpoint = { x: checkpoint.x, y: checkpoint.y, name: checkpoint.name };
  }
}

function resetPlayer() {
  player.x = player.checkpoint.x;
  player.y = player.checkpoint.y - player.h;
  player.vx = 0;
  player.vy = 0;
  player.jumps = 0;
  player.grounded = false;
}

function pressed(name) {
  return keys.has(name) || touch.has(name);
}

function rectsOverlap(a, b) {
  return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
}

function activeScenery() {
  return world.sceneryZones.find((zone) => player.y >= zone.fromY) || world.sceneryZones.at(-1);
}

window.addEventListener("keydown", (event) => {
  keys.add(event.key.toLowerCase());
  if (event.key === " " || event.key === "ArrowUp") event.preventDefault();
});

window.addEventListener("keyup", (event) => keys.delete(event.key.toLowerCase()));

document.querySelectorAll("[data-control]").forEach((button) => {
  const control = button.dataset.control;
  button.addEventListener("pointerdown", () => touch.add(control));
  button.addEventListener("pointerup", () => touch.delete(control));
  button.addEventListener("pointerleave", () => touch.delete(control));
  button.addEventListener("pointercancel", () => touch.delete(control));
});

let jumpWasPressed = false;
let attackWasPressed = false;
let saveQueuedUntil = 0;

function refreshTimedEffects() {
  const elapsedSeconds = Math.floor((performance.now() - effectStartedAt) / 1000);
  const nextStats = {
    attack: Number(baseStats.attack || 4),
    defense: Number(baseStats.defense || 0),
    jumpBoost: Number(baseStats.jump || 0),
  };
  for (const effect of activeEffects) {
    const remaining = Number(effect.seconds_remaining || 0) - elapsedSeconds;
    if (remaining <= 0) continue;
    const stacks = Math.max(1, Number(effect.stacks || 1));
    nextStats.attack += Number(effect.stat_attack || 0) * stacks;
    nextStats.defense += Number(effect.stat_defense || 0) * stacks;
    nextStats.jumpBoost += Number(effect.stat_jump || 0) * stacks;
  }
  equipmentStats = nextStats;
  document.getElementById("hudAtk").textContent = String(equipmentStats.attack);
  document.getElementById("hudDef").textContent = String(equipmentStats.defense);
}

function jump() {
  if (player.grounded || player.jumps < 2) {
    player.vy = -(15 + equipmentStats.jumpBoost);
    player.grounded = false;
    player.jumps += 1;
  }
}

function attack() {
  const now = performance.now();
  if (now < player.attackCooldownUntil) return;
  player.attackCooldownUntil = now + 420;
  const hasKnife = loadout.offense?.slug === "moon-fang-knife";
  attacks.push({
    x: player.x + player.w / 2 + player.facing * 28,
    y: player.y + player.h / 2,
    vx: hasKnife ? 0 : player.facing * 13,
    life: hasKnife ? 9 : 54,
    r: hasKnife ? 48 : 13,
    damage: Math.max(4, equipmentStats.attack),
    melee: hasKnife,
    facing: player.facing,
  });
}

async function saveProgress() {
  await fetch("api/progress.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": window.CSRF_TOKEN || "",
    },
    body: JSON.stringify({ action: "save", csrf_token: window.CSRF_TOKEN || "", ...save }),
  });
}

function queueSave(delay = 700) {
  saveQueuedUntil = performance.now() + delay;
}

async function flushQueuedSave() {
  if (!saveQueuedUntil || performance.now() < saveQueuedUntil) return;
  saveQueuedUntil = 0;
  await saveProgress();
}

function update() {
  refreshTimedEffects();
  const left = pressed("arrowleft") || pressed("a") || pressed("left");
  const right = pressed("arrowright") || pressed("d") || pressed("right");
  const wantsJump = pressed("arrowup") || pressed("w") || pressed(" ") || pressed("jump");
  const wantsAttack = pressed("f") || pressed("attack");

  if (wantsJump && !jumpWasPressed) jump();
  jumpWasPressed = wantsJump;
  if (wantsAttack && !attackWasPressed) attack();
  attackWasPressed = wantsAttack;

  player.vx += (Number(right) - Number(left)) * 0.82;
  if (right) player.facing = 1;
  if (left) player.facing = -1;
  player.vx *= player.grounded ? 0.82 : 0.88;
  player.vx = Math.max(-8, Math.min(8, player.vx));
  player.vy += world.gravity;
  player.vy = Math.min(player.vy, 18);
  player.grounded = false;

  moveHorizontal();
  moveVertical();
  updateBounds();
  updateEnemies();
  updateAttacks();
  collectItems();
  updateClimbDistance();
  checkTopLoop();
  flushQueuedSave();
}

function moveHorizontal() {
  player.x += player.vx;
  for (const block of world.platforms) {
    if (!rectsOverlap(player, block)) continue;
    if (player.vx > 0) player.x = block.x - player.w;
    if (player.vx < 0) player.x = block.x + block.w;
    player.vx = 0;
  }
}

function moveVertical() {
  player.y += player.vy;
  for (const block of world.platforms) {
    if (!rectsOverlap(player, block)) continue;
    if (player.vy > 0) {
      player.y = block.y - player.h;
      player.grounded = true;
      player.jumps = 0;
    } else {
      player.y = block.y + block.h;
    }
    player.vy = 0;
  }
}

function updateBounds() {
  player.x = Math.max(0, Math.min(world.width - player.w, player.x));
  if (player.y > world.height + 180) {
    damagePlayer(18);
    resetPlayer();
  }
}

function updateEnemies() {
  for (const enemy of world.enemies) {
    if (!enemy.alive) continue;
    enemy.x += enemy.dir * enemy.speed;
    if (Math.abs(enemy.x - enemy.baseX) > enemy.patrol) enemy.dir *= -1;
    if (rectsOverlap(player, enemy)) {
      damagePlayer(Math.max(1, enemy.damage - equipmentStats.defense));
      player.vx = -player.facing * 9;
      player.vy = -9;
    }
  }
}

function damagePlayer(amount) {
  const now = performance.now();
  if (now < player.invulnerableUntil) return;
  player.hp = Math.max(0, player.hp - amount);
  player.invulnerableUntil = now + 900;
  document.getElementById("hudHp").textContent = player.hp;
  if (player.hp <= 0) {
    player.hp = 100;
    document.getElementById("hudHp").textContent = player.hp;
    resetPlayer();
  }
}

function updateAttacks() {
  for (const shot of attacks) {
    shot.x += shot.vx;
    shot.life -= 1;
    for (const enemy of world.enemies) {
      if (!enemy.alive) continue;
      const hit = shot.x > enemy.x - shot.r && shot.x < enemy.x + enemy.w + shot.r && shot.y > enemy.y - shot.r && shot.y < enemy.y + enemy.h + shot.r;
      if (!hit) continue;
      enemy.hp -= shot.damage;
      shot.life = 0;
      if (enemy.hp <= 0) {
        enemy.alive = false;
        save.coins += 5;
        save.xp += 10;
        document.getElementById("hudCoins").textContent = save.coins;
        queueSave();
      }
    }
  }
  for (let i = attacks.length - 1; i >= 0; i -= 1) {
    if (attacks[i].life <= 0) attacks.splice(i, 1);
  }
}

function collectItems() {
  const centerX = player.x + player.w / 2;
  const centerY = player.y + player.h / 2;
  for (const coin of world.coins) {
    if (coin.collected || Math.hypot(centerX - coin.x, centerY - coin.y) >= 34) continue;
    coin.collected = true;
    save.coins += 1;
    save.xp += 5;
    save.achievements = Array.from(new Set([...save.achievements, "Coin Comet"]));
    document.getElementById("hudCoins").textContent = save.coins;
    queueSave();
  }

  for (const checkpoint of world.checkpoints) {
    if (Math.abs(player.x - checkpoint.x) >= 54 || Math.abs(player.y - checkpoint.y) >= 90 || player.checkpoint.name === checkpoint.name) continue;
    player.checkpoint = { x: checkpoint.x, y: checkpoint.y, name: checkpoint.name };
    save.current_checkpoint = checkpoint.name;
    save.level = Math.max(save.level, checkpoint.level || 1);
    save.achievements = Array.from(new Set([...save.achievements, "Checkpoint Chaser", "First Launch"]));
    document.getElementById("hudLevel").textContent = save.level;
    document.getElementById("hudCheckpoint").textContent = `Checkpoint: ${save.current_checkpoint}`;
    queueSave(100);
  }
}

function updateClimbDistance() {
  player.bestY = Math.min(player.bestY, player.y);
  const meters = Math.max(0, Math.round((world.start.y - player.bestY) / 10) + player.loopCount * Math.round(world.height / 10));
  document.getElementById("hudDistance").textContent = `${meters}m`;
}

function checkTopLoop() {
  if (player.y > world.finishY) return;
  player.loopCount += 1;
  save.progress = { ...(save.progress || {}), climbLoops: player.loopCount };
  save.current_checkpoint = world.start.name;
  save.level = Math.max(save.level, 4 + player.loopCount);
  save.achievements = Array.from(new Set([...save.achievements, "Galaxy Climber"]));
  player.checkpoint = { ...world.start };
  resetStageState();
  resetPlayer();
  document.getElementById("hudLevel").textContent = save.level;
  document.getElementById("hudCheckpoint").textContent = `Checkpoint: ${save.current_checkpoint}`;
  queueSave(100);
}

function resetStageState() {
  for (const enemy of world.enemies) {
    enemy.hp = enemy.maxHp;
    enemy.alive = true;
    enemy.x = enemy.baseX;
  }
  for (const coin of world.coins) coin.collected = false;
}

function camera() {
  return {
    x: Math.max(0, Math.min(world.width - canvas.width, player.x - canvas.width * 0.42)),
    y: Math.max(0, Math.min(world.height - canvas.height, player.y - canvas.height * 0.58)),
  };
}

function draw() {
  const cam = camera();
  drawBackground(cam);
  ctx.save();
  ctx.translate(-cam.x, -cam.y);
  drawScenery();
  drawBlocks();
  drawCheckpoints();
  drawCoins();
  drawEnemies();
  drawAttacks();
  drawPlayer();
  ctx.restore();
}

function drawBackground(cam) {
  const scene = activeScenery();
  const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
  gradient.addColorStop(0, scene?.top || "#07103b");
  gradient.addColorStop(1, scene?.bottom || "#19082c");
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  for (let i = 0; i < 120; i += 1) {
    const x = ((i * 173) % world.width) - cam.x * 0.25;
    const y = ((i * 241) % world.height) - cam.y * 0.18;
    ctx.fillStyle = i % 3 ? "rgba(255,255,255,.76)" : "rgba(69,239,255,.74)";
    ctx.beginPath();
    ctx.arc((x + world.width) % world.width, (y + world.height) % world.height, i % 4 === 0 ? 2 : 1, 0, Math.PI * 2);
    ctx.fill();
  }
}

function drawScenery() {
  const scene = activeScenery();
  if (!scene?.planet) return;
  const planet = scene.planet;
  ctx.fillStyle = planet.color;
  ctx.shadowColor = planet.color;
  ctx.shadowBlur = 28;
  ctx.beginPath();
  ctx.arc(planet.x, planet.y, planet.r, 0, Math.PI * 2);
  ctx.fill();
  ctx.shadowBlur = 0;
  ctx.strokeStyle = planet.ring;
  ctx.lineWidth = 8;
  ctx.beginPath();
  ctx.ellipse(planet.x, planet.y, planet.r * 1.55, planet.r * 0.34, -0.22, 0, Math.PI * 2);
  ctx.stroke();
}

function drawBlocks() {
  for (const block of world.platforms) {
    const grd = ctx.createLinearGradient(block.x, block.y, block.x, block.y + block.h);
    grd.addColorStop(0, block.stair ? "#9dfff0" : "#45efff");
    grd.addColorStop(1, block.stair ? "#39bdce" : "#1c6fbf");
    ctx.fillStyle = grd;
    ctx.shadowColor = "#45efff";
    ctx.shadowBlur = block.stair ? 8 : 16;
    roundRect(block.x, block.y, block.w, block.h, 10);
    ctx.fill();
    ctx.shadowBlur = 0;
    ctx.fillStyle = "rgba(255,255,255,.38)";
    roundRect(block.x + 8, block.y + 4, Math.max(12, block.w - 16), 4, 4);
    ctx.fill();
  }
}

function drawCheckpoints() {
  for (const checkpoint of world.checkpoints) {
    const active = checkpoint.name === player.checkpoint.name;
    ctx.fillStyle = active ? "#67ff93" : "#ffd166";
    ctx.fillRect(checkpoint.x, checkpoint.y - 86, 10, 86);
    ctx.beginPath();
    ctx.moveTo(checkpoint.x + 10, checkpoint.y - 86);
    ctx.lineTo(checkpoint.x + 84, checkpoint.y - 66);
    ctx.lineTo(checkpoint.x + 10, checkpoint.y - 46);
    ctx.closePath();
    ctx.fill();
  }
}

function drawCoins() {
  for (const coin of world.coins) {
    if (coin.collected) continue;
    ctx.fillStyle = "#ffd166";
    ctx.shadowColor = "#ffd166";
    ctx.shadowBlur = 16;
    ctx.beginPath();
    ctx.arc(coin.x, coin.y, coin.r, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;
  }
}

function drawEnemies() {
  for (const enemy of world.enemies) {
    if (!enemy.alive) continue;
    ctx.fillStyle = "#ff5fc8";
    ctx.shadowColor = "#ff5fc8";
    ctx.shadowBlur = 18;
    ctx.beginPath();
    ctx.ellipse(enemy.x + enemy.w / 2, enemy.y + enemy.h / 2, enemy.w / 2, enemy.h / 2, 0, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;
    ctx.fillStyle = "#07111c";
    ctx.beginPath();
    ctx.arc(enemy.x + enemy.w * 0.34, enemy.y + enemy.h * 0.38, 4, 0, Math.PI * 2);
    ctx.arc(enemy.x + enemy.w * 0.66, enemy.y + enemy.h * 0.38, 4, 0, Math.PI * 2);
    ctx.fill();
    drawEnemyHealthBar(enemy);
  }
}

function drawEnemyHealthBar(enemy) {
  const barW = Math.max(56, enemy.w + 18);
  const x = enemy.x + enemy.w / 2 - barW / 2;
  const y = enemy.y - 18;
  ctx.fillStyle = "rgba(5,8,26,.72)";
  roundRect(x, y, barW, 8, 999);
  ctx.fill();
  ctx.fillStyle = "#ff6b85";
  roundRect(x, y, barW * Math.max(0, enemy.hp / enemy.maxHp), 8, 999);
  ctx.fill();
  ctx.strokeStyle = "rgba(255,255,255,.28)";
  ctx.stroke();
}

function drawAttacks() {
  for (const shot of attacks) {
    ctx.fillStyle = shot.melee ? "rgba(223,252,255,.72)" : "#45efff";
    ctx.shadowColor = shot.melee ? "#dffcff" : "#45efff";
    ctx.shadowBlur = 20;
    if (shot.melee) {
      ctx.beginPath();
      ctx.arc(shot.x, shot.y, shot.r, shot.facing > 0 ? -0.8 : Math.PI - 0.8, shot.facing > 0 ? 0.8 : Math.PI + 0.8);
      ctx.lineWidth = 8;
      ctx.strokeStyle = "rgba(223,252,255,.86)";
      ctx.stroke();
    } else {
      ctx.beginPath();
      ctx.arc(shot.x, shot.y, shot.r, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.shadowBlur = 0;
  }
}

function drawPlayer() {
  ctx.save();
  ctx.translate(player.x + player.w / 2, player.y + player.h / 2);
  const slimeColor = slimePalette[equippedSkin.tone] || "#67ff93";
  drawEquippedAttachments(-1);
  if (skinImage?.complete && skinImage.naturalWidth > 0) {
    ctx.shadowColor = slimeColor;
    ctx.shadowBlur = 24;
    ctx.drawImage(skinImage, -player.w * 0.84, -player.h * 1.08, player.w * 1.68, player.h * 1.85);
    drawEquippedAttachments(1);
    ctx.restore();
    return;
  }
  ctx.fillStyle = slimeColor;
  ctx.shadowColor = slimeColor;
  ctx.shadowBlur = 24;
  ctx.beginPath();
  ctx.ellipse(0, 0, player.w / 2, player.h / 2, 0, 0, Math.PI * 2);
  ctx.fill();
  ctx.shadowBlur = 0;
  ctx.fillStyle = "#07111c";
  ctx.beginPath();
  ctx.arc(-8, -3, 4, 0, Math.PI * 2);
  ctx.arc(8, -3, 4, 0, Math.PI * 2);
  ctx.fill();
  ctx.strokeStyle = "#07111c";
  ctx.lineWidth = 3;
  ctx.beginPath();
  ctx.arc(0, 5, 10, 0.1, Math.PI - 0.1);
  ctx.stroke();
  drawEquippedAttachments(1);
  ctx.restore();
}

function drawEquippedAttachments(layer) {
  if (layer < 0 && loadout.wings) {
    ctx.fillStyle = "rgba(69,239,255,.72)";
    ctx.shadowColor = "#45efff";
    ctx.shadowBlur = 18;
    ctx.beginPath();
    ctx.ellipse(-28, -4, 30, 14, -0.45, 0, Math.PI * 2);
    ctx.ellipse(28, -4, 30, 14, 0.45, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;
  }
  if (layer > 0 && loadout.defense) {
    ctx.strokeStyle = "rgba(255,209,102,.8)";
    ctx.lineWidth = 4;
    ctx.shadowColor = "#ffd166";
    ctx.shadowBlur = 14;
    ctx.beginPath();
    ctx.arc(0, 0, 34, -0.5, Math.PI * 1.15);
    ctx.stroke();
    ctx.shadowBlur = 0;
  }
  if (layer > 0 && loadout.tool) {
    ctx.fillStyle = "#67ff93";
    ctx.shadowColor = "#67ff93";
    ctx.shadowBlur = 10;
    ctx.beginPath();
    ctx.ellipse(-13, 18, 11, 5, -0.15, 0, Math.PI * 2);
    ctx.ellipse(13, 18, 11, 5, 0.15, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = "#07111c";
    ctx.fillRect(-23, 20, 20, 4);
    ctx.fillRect(3, 20, 20, 4);
    ctx.shadowBlur = 0;
  }
  if (layer > 0 && loadout.offense) {
    ctx.strokeStyle = loadout.offense.slug === "moon-fang-knife" ? "#dffcff" : "#45efff";
    ctx.lineWidth = 5;
    ctx.beginPath();
    ctx.moveTo(player.facing * 16, -4);
    ctx.lineTo(player.facing * 40, -18);
    ctx.stroke();
  }
}

function roundRect(x, y, w, h, r) {
  const radius = Math.min(r, w / 2, h / 2);
  ctx.beginPath();
  ctx.moveTo(x + radius, y);
  ctx.lineTo(x + w - radius, y);
  ctx.quadraticCurveTo(x + w, y, x + w, y + radius);
  ctx.lineTo(x + w, y + h - radius);
  ctx.quadraticCurveTo(x + w, y + h, x + w - radius, y + h);
  ctx.lineTo(x + radius, y + h);
  ctx.quadraticCurveTo(x, y + h, x, y + h - radius);
  ctx.lineTo(x, y + radius);
  ctx.quadraticCurveTo(x, y, x + radius, y);
  ctx.closePath();
}

function resize() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight - document.querySelector(".game-hud").offsetHeight;
}

function loop() {
  update();
  draw();
  requestAnimationFrame(loop);
}

window.addEventListener("resize", resize);
resize();
save.achievements = Array.from(new Set([...save.achievements, "First Launch"]));
queueSave(100);
requestAnimationFrame(loop);
