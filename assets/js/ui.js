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
