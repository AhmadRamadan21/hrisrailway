@extends('layouts.app')

@section('content')
<style>
  .selfie-card {
    border: none;
    border-radius: 20px;
    background: #ffffff;
    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }
  
  .camera-container {
    position: relative;
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
    background: #0f172a;
    border-radius: 20px;
    overflow: hidden;
    aspect-ratio: 3 / 4;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
  }

  .camera-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1); /* Mirror selfie camera */
  }

  /* Oval Face Guide Overlay */
  .face-oval-guide {
    position: absolute;
    width: 220px;
    height: 280px;
    border: 3px dashed rgba(255, 255, 255, 0.6);
    border-radius: 50%;
    pointer-events: none;
    box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.55);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .face-oval-guide.active {
    border-color: #22c55e;
    box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.55), 0 0 25px rgba(34, 197, 94, 0.6);
  }

  .face-oval-guide.locked {
    border-color: #ef4444;
    box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.75), 0 0 25px rgba(239, 68, 68, 0.6);
  }

  .camera-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    gap: 14px;
    padding: 24px;
    text-align: center;
  }

  .camera-placeholder i {
    font-size: 60px;
    color: #475569;
  }

  .snapshot-preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1);
  }

  .locked-overlay-card {
    background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
    border: 1px solid #fecdd3;
    border-radius: 16px;
  }

  .distance-badge-pulse {
    animation: pulseGlow 2s infinite ease-in-out;
  }

  @keyframes pulseGlow {
    0% { transform: scale(1); }
    50% { transform: scale(1.03); }
    100% { transform: scale(1); }
  }
</style>

<div class="container-fluid p-0">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-camera-fill text-primary me-2"></i>Absensi Kamera Selfie</h4>
      <p class="text-muted small mb-0">Verifikasi kehadiran menggunakan foto selfie & radius lokasi GPS (Maksimal {{ $maxRadius }}m).</p>
    </div>
    <div>
      <a href="{{ route('scan-qr.index') }}" class="btn btn-outline-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
        <i class="bi bi-qr-code-scan"></i> Beralih ke Scan QR
      </a>
    </div>
  </div>

  @if($currentKaryawan)
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
      <div class="avatar-circle bg-primary text-white" style="width: 46px; height: 46px; font-size: 16px;">
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
        <h6 class="fw-bold mb-1 text-dark">Absensi Masuk Terdaftar Hari Ini</h6>
        <p class="mb-0 text-muted small">
          Tercatat jam <b>{{ \Carbon\Carbon::parse($todayAbsensi->masuk)->format('H:i') }} WIB</b> (Status: <b>{{ $todayAbsensi->status }}</b>) — Jarak: <b>{{ $todayAbsensi->distance ?? '-' }}m</b>
          @if($todayAbsensi->keluar)
            | Jam Keluar: <b>{{ \Carbon\Carbon::parse($todayAbsensi->keluar)->format('H:i') }} WIB</b>
          @endif
        </p>
      </div>
    </div>
  @endif

  <div class="row g-4">
    <!-- Kolom Kamera Selfie & Controls -->
    <div class="col-lg-7">
      <div class="card selfie-card p-4">
        <!-- Header Status Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <h6 class="fw-bold m-0"><i class="bi bi-person-bounding-box me-2 text-primary"></i>Verifikasi Wajah & GPS</h6>
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span id="gpsStatusBadge" class="badge bg-secondary text-white px-3 py-2 rounded-pill distance-badge-pulse">
              <i class="bi bi-geo-alt me-1"></i> Memuat GPS...
            </span>
            <span id="cameraStatusBadge" class="badge bg-warning text-dark px-3 py-2 rounded-pill">
              <i class="bi bi-hourglass-split me-1"></i> Menunggu GPS...
            </span>
          </div>
        </div>

        <!-- Warning Alert Jika Di Luar Radius 500m -->
        <div id="outOfRangeAlert" class="locked-overlay-card p-3 mb-3 d-none">
          <div class="d-flex align-items-start gap-3">
            <i class="bi bi-slash-circle-fill text-danger fs-3 flex-shrink-0"></i>
            <div>
              <h6 class="fw-bold text-danger mb-1">Akses Absensi Selfie Dikunci</h6>
              <p class="mb-0 text-secondary small" id="outOfRangeText">
                Anda berada di luar radius {{ $maxRadius }} meter dari titik lokasi PLN ULP BANDUNG TIMUR (-6.89905678, 107.64153303). Absensi selfie hanya dapat diakses dari area PLN ULP BANDUNG TIMUR.
              </p>
            </div>
          </div>
        </div>

        <!-- Camera Frame Viewport -->
        <div class="camera-container mb-3">
          <div id="cameraPlaceholder" class="camera-placeholder">
            <i class="bi bi-camera-video-off" id="placeholderIcon"></i>
            <div>
              <p class="fw-bold mb-1" id="placeholderTitle">Meminta Lokasi GPS...</p>
              <small id="placeholderSubtitle">Pastikan izin akses lokasi (GPS) diaktifkan di browser Anda.</small>
            </div>
          </div>

          <video id="selfiePreview" autoplay playsinline muted class="camera-video d-none"></video>
          <img id="snapshotPreview" class="snapshot-preview-img d-none" alt="Snapshot Selfie">

          <!-- Oval Frame Overlay -->
          <div class="face-oval-guide d-none" id="faceOvalGuide">
            <span class="badge bg-dark text-white px-3 py-1 rounded-pill opacity-75 small mb-auto mt-3">
              <i class="bi bi-person-square me-1"></i> Posisikan Wajah di Oval
            </span>
          </div>
        </div>

        <!-- Camera Actions & Submit Buttons -->
        <div class="d-flex flex-column gap-2">
          <div class="d-flex gap-2">
            <button id="btnCapturePhoto" class="btn btn-outline-primary flex-fill rounded-3 py-2 fw-semibold" disabled>
              <i class="bi bi-camera me-1"></i> Ambil Foto Snapshot
            </button>
            <button id="btnRetakePhoto" class="btn btn-outline-secondary rounded-3 py-2 fw-semibold d-none">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Ulangi Foto
            </button>
          </div>

          <div class="row g-2 mt-1">
            <div class="col-6">
              <button id="btnAbsenMasuk" class="btn btn-success w-100 rounded-3 py-2.5 fw-bold shadow-sm" disabled>
                <i class="bi bi-box-arrow-in-right me-1"></i> Absensi Masuk
              </button>
            </div>
            <div class="col-6">
              <button id="btnAbsenKeluar" class="btn btn-danger w-100 rounded-3 py-2.5 fw-bold shadow-sm" {{ (isset($todayAbsensi) && $todayAbsensi && $todayAbsensi->masuk && !$todayAbsensi->keluar) ? '' : 'disabled' }}>
                <i class="bi bi-box-arrow-left me-1"></i> Absensi Keluar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Informasi Lokasi Target & Ketentuan Radius -->
    <div class="col-lg-5">
      <div class="card selfie-card p-4 h-100">
        <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Ketentuan Absensi Selfie</h6>

        <div class="p-3 bg-light rounded-4 border mb-4">
          <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold">
            <i class="bi bi-building text-primary fs-5"></i> Titik Lokasi: PLN ULP BANDUNG TIMUR
          </div>
          <div class="font-monospace text-secondary small bg-white p-2 rounded border mb-2">
            Lat: {{ $officeLat }}<br>
            Lon: {{ $officeLon }}
          </div>
          <div class="d-flex justify-content-between align-items-center small">
            <span class="text-muted">Radius Maksimal:</span>
            <span class="badge bg-primary px-3 py-1 rounded-pill fw-semibold">{{ $maxRadius }} Meter</span>
          </div>
        </div>

        <div class="d-flex gap-3 mb-3">
          <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">1</div>
          <div>
            <h6 class="fw-bold mb-1">Verifikasi Radius GPS</h6>
            <p class="text-muted small mb-0">Browser akan mengecek posisi lokasi Anda secara otomatis. Kamera hanya aktif jika Anda berada dalam radius 500m dari PLN ULP BANDUNG TIMUR.</p>
          </div>
        </div>

        <div class="d-flex gap-3 mb-3">
          <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">2</div>
          <div>
            <h6 class="fw-bold mb-1">Ambil Foto Selfie</h6>
            <p class="text-muted small mb-0">Posisikan wajah tepat di dalam garis panduan oval, lalu tekan tombol "Ambil Foto Snapshot".</p>
          </div>
        </div>

        <div class="d-flex gap-3 mb-3">
          <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">3</div>
          <div>
            <h6 class="fw-bold mb-1">Kirim Absensi</h6>
            <p class="text-muted small mb-0">Klik tombol "Absensi Masuk" atau "Absensi Keluar" untuk menyimpan presensi ke sistem.</p>
          </div>
        </div>

        <div class="mt-auto p-3 rounded-4 bg-primary-subtle border border-primary-subtle">
          <div class="d-flex align-items-center gap-2 text-primary small fw-semibold">
            <i class="bi bi-shield-check fs-4"></i>
            <span>Sistem geofencing 500m memastikan keabsahan kehadiran karyawan secara akurat.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const officeLat = {{ $officeLat }};
  const officeLon = {{ $officeLon }};
  const maxRadius = {{ $maxRadius }};

  const selfiePreview = document.getElementById('selfiePreview');
  const snapshotPreview = document.getElementById('snapshotPreview');
  const cameraPlaceholder = document.getElementById('cameraPlaceholder');
  const faceOvalGuide = document.getElementById('faceOvalGuide');

  const placeholderIcon = document.getElementById('placeholderIcon');
  const placeholderTitle = document.getElementById('placeholderTitle');
  const placeholderSubtitle = document.getElementById('placeholderSubtitle');

  const gpsStatusBadge = document.getElementById('gpsStatusBadge');
  const cameraStatusBadge = document.getElementById('cameraStatusBadge');
  const outOfRangeAlert = document.getElementById('outOfRangeAlert');
  const outOfRangeText = document.getElementById('outOfRangeText');

  const btnCapturePhoto = document.getElementById('btnCapturePhoto');
  const btnRetakePhoto = document.getElementById('btnRetakePhoto');
  const btnAbsenMasuk = document.getElementById('btnAbsenMasuk');
  const btnAbsenKeluar = document.getElementById('btnAbsenKeluar');

  let userLatitude = null;
  let userLongitude = null;
  let currentDistance = null;
  let currentStream = null;
  let capturedPhotoData = null;

  // Rumus Haversine untuk kalkulasi jarak GPS (Meter)
  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radius Bumi dalam meter
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return Math.round(R * c);
  }

  function updateStatusBadges(inRange, distance) {
    if (inRange) {
      gpsStatusBadge.className = 'badge bg-success text-white px-3 py-2 rounded-pill distance-badge-pulse';
      gpsStatusBadge.innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i> Dalam Radius (${distance}m dari PLN ULP BANDUNG TIMUR)`;
      outOfRangeAlert.classList.add('d-none');
    } else {
      gpsStatusBadge.className = 'badge bg-danger text-white px-3 py-2 rounded-pill distance-badge-pulse';
      gpsStatusBadge.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Di Luar Radius (${distance}m)`;
      outOfRangeText.innerHTML = `Posisi Anda saat ini berjarak <b>${distance} meter</b> dari titik koordinat PLN ULP BANDUNG TIMUR (-6.89905678, 107.64153303). Maksimal radius yang diizinkan adalah <b>${maxRadius} meter</b>.`;
      outOfRangeAlert.classList.remove('d-none');
    }
  }

  // Permintaan Izin Geolocation GPS
  function initGeolocation() {
    if (!navigator.geolocation) {
      gpsStatusBadge.className = 'badge bg-secondary text-white px-3 py-2 rounded-pill';
      gpsStatusBadge.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i> GPS Tidak Didukung';
      placeholderTitle.textContent = 'GPS Tidak Didukung';
      placeholderSubtitle.textContent = 'Browser Anda tidak mendukung fitur lokasi Geolocation.';
      return;
    }

    gpsStatusBadge.className = 'badge bg-warning text-dark px-3 py-2 rounded-pill';
    gpsStatusBadge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memuat GPS...';

    navigator.geolocation.getCurrentPosition(
      (position) => {
        userLatitude = position.coords.latitude;
        userLongitude = position.coords.longitude;
        currentDistance = calculateDistance(userLatitude, userLongitude, officeLat, officeLon);

        const isWithinRadius = currentDistance <= maxRadius;
        updateStatusBadges(isWithinRadius, currentDistance);

        if (isWithinRadius) {
          // JIKA DALAM RADIUS 500M -> AKTIFKAN KAMERA SELFIE
          startSelfieCamera();
        } else {
          // JIKA DI LUAR RADIUS 500M -> KUNCI KAMERA & TOMBOL
          lockSelfieFeature('Di Luar Radius PLN ULP BANDUNG TIMUR', `Lokasi Anda berjarak ${currentDistance}m dari PLN ULP BANDUNG TIMUR (Maksimal ${maxRadius}m). Absensi selfie terkunci.`);
        }
      },
      (error) => {
        console.warn("Geolocation Error:", error);
        gpsStatusBadge.className = 'badge bg-danger text-white px-3 py-2 rounded-pill';
        gpsStatusBadge.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i> Akses GPS Ditolak';
        lockSelfieFeature('Akses GPS Ditolak', 'Izinkan akses lokasi GPS pada peranti/browser Anda untuk memverifikasi radius 500m kantor.');
      },
      { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
  }

  function lockSelfieFeature(title, subtitle) {
    stopSelfieCamera();
    cameraStatusBadge.className = 'badge bg-danger text-white px-3 py-2 rounded-pill';
    cameraStatusBadge.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Fitur Absen Terkunci';

    placeholderIcon.className = 'bi bi-slash-circle text-danger';
    placeholderTitle.textContent = title;
    placeholderSubtitle.textContent = subtitle;
    cameraPlaceholder.classList.remove('d-none');
    selfiePreview.classList.add('d-none');
    faceOvalGuide.classList.add('d-none');

    btnCapturePhoto.disabled = true;
    btnAbsenMasuk.disabled = true;
    btnAbsenKeluar.disabled = true;
  }

  // Inisialisasi Stream Kamera Selfie (Depan)
  async function startSelfieCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      cameraStatusBadge.className = 'badge bg-danger text-white px-3 py-2 rounded-pill';
      cameraStatusBadge.innerHTML = '<i class="bi bi-x-circle me-1"></i> Kamera Tidak Didukung';
      return;
    }

    cameraStatusBadge.className = 'badge bg-info text-white px-3 py-2 rounded-pill';
    cameraStatusBadge.innerHTML = '<i class="bi bi-camera me-1"></i> Membuka Kamera Selfie...';

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 960 } },
        audio: false
      });

      currentStream = stream;
      selfiePreview.srcObject = stream;

      selfiePreview.classList.remove('d-none');
      cameraPlaceholder.classList.add('d-none');
      faceOvalGuide.classList.remove('d-none');
      faceOvalGuide.classList.add('active');

      cameraStatusBadge.className = 'badge bg-success text-white px-3 py-2 rounded-pill';
      cameraStatusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i> Kamera Selfie Aktif';

      btnCapturePhoto.disabled = false;
      btnAbsenMasuk.disabled = capturedPhotoData ? false : true;

    } catch (err) {
      console.error('Selfie camera error:', err);
      cameraStatusBadge.className = 'badge bg-danger text-white px-3 py-2 rounded-pill';
      cameraStatusBadge.innerHTML = '<i class="bi bi-camera-video-off me-1"></i> Akses Kamera Ditolak';
      placeholderIcon.className = 'bi bi-camera-video-off text-warning';
      placeholderTitle.textContent = 'Akses Kamera Ditolak';
      placeholderSubtitle.textContent = 'Mohon berikan izin akses kamera di browser Anda lalu muat ulang halaman.';
    }
  }

  function stopSelfieCamera() {
    if (currentStream) {
      currentStream.getTracks().forEach(track => track.stop());
      currentStream = null;
    }
    selfiePreview.srcObject = null;
  }

  // Tangkap Foto Snapshot Selfie
  btnCapturePhoto.addEventListener('click', function() {
    if (!selfiePreview.srcObject) return;

    const canvas = document.createElement('canvas');
    canvas.width = selfiePreview.videoWidth || 640;
    canvas.height = selfiePreview.videoHeight || 480;
    const ctx = canvas.getContext('2d');

    // Flip horizontal untuk menyesuaikan mirror video selfie
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(selfiePreview, 0, 0, canvas.width, canvas.height);

    capturedPhotoData = canvas.toDataURL('image/jpeg', 0.88);

    snapshotPreview.src = capturedPhotoData;
    snapshotPreview.classList.remove('d-none');
    selfiePreview.classList.add('d-none');
    faceOvalGuide.classList.add('d-none');

    btnCapturePhoto.classList.add('d-none');
    btnRetakePhoto.classList.remove('d-none');

    // Aktifkan tombol absensi
    if (currentDistance !== null && currentDistance <= maxRadius) {
      btnAbsenMasuk.disabled = false;
      btnAbsenKeluar.disabled = false;
    }
  });

  // Ulangi Ambil Foto Snapshot
  btnRetakePhoto.addEventListener('click', function() {
    capturedPhotoData = null;
    snapshotPreview.classList.add('d-none');
    snapshotPreview.src = '';

    selfiePreview.classList.remove('d-none');
    faceOvalGuide.classList.remove('d-none');

    btnCapturePhoto.classList.remove('d-none');
    btnRetakePhoto.classList.add('d-none');

    btnAbsenMasuk.disabled = true;
  });

  // Kirim Absensi Masuk / Keluar via AJAX
  function sendAbsensi(endpointUrl, modeName) {
    if (!capturedPhotoData) {
      Swal.fire({
        icon: 'warning',
        title: 'Foto Selfie Diperlukan',
        text: 'Silakan ambil foto snapshot selfie terlebih dahulu sebelum menekan tombol absensi.',
        confirmButtonColor: '#1E3A8A'
      });
      return;
    }

    if (currentDistance === null || currentDistance > maxRadius) {
      Swal.fire({
        icon: 'error',
        title: 'Absensi Selfie Ditolak',
        text: `Anda berada di luar radius ${maxRadius} meter dari titik lokasi kantor (Jarak: ${currentDistance}m). Absensi tidak dapat diproses.`,
        confirmButtonColor: '#dc3545'
      });
      return;
    }

    Swal.fire({
      title: `Memproses Absensi ${modeName.toUpperCase()}...`,
      text: 'Mengirimkan data foto selfie & lokasi GPS ke server.',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(endpointUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        photo: capturedPhotoData,
        latitude: userLatitude,
        longitude: userLongitude,
        karyawan_id: "{{ optional($currentKaryawan)->id }}"
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: `Absensi ${modeName.toUpperCase()} Berhasil!`,
          html: `
            <p class="mb-2 text-muted small">${data.message}</p>
            <div class="p-3 bg-light rounded-3 border mb-3">
              <div class="fw-bold fs-4 text-dark">${data.time || ''}</div>
              <small class="text-muted">${data.date || ''}</small>
            </div>
            <span class="badge bg-success-subtle text-success px-3 py-2 border border-success-subtle">
              <i class="bi bi-geo-alt-fill me-1"></i> Jarak GPS: ${data.distance || currentDistance} Meter
            </span>
          `,
          confirmButtonText: 'Selesai',
          confirmButtonColor: '#1E3A8A',
          customClass: { popup: 'rounded-4 shadow-lg border-0' }
        }).then(() => {
          window.location.reload();
        });
      } else {
        Swal.fire({
          icon: data.location_error ? 'error' : 'warning',
          title: 'Gagal Absensi Selfie',
          text: data.message || 'Terjadi kesalahan saat memproses absensi.',
          confirmButtonColor: '#dc3545',
          customClass: { popup: 'rounded-4 shadow-lg border-0' }
        });
      }
    })
    .catch(err => {
      console.error('AJAX Absensi error:', err);
      Swal.fire({
        icon: 'error',
        title: 'Gangguan Jaringan',
        text: 'Tidak dapat terhubung ke server. Silakan periksa koneksi internet Anda.',
        confirmButtonColor: '#dc3545',
        customClass: { popup: 'rounded-4 shadow-lg border-0' }
      });
    });
  }

  btnAbsenMasuk.addEventListener('click', function() {
    sendAbsensi("{{ route('absensi.masuk') }}", 'Masuk');
  });

  btnAbsenKeluar.addEventListener('click', function() {
    sendAbsensi("{{ route('absensi.keluar') }}", 'Keluar');
  });

  // Panggil Inisialisasi Geolocation saat halaman dimuat
  initGeolocation();
});
</script>
@endsection
