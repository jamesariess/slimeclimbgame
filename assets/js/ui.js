const AudioContextClass = window.AudioContext || window.webkitAudioContext;
let audio;
let musicTimer;

function tone(freq, duration = 0.08, type = "sine", gain = 0.035) {
  if (!AudioContextClass) return;
  audio ||= new AudioContextClass();
  const osc = audio.createOscillator();
  const volume = audio.createGain();
  osc.type = type;
  osc.frequency.value = freq;
  volume.gain.value = gain;
  osc.connect(volume).connect(audio.destination);
  osc.start();
  volume.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + duration);
  osc.stop(audio.currentTime + duration);
}

function startAmbientMusic() {
  if (window.SlimeMusicPlayer?.start) {
    window.SlimeMusicPlayer.start();
    return;
  }
  if (musicTimer) return;
  const notes = [196, 247, 294, 370, 330, 247];
  let index = 0;
  musicTimer = setInterval(() => {
    tone(notes[index++ % notes.length], 0.44, "triangle", 0.014);
  }, 540);
}

document.addEventListener("pointerdown", (event) => {
  if (event.target.closest("a, button, input")) {
    tone(620, 0.055, "square", 0.02);
    startAmbientMusic();
  }
});

const SlimeLoader = (() => {
  let fallbackTimer;

  function show(message = "Preparing your climb...") {
    const label = document.querySelector(".page-loader strong");
    if (label) label.textContent = message;
    clearTimeout(fallbackTimer);
    document.body.classList.add("is-loading-next-page");
  }

  function hide() {
    clearTimeout(fallbackTimer);
    document.body.classList.remove("is-loading-next-page");
  }

  function flash(message = "Working on it...") {
    show(message);
    fallbackTimer = setTimeout(hide, 700);
  }

  return { show, hide, flash };
})();

window.SlimeLoader = SlimeLoader;

function isInternalNavigation(link) {
  if (!link || link.target === "_blank" || link.hasAttribute("download")) return false;
  const href = link.getAttribute("href") || "";
  if (!href || href.startsWith("#") || href.startsWith("javascript:") || href.startsWith("mailto:")) return false;
  try {
    return new URL(link.href, window.location.href).origin === window.location.origin;
  } catch {
    return false;
  }
}

document.addEventListener("click", (event) => {
  const link = event.target.closest("a");
  if (isInternalNavigation(link)) {
    SlimeLoader.show("Opening galaxy screen...");
    return;
  }

  const button = event.target.closest("button");
  if (!button || button.disabled || button.matches("[data-control]")) return;
  if (button.type === "submit" && button.form) return;
  SlimeLoader.flash("Applying action...");
});

document.addEventListener("submit", (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) return;
  if (!form.checkValidity()) return;
  const submitter = event.submitter;
  if (submitter && submitter.disabled) return;
  SlimeLoader.show("Saving changes...");
  if (submitter) submitter.setAttribute("aria-busy", "true");
});

window.addEventListener("beforeunload", () => {
  SlimeLoader.show("Loading...");
});

window.addEventListener("pageshow", () => {
  SlimeLoader.hide();
});
