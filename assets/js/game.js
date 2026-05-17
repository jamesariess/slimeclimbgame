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
const loadout = window.SLIME_LOADOUT || {};
const equippedSkin = loadout.skin || {};
const slimePalette = {
  green: "#67ff93",
  cyan: "#45efff",
  pink: "#ff5fc8",
  gold: "#ffd166",
};
let skinImage = null;
if (equippedSkin.visual_type === "image" && equippedSkin.image_path) {
  skinImage = new Image();
  skinImage.src = equippedSkin.image_path;
}

const keys = new Set();
const touch = new Set();
const world = {
  width: 2600,
  height: 900,
  gravity: 0.72,
  platforms: [
    { x: 0, y: 820, w: 360, h: 30 },
    { x: 430, y: 720, w: 210, h: 24 },
    { x: 720, y: 620, w: 210, h: 24 },
    { x: 980, y: 500, w: 250, h: 24 },
    { x: 1320, y: 620, w: 220, h: 24 },
    { x: 1630, y: 470, w: 260, h: 24 },
    { x: 1980, y: 350, w: 300, h: 24 },
    { x: 2320, y: 250, w: 220, h: 24 },
  ],
  walls: [
    { x: 360, y: 590, w: 26, h: 230 },
    { x: 1260, y: 400, w: 26, h: 220 },
    { x: 1900, y: 240, w: 26, h: 230 },
  ],
  coins: [],
  checkpoints: [
    { x: 1040, y: 450, name: "Lunar Gate" },
    { x: 2040, y: 300, name: "Orion Peak" },
  ],
};

for (let i = 0; i < 22; i += 1) {
  world.coins.push({ x: 260 + i * 108, y: 680 - (i % 5) * 62, r: 12, collected: false });
}

const player = {
  x: 80,
  y: 760,
  w: 42,
  h: 34,
  vx: 0,
  vy: 0,
  jumps: 0,
  gravitySign: 1,
  grounded: false,
  checkpoint: { x: 80, y: 760, name: "Start" },
};

if (save.current_checkpoint === "Lunar Gate") player.checkpoint = { x: 1040, y: 450, name: "Lunar Gate" };
if (save.current_checkpoint === "Orion Peak") player.checkpoint = { x: 2040, y: 300, name: "Orion Peak" };
resetPlayer();

function resetPlayer() {
  player.x = player.checkpoint.x;
  player.y = player.checkpoint.y;
  player.vx = 0;
  player.vy = 0;
}

function pressed(name) {
  return keys.has(name) || touch.has(name);
}

function rectsOverlap(a, b) {
  return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
}

window.addEventListener("keydown", (event) => {
  keys.add(event.key.toLowerCase());
  if (event.key === " " || event.key === "ArrowUp") event.preventDefault();
  if (event.key.toLowerCase() === "g") flipGravity();
});

window.addEventListener("keyup", (event) => keys.delete(event.key.toLowerCase()));

document.querySelectorAll("[data-control]").forEach((button) => {
  const control = button.dataset.control;
  button.addEventListener("pointerdown", () => control === "gravity" ? flipGravity() : touch.add(control));
  button.addEventListener("pointerup", () => touch.delete(control));
  button.addEventListener("pointerleave", () => touch.delete(control));
});

let jumpWasPressed = false;

function jump() {
  if (player.grounded || player.jumps < 2) {
    player.vy = -15 * player.gravitySign;
    player.grounded = false;
    player.jumps += 1;
  }
}

function flipGravity() {
  player.gravitySign *= -1;
  player.vy = 0;
  save.achievements = Array.from(new Set([...save.achievements, "Gravity Master"]));
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

async function collectItems() {
  for (const coin of world.coins) {
    const centerX = player.x + player.w / 2;
    const centerY = player.y + player.h / 2;
    if (!coin.collected && Math.hypot(centerX - coin.x, centerY - coin.y) < 34) {
      coin.collected = true;
      save.coins += 1;
      save.xp += 5;
      save.achievements = Array.from(new Set([...save.achievements, "Coin Comet"]));
      document.getElementById("hudCoins").textContent = save.coins;
      await saveProgress();
    }
  }

  for (const checkpoint of world.checkpoints) {
    if (Math.abs(player.x - checkpoint.x) < 48 && Math.abs(player.y - checkpoint.y) < 80 && player.checkpoint.name !== checkpoint.name) {
      player.checkpoint = { x: checkpoint.x, y: checkpoint.y, name: checkpoint.name };
      save.current_checkpoint = checkpoint.name;
      save.level = Math.max(save.level, checkpoint.name === "Orion Peak" ? 3 : 2);
      save.achievements = Array.from(new Set([...save.achievements, "Checkpoint Chaser", "First Launch"]));
      document.getElementById("hudLevel").textContent = save.level;
      document.getElementById("hudCheckpoint").textContent = `Checkpoint: ${save.current_checkpoint}`;
      await saveProgress();
    }
  }
}

function update() {
  const left = pressed("arrowleft") || pressed("a") || pressed("left");
  const right = pressed("arrowright") || pressed("d") || pressed("right");
  const wantsJump = pressed("arrowup") || pressed("w") || pressed(" ") || pressed("jump");

  if (wantsJump && !jumpWasPressed) jump();
  jumpWasPressed = wantsJump;

  player.vx += (right - left) * 0.8;
  player.vx *= 0.84;
  player.vx = Math.max(-8, Math.min(8, player.vx));
  player.vy += world.gravity * player.gravitySign;
  player.grounded = false;

  player.x += player.vx;
  [...world.platforms, ...world.walls].forEach((block) => {
    if (rectsOverlap(player, block)) {
      if (player.vx > 0) player.x = block.x - player.w;
      if (player.vx < 0) player.x = block.x + block.w;
      if (Math.abs(player.vx) > 2 && wantsJump) {
        player.vy = -13 * player.gravitySign;
        player.jumps = 1;
      }
      player.vx = 0;
    }
  });

  player.y += player.vy;
  world.platforms.forEach((block) => {
    if (rectsOverlap(player, block)) {
      if (player.vy * player.gravitySign > 0) {
        player.y = player.gravitySign > 0 ? block.y - player.h : block.y + block.h;
        player.grounded = true;
        player.jumps = 0;
      } else {
        player.y = player.gravitySign > 0 ? block.y + block.h : block.y - player.h;
      }
      player.vy = 0;
    }
  });

  player.x = Math.max(0, Math.min(world.width - player.w, player.x));
  if (player.y > world.height + 180 || player.y < -220) resetPlayer();
  collectItems();
}

function draw() {
  const cameraX = Math.max(0, Math.min(world.width - canvas.width, player.x - canvas.width * 0.38));
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
  gradient.addColorStop(0, "#09103b");
  gradient.addColorStop(1, "#19082c");
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.save();
  ctx.translate(-cameraX, -140);

  for (let i = 0; i < 95; i += 1) {
    ctx.fillStyle = i % 3 ? "rgba(255,255,255,.82)" : "rgba(69,239,255,.82)";
    ctx.beginPath();
    ctx.arc((i * 173) % world.width, 70 + (i * 83) % world.height, i % 4 === 0 ? 2 : 1, 0, Math.PI * 2);
    ctx.fill();
  }

  ctx.fillStyle = "#5b62ff";
  ctx.beginPath();
  ctx.arc(520, 210, 62, 0, Math.PI * 2);
  ctx.fill();
  ctx.strokeStyle = "rgba(255,255,255,.35)";
  ctx.lineWidth = 7;
  ctx.beginPath();
  ctx.ellipse(520, 210, 100, 22, -0.3, 0, Math.PI * 2);
  ctx.stroke();

  [...world.platforms, ...world.walls].forEach((block) => {
    ctx.fillStyle = "#1fd6cb";
    ctx.shadowColor = "#45efff";
    ctx.shadowBlur = 18;
    ctx.fillRect(block.x, block.y, block.w, block.h);
    ctx.shadowBlur = 0;
    ctx.fillStyle = "rgba(255,255,255,.35)";
    ctx.fillRect(block.x, block.y, block.w, 5);
  });

  world.checkpoints.forEach((checkpoint) => {
    ctx.fillStyle = checkpoint.name === player.checkpoint.name ? "#67ff93" : "#ffd166";
    ctx.fillRect(checkpoint.x, checkpoint.y - 86, 10, 86);
    ctx.beginPath();
    ctx.moveTo(checkpoint.x + 10, checkpoint.y - 86);
    ctx.lineTo(checkpoint.x + 78, checkpoint.y - 66);
    ctx.lineTo(checkpoint.x + 10, checkpoint.y - 46);
    ctx.closePath();
    ctx.fill();
  });

  world.coins.forEach((coin) => {
    if (coin.collected) return;
    ctx.fillStyle = "#ffd166";
    ctx.shadowColor = "#ffd166";
    ctx.shadowBlur = 16;
    ctx.beginPath();
    ctx.arc(coin.x, coin.y, coin.r, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;
  });

  ctx.translate(player.x + player.w / 2, player.y + player.h / 2);
  ctx.scale(1, player.gravitySign);
  const slimeColor = slimePalette[equippedSkin.tone] || "#67ff93";
  if (skinImage?.complete && skinImage.naturalWidth > 0) {
    ctx.shadowColor = slimeColor;
    ctx.shadowBlur = 24;
    ctx.drawImage(skinImage, -player.w * 0.8, -player.h * 1.05, player.w * 1.6, player.h * 1.8);
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
  ctx.restore();
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
saveProgress();
requestAnimationFrame(loop);
