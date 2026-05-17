window.SlimeMusicPlayer = (() => {
  const track = new Audio("assets/audio/slime-galaxy-theme.wav");
  track.loop = true;
  track.volume = 0.28;
  let started = false;

  async function start() {
    if (started) return;
    started = true;
    try {
      await track.play();
    } catch (error) {
      started = false;
    }
  }

  function stop() {
    track.pause();
    track.currentTime = 0;
    started = false;
  }

  function setVolume(value) {
    track.volume = Math.max(0, Math.min(1, Number(value)));
  }

  return { start, stop, setVolume };
})();

