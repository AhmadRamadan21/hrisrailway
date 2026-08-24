<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem HRIS - PT Inti Bumi Perkasa</title>

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    * {
      font-family: 'Plus Jakarta Sans', sans-serif;
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      background: #0f172a;
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

    /* Ambient Background Glows */
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

    /* Main Container & Card */
    .welcome-wrapper {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 500px;
      padding: 24px;
    }

    .welcome-card {
      background: rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 32px;
      padding: 48px 40px;
      text-align: center;
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.45),
                  inset 0 1px 0 rgba(255, 255, 255, 0.25);
      transition: all 0.4s ease;
    }

    .welcome-card:hover {
      transform: translateY(-4px);
      background: rgba(15, 23, 42, 0.52);
      box-shadow: 0 35px 70px rgba(0, 0, 0, 0.55),
                  inset 0 1px 0 rgba(255, 255, 255, 0.35);
      border-color: rgba(255, 255, 255, 0.3);
    }

    /* Icon Badge */
    .icon-badge {
      width: 90px;
      height: 90px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 24px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 14px;
      margin-bottom: 24px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.8);
      position: relative;
    }

    .icon-badge::after {
      content: '';
      position: absolute;
      inset: -5px;
      border-radius: 28px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      animation: borderPulse 2.5s infinite ease-in-out;
    }

    @keyframes borderPulse {
      0%, 100% { opacity: 0.3; transform: scale(1); }
      50% { opacity: 0.8; transform: scale(1.05); }
    }

    /* Typography */
    .welcome-title {
      font-size: 28px;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -0.5px;
      line-height: 1.2;
      margin-bottom: 4px;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .welcome-subtitle {
      font-size: 22px;
      font-weight: 800;
      color: #60a5fa;
      letter-spacing: -0.5px;
      margin-bottom: 16px;
      text-shadow: 0 2px 12px rgba(59, 130, 246, 0.4);
    }

    .welcome-desc {
      font-size: 15px;
      color: #e2e8f0;
      line-height: 1.6;
      margin-bottom: 36px;
      font-weight: 500;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
    }

    /* Primary Button */
    .btn-masuk {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 16px 24px;
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      color: #ffffff;
      font-size: 16px;
      font-weight: 700;
      border-radius: 18px;
      text-decoration: none;
      box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45);
      transition: all 0.25s ease;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn-masuk:hover {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: #ffffff;
      transform: translateY(-2px);
      box-shadow: 0 16px 36px rgba(37, 99, 235, 0.6);
    }

    .btn-masuk:active {
      transform: translateY(0);
    }

    .btn-masuk:active {
      transform: translateY(0);
    }

    /* Footer Text */
    .welcome-footer {
      margin-top: 24px;
      font-size: 12px;
      color: rgba(255, 255, 255, 0.7);
      text-align: center;
      font-weight: 500;
      letter-spacing: 0.3px;
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

  <div class="welcome-wrapper">
    <div class="welcome-card">
      
      <!-- Logo IBP Badge -->
      <div class="icon-badge">
        <img src="{{ asset('assets/logo.png') }}" alt="Logo IBP" style="width: 100%; height: 100%; object-fit: contain; border-radius: 14px;">
      </div>

      <!-- Header Titles -->
      <h2 class="welcome-title">Sistem HRIS</h2>
      <h3 class="welcome-subtitle">PT Inti Bumi Perkasa</h3>

      <!-- Description -->
      <p class="welcome-desc">
        Absensi, cuti, penggajian, dan voucher dalam satu tempat.
      </p>

      <!-- CTA Button -->
      <a href="{{ route('login') }}" class="btn-masuk">
        <i class="bi bi-box-arrow-in-right me-2 fs-5"></i> Masuk ke sistem
      </a>

    </div>

    <!-- Footer Text -->
    <div class="welcome-footer">
      PT Inti Bumi Perkasa &mdash; HRIS Internal
    </div>
  </div>

</body>
</html>
