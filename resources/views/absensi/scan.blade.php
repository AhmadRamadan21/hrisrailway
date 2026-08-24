@extends('layouts.app')

@section('content')
<style>
  .scanner-card {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }
  
  .scanner-wrapper {
    position: relative;
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    background: #0f172a;
    border-radius: 16px;
    overflow: hidden;
    aspect-ratio: 4 / 3;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .camera-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  /* Overlay Scan Frame */
  .scan-frame {
    position: absolute;
    width: 230px;
    height: 230px;
    border: 2px dashed rgba(255, 255, 255, 0.4);
    border-radius: 16px;
    box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.5);
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  .scan-corner {
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: #2563eb;
    border-style: solid;
  }

  .corner-tl { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 12px; }
  .corner-tr { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 12px; }
  .corner-bl { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 12px; }
  .corner-br { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 12px; }

  /* Animasi Laser Scan */
  .scan-laser {
    position: absolute;
    width: 90%;
    height: 3px;
    background: linear-gradient(90deg, rgba(37, 99, 235, 0) 0%, #3b82f6 50%, rgba(37, 99, 235, 0) 100%);
    box-shadow: 0 0 12px #3b82f6;
    animation: scanAnim 2.2s infinite ease-in-out;
    display: none;
  }

  @keyframes scanAnim {
    0% { top: 10%; opacity: 0.3; }
    50% { top: 85%; opacity: 1; }
    100% { top: 10%; opacity: 0.3; }
  }

  .camera-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    gap: 12px;
    padding: 24px;
    text-align: center;
  }

  .camera-placeholder i {
    font-size: 54px;
    color: #475569;
  }
</style>

<div class="container-fluid p-0">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-qr-code-scan text-primary me-2"></i>Scan QR Absensi</h4>
      <p class="text-muted small mb-0">Arahkan kamera ke QR Code absensi untuk melakukan absensi karyawan.</p>
    </div>
  </div>

  @if($currentKaryawan)
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
      <div class="avatar-circle bg-primary text-white" style="width: 44px; height: 44px; font-size: 16px;">
        {{ strtoupper(substr($currentKaryawan->nama, 0, 2)) }}
      </div>
      <div>
        <h6 class="fw-bold mb-0">{{ $currentKaryawan->nama }}</h6>
        <small class="text-muted">{{ $currentKaryawan->divisi->nama_divisi ?? 'Divisi belum diatur' }} — {{ $currentKaryawan->jabatan->nama_jabatan ?? 'Jabatan belum diatur' }}</small>
      </div>
    </div>
  @else
    <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> Data karyawan untuk akun Anda belum dikaitkan. Silakan hubungi admin.
    </div>
  @endif

  {{-- BANNER STATUS PRESENSI HARI INI --}}
  @if(isset($todayAbsensi) && $todayAbsensi && $todayAbsensi->masuk)
    <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
      <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:46px; height:46px; font-size:22px;">
        <i class="bi bi-check-circle-fill"></i>
      </div>
      <div>
        <h6 class="fw-bold mb-1 text-dark">Anda Sudah Absen Hari Ini</h6>
        <p class="mb-0 text-muted small">
          Presensi tercatat pada jam <b>{{ \Carbon\Carbon::parse($todayAbsensi->masuk)->format('H:i') }} WIB</b> (Status: <b>{{ $todayAbsensi->status }}</b>).
        </p>
      </div>
    </div>
  @endif

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card scanner-card p-4">
        <!-- Badge Status Kamera, GPS, & Wi-Fi -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <h6 class="fw-bold m-0"><i class="bi bi-camera me-2"></i>Kamera Presensi</h6>
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="badge bg-primary text-white px-3 py-2 rounded-pill">
              <i class="bi bi-wifi me-1"></i> Wi-Fi Kantor: {{ env('OFFICE_WIFI_IP', '182.10.100.190') }}
            </span>
            <span id="gpsStatusBadge" class="badge bg-secondary text-white px-3 py-2 rounded-pill">
              <i class="bi bi-geo-alt me-1"></i> Memuat GPS...
            </span>
            <span id="cameraStatusBadge" class="badge bg-warning text-dark px-3 py-2 rounded-pill">
              <i class="bi bi-hourglass-split me-1"></i> Meminta Izin Kamera...
            </span>
          </div>
        </div>

        <!-- Frame Tempat Video Kamera -->
        <div class="scanner-wrapper mb-3">
          <div id="cameraPlaceholder" class="camera-placeholder">
            <i class="bi bi-camera-video-off"></i>
            <div>
              <p class="fw-bold mb-1">Kamera Belum Aktif</p>
              <small>Klik tombol "Mulai Kamera" atau izinkan browser mengakses kamera Anda.</small>
            </div>
          </div>

          <video id="webcamPreview" autoplay playsinline muted class="camera-video d-none"></video>

          <!-- Frame Visual QR Scanner -->
          <div class="scan-frame" id="scanFrame" style="display: none;">
            <div class="scan-corner corner-tl"></div>
            <div class="scan-corner corner-tr"></div>
            <div class="scan-corner corner-bl"></div>
            <div class="scan-corner corner-br"></div>
            <div class="scan-laser" id="scanLaser"></div>
          </div>
        </div>

        <!-- Opsi & Tombol Kontrol Kamera -->
        <div class="row g-2 mb-3">
          <div class="col-md-7">
            <select id="cameraSelect" class="form-select rounded-3" disabled>
              <option value="">Deteksi kamera...</option>
            </select>
          </div>
          <div class="col-md-5 d-flex gap-2">
            <button id="btnSwitchCamera" class="btn btn-outline-secondary w-100 rounded-3" disabled>
              <i class="bi bi-arrow-repeat"></i> Ganti
            </button>
          </div>
        </div>

        <div class="d-flex gap-2 mb-3">
          <button id="btnStartCamera" class="btn btn-primary flex-fill rounded-3 py-2 fw-semibold">
            <i class="bi bi-camera-video-fill me-1"></i> Mulai Kamera
          </button>
          <button id="btnStopCamera" class="btn btn-outline-danger flex-fill rounded-3 py-2 fw-semibold" disabled>
            <i class="bi bi-stop-circle me-1"></i> Hentikan Kamera
          </button>
        </div>

        <!-- Card Hasil Scan QR Terakhir -->
        <div id="lastScanContainer" class="card bg-light border-0 rounded-3 p-3 d-none">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold text-success small"><i class="bi bi-check-circle-fill me-1"></i> QR Code Terbaca</span>
            <small id="lastScanTime" class="text-muted"></small>
          </div>
          <div id="lastScanResult" class="font-monospace text-break fw-bold text-dark fs-6"></div>
        </div>
      </div>
    </div>

    <!-- Petunjuk / Instruksi -->
    <div class="col-lg-5">
      <div class="card scanner-card p-4 h-100">
        <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Panduan Absensi QR</h6>

        <div class="d-flex gap-3 mb-3">
          <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">1</div>
          <div>
            <h6 class="fw-bold mb-1">Izinkan Akses Kamera</h6>
            <p class="text-muted small mb-0">Klik "Allow / Izinkan" saat browser meminta akses ke kamera perangkat Anda.</p>
          </div>
        </div>

        <div class="d-flex gap-3 mb-3">
          <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">2</div>
          <div>
            <h6 class="fw-bold mb-1">Posisikan QR Code</h6>
            <p class="text-muted small mb-0">Posisikan QR Code absensi tepat berada di dalam bingkai (frame) kotak pemindai.</p>
          </div>
        </div>

        <div class="d-flex gap-3 mb-3">
          <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">3</div>
          <div>
            <h6 class="fw-bold mb-1">Verifikasi Otomatis</h6>
            <p class="text-muted small mb-0">Sistem akan secara otomatis membaca QR Code dan mencatat kehadiran Anda.</p>
          </div>
        </div>

        <div class="mt-auto p-3 rounded-3 bg-light border border-dashed">
          <div class="d-flex align-items-center gap-2 text-secondary small">
            <i class="bi bi-shield-check text-success fs-5"></i>
            <span>Akses kamera hanya dipergunakan untuk memindai absensi dan tidak direkam tanpa izin.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<!-- jsQR Library for real-time client-side QR Code decoding -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const webcamPreview = document.getElementById('webcamPreview');
  const cameraPlaceholder = document.getElementById('cameraPlaceholder');
  const scanFrame = document.getElementById('scanFrame');
  const scanLaser = document.getElementById('scanLaser');
  const statusBadge = document.getElementById('cameraStatusBadge');
  const btnStart = document.getElementById('btnStartCamera');
  const btnStop = document.getElementById('btnStopCamera');
  const btnSwitch = document.getElementById('btnSwitchCamera');
  const cameraSelect = document.getElementById('cameraSelect');

  const lastScanContainer = document.getElementById('lastScanContainer');
  const lastScanResult = document.getElementById('lastScanResult');
  const lastScanTime = document.getElementById('lastScanTime');

  const gpsStatusBadge = document.getElementById('gpsStatusBadge');

  let currentStream = null;
  let videoDevices = [];
  let currentDeviceIndex = 0;
  let animationFrameId = null;
  let isCooldown = false;
  let userLatitude = null;
  let userLongitude = null;

  // Meminta Lokasi GPS Perangkat / Browser
  function getGPSLocation() {
    if (!navigator.geolocation) {
      if (gpsStatusBadge) {
        gpsStatusBadge.className = 'badge bg-secondary text-white px-3 py-2 rounded-pill';
        gpsStatusBadge.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i> GPS Tidak Didukung';
      }
      return;
    }

    if (gpsStatusBadge) {
      gpsStatusBadge.className = 'badge bg-warning text-dark px-3 py-2 rounded-pill';
      gpsStatusBadge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memuat GPS...';
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        userLatitude = position.coords.latitude;
        userLongitude = position.coords.longitude;
        if (gpsStatusBadge) {
          gpsStatusBadge.className = 'badge bg-success text-white px-3 py-2 rounded-pill';
          gpsStatusBadge.innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i> GPS Aktif (${userLatitude.toFixed(3)}, ${userLongitude.toFixed(3)})`;
        }
      },
      (error) => {
        console.warn("Error GPS Geolocation:", error);
        if (gpsStatusBadge) {
          gpsStatusBadge.className = 'badge bg-danger text-white px-3 py-2 rounded-pill';
          gpsStatusBadge.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i> Izin GPS Ditolak';
        }
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  }

  // Panggil deteksi lokasi GPS saat halaman dimuat
  getGPSLocation();

  // Offscreen canvas untuk decoding frame video
  const canvasElement = document.createElement('canvas');
  const canvas = canvasElement.getContext('2d', { willReadFrequently: true });

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function playBeep() {
    try {
      const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(880, audioCtx.currentTime);
      gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start();
      osc.stop(audioCtx.currentTime + 0.15);
    } catch (e) {
      // AudioContext mungkin terblokir sebelum gesture pengguna
    }
  }

  function updateStatus(status, text, iconClass = '') {
    statusBadge.className = `badge bg-${status} text-white px-3 py-2 rounded-pill`;
    statusBadge.innerHTML = `<i class="${iconClass} me-1"></i> ${text}`;
  }

  // Loop pemindaian QR Code dari video stream kamera
  function scanTick() {
    if (!currentStream) return;

    if (!isCooldown && webcamPreview.readyState === webcamPreview.HAVE_ENOUGH_DATA) {
      canvasElement.height = webcamPreview.videoHeight;
      canvasElement.width = webcamPreview.videoWidth;
      canvas.drawImage(webcamPreview, 0, 0, canvasElement.width, canvasElement.height);

      const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
      const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: "dontInvert"
      });

      if (code && code.data && code.data.trim() !== '') {
        onQrCodeScanned(code.data);
      }
    }

    animationFrameId = requestAnimationFrame(scanTick);
  }

  function resetScanState() {
    scanFrame.style.borderColor = 'rgba(255, 255, 255, 0.4)';
    scanFrame.style.boxShadow = '0 0 0 9999px rgba(15, 23, 42, 0.5)';
    updateStatus('success', 'Kamera Aktif & Siap Memindai', 'bi bi-check-circle');
    setTimeout(() => {
      isCooldown = false;
    }, 1500);
  }

  function onQrCodeScanned(qrData) {
    isCooldown = true;
    playBeep();

    // Efek border hijau saat QR Code berhasil terdeteksi
    scanFrame.style.borderColor = '#22c55e';
    scanFrame.style.boxShadow = '0 0 0 9999px rgba(15, 23, 42, 0.5), 0 0 20px #22c55e';

    updateStatus('info', 'Menyimpan Presensi ke Database...', 'bi bi-hourglass-split');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Tangkap foto snapshot dari frame video
    let snapshotPhoto = null;
    try {
      if (webcamPreview.videoWidth > 0 && webcamPreview.videoHeight > 0) {
        canvasElement.height = webcamPreview.videoHeight;
        canvasElement.width = webcamPreview.videoWidth;
        canvas.drawImage(webcamPreview, 0, 0, canvasElement.width, canvasElement.height);
        snapshotPhoto = canvasElement.toDataURL('image/jpeg', 0.85);
      }
    } catch(e) {
      console.warn("Snapshot foto gagal:", e);
    }

    // Kirim AJAX ke backend Laravel bersamaan dengan koordinat GPS
    fetch("{{ route('scan-qr.process') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": csrfToken
      },
      body: JSON.stringify({
        qr_code: qrData,
        photo: snapshotPhoto,
        latitude: userLatitude,
        longitude: userLongitude
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const isMasuk = data.mode === 'masuk';
        const badgeClass = isMasuk ? 'bg-success' : 'bg-primary';
        const titleText = isMasuk ? 'Absensi MASUK Berhasil!' : 'Absensi KELUAR Berhasil!';

        if (lastScanContainer && lastScanResult) {
          lastScanContainer.classList.remove('d-none');
          lastScanResult.innerHTML = `<span class="badge ${badgeClass} me-2">${isMasuk ? 'MASUK' : 'KELUAR'}</span> ${escapeHtml(data.message)}`;
          lastScanTime.textContent = data.time || new Date().toLocaleTimeString('id-ID');
        }

        Swal.fire({
          icon: 'success',
          title: titleText,
          html: `
            <p class="mb-2 text-muted small">${escapeHtml(data.message)}</p>
            <div class="p-3 bg-light rounded-3 border mb-3">
              <div class="fw-bold fs-4 text-dark">${data.time || ''}</div>
              <small class="text-muted">${data.date || ''}</small>
            </div>
            <span class="badge bg-success-subtle text-success px-3 py-2 border border-success-subtle">
              <i class="bi bi-database-check me-1"></i>Tersimpan di Database Absensi
            </span>
          `,
          confirmButtonText: 'Selesai',
          confirmButtonColor: '#1E3A8A',
          customClass: { popup: 'rounded-4 shadow-lg border-0' }
        }).then(() => {
          resetScanState();
        });
      } else if (data.already_done) {
        Swal.fire({
          icon: 'info',
          title: 'Anda Sudah Absen',
          html: `
            <div class="p-3 bg-light rounded-3 border mb-3 text-start">
              <div class="fw-bold fs-6 text-dark mb-2">
                <i class="bi bi-check-circle-fill text-success me-2"></i>Presensi Hari Ini Sudah Lengkap
              </div>
              <small class="text-muted d-block mb-1">${escapeHtml(data.message)}</small>
            </div>
            <span class="badge bg-primary-subtle text-primary px-3 py-2 border border-primary-subtle">
              <i class="bi bi-info-circle me-1"></i>Tidak Perlu Memindai QR Code Lagi Hari Ini
            </span>
          `,
          confirmButtonText: 'Saya Mengerti',
          confirmButtonColor: '#1E3A8A',
          customClass: { popup: 'rounded-4 shadow-lg border-0' }
        }).then(() => {
          resetScanState();
        });
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'Gagal Mencatat Presensi',
          text: data.message || 'Terjadi kesalahan saat menyimpan presensi.',
          confirmButtonText: 'OK',
          confirmButtonColor: '#1E3A8A',
          customClass: { popup: 'rounded-4 shadow-lg border-0' }
        }).then(() => {
          resetScanState();
        });
      }
    })
    .catch(err => {
      console.error("AJAX error:", err);
      Swal.fire({
        icon: 'error',
        title: 'Gagal Menyimpan',
        text: 'Terjadi gangguan jaringan atau koneksi ke server.',
        confirmButtonText: 'Coba Lagi',
        confirmButtonColor: '#dc3545',
        customClass: { popup: 'rounded-4 shadow-lg border-0' }
      }).then(() => {
        resetScanState();
      });
    });
  }

  // Fungsi menghentikan stream kamera
  function stopCameraStream() {
    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
      animationFrameId = null;
    }
    if (currentStream) {
      currentStream.getTracks().forEach(track => track.stop());
      currentStream = null;
    }
    webcamPreview.srcObject = null;
    webcamPreview.classList.add('d-none');
    cameraPlaceholder.classList.remove('d-none');
    scanFrame.style.display = 'none';
    scanLaser.style.display = 'none';

    btnStart.disabled = false;
    btnStop.disabled = true;
    updateStatus('secondary', 'Kamera Nonaktif', 'bi bi-camera-video-off');
  }

  // Membaca daftar perangkat kamera
  async function getCameraDevices() {
    try {
      const devices = await navigator.mediaDevices.enumerateDevices();
      videoDevices = devices.filter(device => device.kind === 'videoinput');
      
      cameraSelect.innerHTML = '';
      if (videoDevices.length > 0) {
        videoDevices.forEach((device, index) => {
          const option = document.createElement('option');
          option.value = device.deviceId;
          option.text = device.label || `Kamera ${index + 1}`;
          cameraSelect.appendChild(option);
        });
        cameraSelect.disabled = false;
        btnSwitch.disabled = videoDevices.length <= 1;
      } else {
        const option = document.createElement('option');
        option.text = 'Tidak ada kamera ditemukan';
        cameraSelect.appendChild(option);
        cameraSelect.disabled = true;
        btnSwitch.disabled = true;
      }
    } catch (err) {
      console.warn('Gagal membaca daftar kamera:', err);
    }
  }

  // Inisialisasi permintaan izin & akses kamera browser
  async function startCamera(deviceId = null) {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      updateStatus('danger', 'Browser Tidak Mendukung Kamera', 'bi bi-x-circle');
      Swal.fire({
        icon: 'error',
        title: 'Kamera Tidak Didukung',
        text: 'Browser Anda tidak mendukung fitur akses kamera (MediaDevices API). Pastikan menggunakan koneksi HTTPS atau browser modern.'
      });
      return;
    }

    updateStatus('warning', 'Meminta Izin Kamera...', 'bi bi-hourglass-split');

    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
      animationFrameId = null;
    }

    if (currentStream) {
      currentStream.getTracks().forEach(track => track.stop());
    }

    const constraints = {
      video: deviceId 
        ? { deviceId: { exact: deviceId } }
        : { facingMode: { ideal: 'environment' } }
    };

    try {
      const stream = await navigator.mediaDevices.getUserMedia(constraints);
      currentStream = stream;
      webcamPreview.srcObject = stream;

      webcamPreview.classList.remove('d-none');
      cameraPlaceholder.classList.add('d-none');
      scanFrame.style.display = 'flex';
      scanLaser.style.display = 'block';

      btnStart.disabled = true;
      btnStop.disabled = false;
      updateStatus('success', 'Kamera Aktif & Siap Memindai', 'bi bi-check-circle');

      await getCameraDevices();

      const activeTrack = stream.getVideoTracks()[0];
      if (activeTrack) {
        const settings = activeTrack.getSettings();
        if (settings.deviceId) {
          cameraSelect.value = settings.deviceId;
          currentDeviceIndex = videoDevices.findIndex(d => d.deviceId === settings.deviceId);
        }
      }

      // Mulai loop real-time scanning QR code
      animationFrameId = requestAnimationFrame(scanTick);

    } catch (err) {
      console.error('Error akses kamera:', err);
      webcamPreview.classList.add('d-none');
      cameraPlaceholder.classList.remove('d-none');
      btnStart.disabled = false;
      btnStop.disabled = true;

      if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
        updateStatus('danger', 'Izin Akses Kamera Ditolak', 'bi bi-shield-x');
        Swal.fire({
          icon: 'warning',
          title: 'Izin Kamera Ditolak',
          text: 'Anda menolak izin akses kamera. Mohon izinkan akses kamera di pengaturan browser Anda lalu muat ulang halaman.'
        });
      } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
        updateStatus('danger', 'Kamera Tidak Ditemukan', 'bi bi-camera-video-off');
        Swal.fire({
          icon: 'error',
          title: 'Kamera Tidak Ditemukan',
          text: 'Tidak ada perangkat kamera yang terdeteksi di komputer / HP Anda.'
        });
      } else {
        updateStatus('danger', 'Gagal Membuka Kamera', 'bi bi-exclamation-triangle');
        Swal.fire({
          icon: 'error',
          title: 'Gagal Membuka Kamera',
          text: 'Terjadi kesalahan saat mencoba mengakses kamera: ' + (err.message || err.name)
        });
      }
    }
  }

  // Event Handlers
  btnStart.addEventListener('click', () => {
    const selectedDeviceId = cameraSelect.value || null;
    startCamera(selectedDeviceId);
  });

  btnStop.addEventListener('click', () => {
    stopCameraStream();
  });

  btnSwitch.addEventListener('click', () => {
    if (videoDevices.length > 1) {
      currentDeviceIndex = (currentDeviceIndex + 1) % videoDevices.length;
      const nextDevice = videoDevices[currentDeviceIndex];
      cameraSelect.value = nextDevice.deviceId;
      startCamera(nextDevice.deviceId);
    }
  });

  cameraSelect.addEventListener('change', (e) => {
    if (e.target.value) {
      startCamera(e.target.value);
    }
  });

  // Minta izin kamera & mulai scan secara otomatis saat halaman dimuat
  startCamera();
});
</script>
@endsection
