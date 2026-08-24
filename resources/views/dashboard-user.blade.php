@extends('layouts.app')

@section('content')
<div class="welcome-card">
  <div>
    <h4>Selamat datang, {{ auth()->user()->name }}!</h4>
    <p>Ini halaman beranda Anda untuk absensi, kegiatan, cuti, dan penggajian.</p>
  </div>
  <div class="text-md-end">
    <div class="welcome-date-label">Hari ini</div>
    <div class="welcome-date">{{ now()->translatedFormat('l, d F Y') }}</div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="panel h-100">
      <div class="panel-header">Profil Karyawan</div>
      <div class="panel-body">
        @if($karyawan)
          <div class="mb-3">
            <strong>Nama</strong>
            <div>{{ $karyawan->nama }}</div>
          </div>
          <div class="mb-3">
            <strong>Divisi</strong>
            <div>{{ optional($karyawan->divisi)->nama_divisi ?? '-' }}</div>
          </div>
          <div class="mb-3">
            <strong>Jabatan</strong>
            <div>{{ optional($karyawan->jabatan)->nama_jabatan ?? '-' }}</div>
          </div>
          <div class="mb-3">
            <strong>Email</strong>
            <div>{{ $karyawan->email }}</div>
          </div>
          <div class="mb-3">
            <strong>No Pegawai</strong>
            <div>{{ $karyawan->no_pegawai ?? '-' }}</div>
          </div>
        @else
          <div class="text-danger">Akun Anda belum terhubung ke data karyawan. Silakan hubungi admin.</div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="stat-card card-link p-3">
          <div class="text-muted">Status Absensi Hari Ini</div>
          <div class="h3">{{ $todayAbsensi?->status ?? 'Belum' }}</div>
          <div class="text-muted small">{{ $todayAbsensi?->tanggal?->translatedFormat('d M Y') ?? now()->translatedFormat('d M Y') }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card card-link p-3">
          <div class="text-muted">Total Absensi</div>
          <div class="h3">{{ $absensiCount }}</div>
          <div class="text-muted small">Sejak bergabung</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card card-link p-3">
          <div class="text-muted">Kegiatan Hari Ini</div>
          <div class="h3">{{ $kegiatanToday }}</div>
          <div class="text-muted small">Laporan kegiatan</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card card-link p-3">
          <div class="text-muted">Cuti Menunggu</div>
          <div class="h3">{{ $cutiPending }}</div>
          <div class="text-muted small">Perlu ditindaklanjuti</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card card-link p-3">
          <div class="text-muted">Cuti Disetujui</div>
          <div class="h3">{{ $cutiApproved }}</div>
          <div class="text-muted small">Bulan ini</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card card-link p-3">
          <div class="text-muted">Penggajian Bulan Ini</div>
          <div class="h3">Rp {{ number_format($currentPayroll->total ?? 0, 0, ',', '.') }}</div>
          <div class="text-muted small">Status: {{ $currentPayroll?->status ?? 'Belum' }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="panel h-100">
      <div class="panel-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-camera-fill text-primary me-2"></i>Absensi Cepat</span>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small">Radius 500m</span>
      </div>
      <div class="panel-body">
        <p class="text-muted small mb-3">Pilih salah satu metode presensi di bawah ini:</p>

        <div class="d-flex flex-column gap-3">
          <a href="{{ route('absensi.selfie') }}" class="card p-3 border-0 bg-success-subtle text-decoration-none shadow-sm rounded-4">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:22px;">
                  <i class="bi bi-camera-fill"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-success-emphasis">Absen Kamera Selfie</h6>
                  <small class="text-secondary">Wajib radius 500m dari PLN ULP BANDUNG TIMUR</small>
                </div>
              </div>
              <i class="bi bi-chevron-right text-success fs-5"></i>
            </div>
          </a>

          <a href="{{ route('scan-qr.index') }}" class="card p-3 border-0 bg-primary-subtle text-decoration-none shadow-sm rounded-4">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:22px;">
                  <i class="bi bi-qr-code-scan"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-primary-emphasis">Scan QR Code</h6>
                  <small class="text-secondary">Pindai kode QR absensi di lokasi</small>
                </div>
              </div>
              <i class="bi bi-chevron-right text-primary fs-5"></i>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="panel h-100">
      <div class="panel-header">Akses Cepat</div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-6">
            <a href="{{ route('absensi.index') }}" class="card p-3 stat-card text-decoration-none">
              <div class="text-muted">Absensi</div>
              <div class="h4">Buka</div>
            </a>
          </div>
          <div class="col-6">
            <a href="{{ route('kegiatan-harian.index') }}" class="card p-3 stat-card text-decoration-none">
              <div class="text-muted">Kegiatan Harian</div>
              <div class="h4">Buka</div>
            </a>
          </div>
          <div class="col-6">
            <a href="{{ route('cuti.index') }}" class="card p-3 stat-card text-decoration-none">
              <div class="text-muted">Cuti</div>
              <div class="h4">Buka</div>
            </a>
          </div>
          <div class="col-6">
            <a href="{{ route('penggajian.index') }}" class="card p-3 stat-card text-decoration-none">
              <div class="text-muted">Penggajian</div>
              <div class="h4">Buka</div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-header">Absensi Terakhir</div>
  <div class="panel-body">
    @if($latestAbsensi->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Masuk</th>
              <th>Keluar</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($latestAbsensi as $row)
              <tr>
                <td>{{ optional($row->tanggal)->translatedFormat('d M Y') }}</td>
                <td>{{ optional($row->masuk)->format('H:i') ?? '-' }}</td>
                <td>{{ optional($row->keluar)->format('H:i') ?? '-' }}</td>
                <td>{{ $row->status }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-muted">Belum ada data absensi terbaru.</div>
    @endif
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const video = document.getElementById('cameraPreview');
  const captureBtn = document.getElementById('capturePhoto');
  const startBtn = document.getElementById('startCamera');
  const resetBtn = document.getElementById('resetPhoto');
  const photoData = document.getElementById('photoData');
  const capturePreview = document.getElementById('capturePreview');
  const absenMasukBtn = document.getElementById('absenMasukBtn');
  let stream;

  const startCamera = async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      alert('Perangkat Anda tidak mendukung kamera browser.');
      return;
    }
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
      video.srcObject = stream;
    } catch (error) {
      console.error(error);
      alert('Tidak dapat mengakses kamera. Pastikan izin diberikan.');
    }
  };

  const capturePhoto = () => {
    if (!video.srcObject) {
      alert('Mulai kamera terlebih dahulu.');
      return;
    }
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg');
    capturePreview.src = dataUrl;
    capturePreview.style.display = 'block';
    photoData.value = dataUrl;
    if (absenMasukBtn) {
      absenMasukBtn.disabled = false;
    }
  };

  const resetPhoto = () => {
    capturePreview.style.display = 'none';
    capturePreview.src = '';
    photoData.value = '';
    if (absenMasukBtn) {
      absenMasukBtn.disabled = true;
    }
  };

  startBtn?.addEventListener('click', startCamera);
  captureBtn?.addEventListener('click', capturePhoto);
  resetBtn?.addEventListener('click', resetPhoto);
});
</script>
@endsection