<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Portal')</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body class="antialiased bg-gray-50 text-gray-900">
  <!-- HEADER (fixed to screen sides) -->
  <header class="fixed inset-x-0 top-0 h-16 bg-white shadow-lg z-50">
    <div class="h-full px-4 md:px-6 flex items-center justify-between">
      <a href="{{ url('/') }}" class="flex items-center gap-2">
        <img src="{{ asset('asset/assets/ioe_logo.png') }}" class="h-10 w-auto" alt="Logo">
        <span class="text-lg md:text-xl font-bold text-blue-900">IOE Purwanchal Campus</span>
      </a>

      <div class="flex items-center gap-2">
        <!-- NEW: Watch How to Apply button -->
        

        <!-- Mobile icon-only -->
        

        <a href="{{ route('employee.login') }}"
           class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition">
          Login
        </a>
      </div>
    </div>
  </header>

  <div class="min-h-screen flex flex-col">
    <main class="flex-grow flex items-center justify-center bg-gradient-to-r from-blue-50 to-slate-100 pt-16">
      <div class="text-center px-6">
        <h1 class="text-3xl sm:text-5xl font-bold text-blue-900 mb-4">
          Welcome to Smart Store Management Portal
        </h1>
        <p class="text-gray-600 text-lg sm:text-xl mb-6">
          Login To See Your Dashboard
        </p>
        <a href="{{ route('employee.login') }}"
           class="px-6 py-3 bg-blue-700 text-white rounded-xl shadow hover:bg-blue-800 transition">
          Get Started
        </a>
      </div>
    </main>

    <footer class="bg-gray-800 text-white text-center py-4">
      <p class="text-sm">© {{ date('Y') }} IOE Purwanchal Campus. All Rights Reserved.</p>
    </footer>
  </div>

  <!-- VIDEO MODAL -->
  <div id="videoDialog"
       class="fixed inset-0 z-[60] hidden"
       role="dialog" aria-modal="true" aria-label="How to Apply Video">
    <!-- Backdrop -->
    <div id="videoBackdrop" class="absolute inset-0 bg-black/70"></div>

    <!-- Modal content -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div
        class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-hidden ring-1 ring-black/5">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-play text-blue-700"></i>
            <h2 class="font-semibold text-slate-900">How to Apply</h2>
          </div>
          <button id="closeVideoBtn" class="p-2 rounded-md hover:bg-slate-100" aria-label="Close video">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <!-- Video -->
        <div class="bg-black">
          <video id="howtoVideo"
                 class="w-full h-auto max-h-[70vh] bg-black"
                 src="{{ asset('video/DEMO_V.mp4') }}"
                 controls
                 playsinline
                 preload="metadata">
              <!-- Optional alternate sources (uncomment when you add them)
              <source src="{{ asset('video/demo-1080p.mp4') }}" data-quality="1080p" type="video/mp4">
              <source src="{{ asset('video/demo-720p.mp4') }}" data-quality="720p" type="video/mp4">
              <source src="{{ asset('video/demo-480p.mp4') }}" data-quality="480p" type="video/mp4">
              -->
              Your browser does not support the video tag.
          </video>
        </div>

        <!-- Custom controls row -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3">
          <!-- Speed -->
          <div class="flex items-center gap-2">
            <label for="speedSel" class="text-sm text-slate-700">Speed</label>
            <select id="speedSel"
                    class="rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
              <option value="0.5">0.5×</option>
              <option value="0.75">0.75×</option>
              <option value="1" selected>1× (Normal)</option>
              <option value="1.25">1.25×</option>
              <option value="1.5">1.5×</option>
              <option value="1.75">1.75×</option>
              <option value="2">2×</option>
            </select>
          </div>

          <!-- Quality (auto-hides if only one source) -->
          <div id="qualityWrap" class="flex items-center gap-2">
            <label for="qualitySel" class="text-sm text-slate-700">Quality</label>
            <select id="qualitySel"
                    class="rounded-md border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
              <!-- Filled by JS from <source data-quality> tags -->
            </select>
          </div>

          <!-- Open fullscreen quickly -->
          <div class="flex items-center gap-2">
            <button id="fsBtn"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-slate-300 hover:bg-slate-50 text-sm">
              <i class="fa-solid fa-expand"></i>
              Fullscreen
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Script -->
  <script>
    const openBtn = document.getElementById('openVideoBtn');
    const openBtnSm = document.getElementById('openVideoBtnSm');
    const dialog = document.getElementById('videoDialog');
    const backdrop = document.getElementById('videoBackdrop');
    const closeBtn = document.getElementById('closeVideoBtn');
    const videoEl = document.getElementById('howtoVideo');
    const speedSel = document.getElementById('speedSel');
    const fsBtn = document.getElementById('fsBtn');
    const qualitySel = document.getElementById('qualitySel');
    const qualityWrap = document.getElementById('qualityWrap');

    function openDialog() {
      dialog.classList.remove('hidden');
      // Focus the dialog for accessibility
      dialog.focus({ preventScroll: true });
    }
    function closeDialog() {
      dialog.classList.add('hidden');
      // Pause when closing
      if (!videoEl.paused) videoEl.pause();
    }

    // Open handlers
    openBtn?.addEventListener('click', openDialog);
    openBtnSm?.addEventListener('click', openDialog);

    // Close handlers
    closeBtn.addEventListener('click', closeDialog);
    backdrop.addEventListener('click', closeDialog);
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !dialog.classList.contains('hidden')) {
        closeDialog();
      }
    });

    // Speed control
    speedSel.addEventListener('change', () => {
      videoEl.playbackRate = parseFloat(speedSel.value);
    });

    // Fullscreen
    fsBtn.addEventListener('click', async () => {
      try {
        if (videoEl.requestFullscreen) await videoEl.requestFullscreen();
        else if (videoEl.webkitRequestFullscreen) videoEl.webkitRequestFullscreen();
      } catch (e) {
        console.error(e);
      }
    });

    // Quality handling (optional multi-source setup)
    (function setupQuality() {
      const sources = Array.from(videoEl.querySelectorAll('source[data-quality]'));
      if (sources.length <= 1) {
        // Only one (or none) alternate quality
        qualityWrap.classList.add('hidden');
        return;
      }
      // Populate selector by descending quality order (e.g., 1080p, 720p…)
      const qualityList = sources
        .map(s => s.getAttribute('data-quality'))
        .filter(Boolean)
        .sort((a, b) => parseInt(b) - parseInt(a));

      qualitySel.innerHTML = qualityList
        .map((q, idx) => `<option value="${q}" ${idx === 0 ? 'selected' : ''}>${q}</option>`)
        .join('');

      // Switch quality by swapping src and preserving currentTime & paused state
      qualitySel.addEventListener('change', () => {
        const selected = qualitySel.value;
        const match = sources.find(s => s.getAttribute('data-quality') === selected);
        if (!match) return;

        const currentTime = videoEl.currentTime;
        const wasPaused = videoEl.paused;

        // Replace the main src with chosen source (for broad browser support)
        videoEl.src = match.getAttribute('src');
        videoEl.load();
        videoEl.currentTime = currentTime;
        if (!wasPaused) videoEl.play();
      });
    })();
  </script>
</body>
</html>
