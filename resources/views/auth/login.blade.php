<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HRIS IBP - Login</title>
  
  <!-- Google Fonts: Plus Jakarta Sans & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    * { 
      font-family: 'Plus Jakarta Sans', sans-serif; 
      box-sizing: border-box; 
    }

    body {
      margin: 0;
      min-height: 100vh;
      background-color: #0f172a;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow-x: hidden;
    }

    /* Video Background Container */
    .video-bg-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      z-index: 0;
      pointer-events: none;
    }

    .video-bg-container iframe {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100vw;
      height: 56.25vw; /* 16:9 Ratio */
      min-height: 100vh;
      min-width: 177.77vh; /* 16:9 Ratio */
      transform: translate(-50%, -50%) scale(1.25);
      object-fit: cover;
    }

    .video-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.65) 0%, rgba(30, 58, 138, 0.75) 100%);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
    }

    /* Ambient Glow Effects */
    .bg-glow-1 {
      position: absolute;
      top: -10%;
      left: -10%;
      width: 450px;
      height: 450px;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.35) 0%, rgba(0, 0, 0, 0) 70%);
      border-radius: 50%;
      z-index: 1;
      animation: pulseGlow 8s infinite alternate ease-in-out;
    }

    .bg-glow-2 {
      position: absolute;
      bottom: -10%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, rgba(0, 0, 0, 0) 70%);
      border-radius: 50%;
      z-index: 1;
      animation: pulseGlow 10s infinite alternate ease-in-out;
    }

    @keyframes pulseGlow {
      0% { transform: scale(1) translate(0, 0); opacity: 0.7; }
      100% { transform: scale(1.15) translate(20px, -20px); opacity: 1; }
    }

    /* Login Main Card */
    .login-card {
      position: relative;
      z-index: 10;
      background: rgba(15, 23, 42, 0.5);
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 32px;
      width: 100%;
      max-width: 960px;
      box-shadow: 0 35px 70px rgba(0, 0, 0, 0.5),
                  inset 0 1px 0 rgba(255, 255, 255, 0.25);
      display: flex;
      overflow: hidden;
      transition: all 0.4s ease;
    }

    .login-card:hover {
      box-shadow: 0 40px 80px rgba(0, 0, 0, 0.6),
                  inset 0 1px 0 rgba(255, 255, 255, 0.35);
      border-color: rgba(255, 255, 255, 0.3);
    }

    /* Left Illustration Container */
    .login-illustration {
      width: 44%;
      background: rgba(255, 255, 255, 0.05);
      border-right: 1px solid rgba(255, 255, 255, 0.12);
      padding: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .logo-container {
      background: #ffffff;
      padding: 32px 28px;
      border-radius: 28px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      max-width: 320px;
      width: 100%;
      position: relative;
    }

    .logo-container img {
      width: 100%;
      height: auto;
      max-height: 240px;
      object-fit: contain;
    }

    /* Right Form Container */
    .login-form {
      width: 56%;
      padding: 52px 56px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-form h1 {
      font-size: 28px;
      font-weight: 800;
      color: #ffffff;
      margin: 0 0 4px;
      letter-spacing: -0.5px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .login-form h2 {
      font-size: 19px;
      font-weight: 700;
      color: #60a5fa;
      margin: 0 0 28px;
      text-shadow: 0 2px 10px rgba(59,130,246,0.3);
    }

    /* Input Custom Styles */
    .input-group-custom {
      display: flex;
      align-items: center;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 14px;
      padding: 0 18px;
      margin-bottom: 18px;
      background: rgba(15, 23, 42, 0.6);
      transition: all .25s ease;
    }

    .input-group-custom:focus-within {
      border-color: #60a5fa;
      box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.25);
      background: rgba(15, 23, 42, 0.8);
    }

    .input-group-custom i { 
      color: #94a3b8; 
      font-size: 18px; 
      transition: color .2s;
    }

    .input-group-custom:focus-within i {
      color: #60a5fa;
    }

    .input-group-custom input {
      border: none;
      outline: none;
      flex: 1;
      padding: 15px 12px;
      font-size: 15px;
      color: #ffffff;
      background: transparent;
      font-weight: 500;
    }

    .input-group-custom input::placeholder {
      color: #94a3b8;
    }

    .input-group-custom .toggle-eye { 
      cursor: pointer; 
    }

    .remember-row {
      display: flex;
      align-items: center;
      margin-bottom: 22px;
      font-size: 14px;
      color: #cbd5e1;
      font-weight: 500;
      cursor: pointer;
    }

    .remember-row input { 
      margin-right: 10px; 
      width: 17px; 
      height: 17px; 
      accent-color: #2563eb;
      cursor: pointer;
    }

    /* Slide to verify */
    .slide-verify {
      position: relative;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 50px;
      height: 52px;
      margin-bottom: 24px;
      overflow: hidden;
      user-select: none;
    }

    .slide-verify-fill {
      position: absolute;
      top: 0; left: 0; bottom: 0;
      width: 0;
      background: rgba(59, 130, 246, 0.35);
      border-radius: 50px;
      transition: width .05s;
    }

    .slide-verify-text {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #94a3b8;
      font-size: 14px;
      font-weight: 500;
      pointer-events: none;
      z-index: 2;
    }

    .slide-verify-handle {
      position: absolute;
      top: 4px; left: 4px;
      width: 44px; height: 44px;
      background: #ffffff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0,0,0,.3);
      cursor: grab;
      z-index: 3;
      color: #2563eb;
      font-size: 18px;
      touch-action: none;
    }

    .slide-verify.verified { 
      background: rgba(34, 197, 94, 0.2); 
      border-color: rgba(34, 197, 94, 0.5); 
    }

    .slide-verify.verified .slide-verify-text { 
      color: #4ade80; 
      font-weight: 700; 
    }

    .slide-verify.verified .slide-verify-handle { 
      color: #16a34a; 
    }

    /* Submit Button */
    .btn-signin {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 16px;
      padding: 15px;
      font-weight: 700;
      font-size: 16px;
      color: #ffffff;
      width: 100%;
      box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45);
      transition: all .25s ease;
      cursor: pointer;
    }

    .btn-signin:not(:disabled):hover { 
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      transform: translateY(-2px);
      box-shadow: 0 16px 36px rgba(37, 99, 235, 0.6);
    }

    .btn-signin:disabled { 
      opacity: .45; 
      cursor: not-allowed; 
      box-shadow: none;
    }

    .alert-error {
      background: rgba(239, 68, 68, 0.2);
      border: 1px solid rgba(239, 68, 68, 0.5);
      color: #fca5a5;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 14px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    @media (max-width: 768px) {
      .login-card { flex-direction: column; max-width: 440px; }
      .login-illustration { width: 100%; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.12); padding: 32px 24px; }
      .login-form { width: 100%; padding: 36px 28px; }
    }
  </style>
</head>
<body>

  <!-- Video Background (YouTube 38WmoGB_XuU) -->
  <div class="video-bg-container">
    <iframe 
      src="https://www.youtube.com/embed/38WmoGB_XuU?autoplay=1&mute=1&controls=0&loop=1&playlist=38WmoGB_XuU&playsinline=1&enablejsapi=1&showinfo=0&rel=0&iv_load_policy=3" 
      title="Background Video"
      frameborder="0" 
      allow="autoplay; encrypted-media; gyroscope" 
      allowfullscreen>
    </iframe>
    <div class="video-overlay"></div>
  </div>

  <!-- Ambient Glow Effects -->
  <div class="bg-glow-1"></div>
  <div class="bg-glow-2"></div>

  <div class="login-card">
    <div class="login-illustration">
      <div class="logo-container">
        <img src="{{ asset('assets/logo.png') }}" alt="Logo IBP">
      </div>
    </div>

    <div class="login-form">
      <h1>HRIS IBP</h1>
      <h2>Silakan Masuk Ke Akunmu</h2>

      @if ($errors->has('login'))
        <div class="alert-error">
          <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first('login') }}
        </div>
      @endif

      <form method="POST" action="{{ url('/login') }}" id="loginForm">
        @csrf

        <div class="input-group-custom">
          <i class="bi bi-person"></i>
          <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required autofocus>
        </div>

        <div class="input-group-custom">
          <i class="bi bi-lock"></i>
          <input type="password" name="password" id="passwordInput" placeholder="Password" required>
          <i class="bi bi-eye toggle-eye" id="toggleEye"></i>
        </div>

        <label class="remember-row">
          <input type="checkbox" name="remember">
          Remember me
        </label>

        <div class="slide-verify" id="slideVerify">
          <div class="slide-verify-fill" id="slideFill"></div>
          <div class="slide-verify-text" id="slideText">&rarr; Geser ke kanan untuk verifikasi</div>
          <div class="slide-verify-handle" id="slideHandle">
            <i class="bi bi-chevron-double-right"></i>
          </div>
        </div>

        <button type="submit" class="btn-signin" id="signInBtn" disabled>Sign in</button>
      </form>
    </div>
  </div>

  <script>
    // Toggle password visibility
    const passwordInput = document.getElementById('passwordInput');
    const toggleEye = document.getElementById('toggleEye');
    toggleEye.addEventListener('click', () => {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      toggleEye.classList.toggle('bi-eye');
      toggleEye.classList.toggle('bi-eye-slash');
    });

    // Slide to verify
    const track = document.getElementById('slideVerify');
    const handle = document.getElementById('slideHandle');
    const fill = document.getElementById('slideFill');
    const text = document.getElementById('slideText');
    const signInBtn = document.getElementById('signInBtn');

    let dragging = false;
    let verified = false;
    const handleSize = 44, padding = 4;

    function maxX() { return track.offsetWidth - handleSize - padding * 2; }

    function setPosition(x) {
      x = Math.max(0, Math.min(x, maxX()));
      handle.style.left = (x + padding) + 'px';
      fill.style.width = (x + handleSize + padding) + 'px';
      if (x >= maxX() - 2) {
        verified = true;
        track.classList.add('verified');
        text.textContent = 'Terverifikasi';
        signInBtn.disabled = false;
      }
    }

    function startDrag(clientX) {
      if (verified) return;
      dragging = true;
      handle.style.cursor = 'grabbing';
      track.dataset.startX = clientX;
      track.dataset.startLeft = parseInt(handle.style.left || padding);
    }

    function moveDrag(clientX) {
      if (!dragging) return;
      const delta = clientX - parseFloat(track.dataset.startX);
      setPosition(parseFloat(track.dataset.startLeft) + delta);
    }

    function endDrag() {
      if (!dragging) return;
      dragging = false;
      handle.style.cursor = 'grab';
      if (!verified) setPosition(0); // snap back kalau belum sampai ujung
    }

    handle.addEventListener('mousedown', e => startDrag(e.clientX));
    document.addEventListener('mousemove', e => moveDrag(e.clientX));
    document.addEventListener('mouseup', endDrag);

    handle.addEventListener('touchstart', e => startDrag(e.touches[0].clientX), { passive: true });
    document.addEventListener('touchmove', e => moveDrag(e.touches[0].clientX), { passive: true });
    document.addEventListener('touchend', endDrag);
  </script>
</body>
</html>