@extends('layouts.app')

@section('content')

{{-- CSS KHUSUS DASHBOARD --}}
<style>
  /* Fix link card agar tidak merusak warna/layout */
  .card-link-wrapper {
    text-decoration: none !important;
    color: inherit !important;
    display: block;
    height: 100%;
  }

  /* WELCOME CARD (Kotak Putih dengan Border Radius) */
  .welcome-card-box {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    border: 1px solid #e2e8f0;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
  }
  .welcome-card-box h4 {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
  }
  .welcome-card-box p {
    color: #64748b;
    font-size: 13px;
    margin: 0;
  }
  .welcome-date-label {
    color: #94a3b8;
    font-size: 11px;
  }
  .welcome-date {
    font-weight: 700;
    color: #0f172a;
    font-size: 13px;
  }

  /* STAT CARD DESIGN (Grid 2x2 Ringkas) */
  .stat-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    border: 1px solid #e2e8f0;
    height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
  }

  .stat-card-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }

  .stat-card-label {
    font-size: 11px;
    color: #475569;
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 2px;
  }

  .stat-card-value {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
  }

  .stat-card-caption {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 2px;
  }

  /* PANEL KOTAK UTAMA (Punya Background Putih & Border) */
  .dashboard-panel {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    height: 100%;
    overflow: hidden;
  }

  .dashboard-panel .panel-header {
    padding: 16px 20px;
    font-weight: 700;
    font-size: 15px;
    color: #0f172a;
    border-bottom: 1px solid #f1f5f9;
  }

  .dashboard-panel .panel-body {
    padding: 20px;
  }

  /* Kustomisasi Avatar Circle */
  .avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
  }

  /* Responsif Desktop */
  @media (min-width: 768px) {
    .stat-card {
      padding: 16px 18px;
      gap: 14px;
    }
    .stat-card-icon {
      width: 44px;
      height: 44px;
      font-size: 20px;
    }
    .stat-card-label {
      font-size: 12.5px;
    }
    .stat-card-value {
      font-size: 20px;
    }
  }

  /* STYLING CUSTOM POP-UP ULANG TAHUN MODERN */
  .birthday-popup-card {
    border-radius: 24px !important;
    padding: 32px 24px !important;
    background: #ffffff !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
  }
  .birthday-icon-box {
    width: 80px;
    height: 80px;
    background: #fef3c7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px auto;
    font-size: 38px;
    animation: bounce 1.2s infinite alternate ease-in-out;
  }
  .birthday-btn-custom {
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%) !important;
    color: #ffffff !important;
    border-radius: 12px !important;
    padding: 12px 28px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3) !important;
    transition: all 0.2s ease !important;
  }
  .birthday-btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4) !important;
  }
  @keyframes bounce {
    0% { transform: translateY(0); }
    100% { transform: translateY(-8px); }
  }
</style>

{{-- ================= WELCOME CARD ================= --}}
<div class="welcome-card-box">
  <div>
    <h4>Selamat datang, {{ auth()->user()->name }}!</h4>
    <p>Ini ringkasan aktivitas dan data Anda hari ini.</p>
  </div>
  <div class="text-start text-md-end">
    <div class="welcome-date-label">Hari ini</div>
    <div class="welcome-date">{{ now()->translatedFormat('l, d F Y') }}</div>
  </div>
</div>

{{-- ================= STAT CARDS - RINGKASAN UTAMA ================= --}}
<div class="row g-2 g-md-3 mb-3">

  {{-- Card 1: Jumlah Karyawan --}}
  <div class="col-6 col-md-3">
    <a href="{{ route('karyawan.index') }}" class="card-link-wrapper">
      <div class="stat-card">
        <div class="stat-card-icon" style="background:#e0e7ff;color:#4338ca;">
          <i class="bi bi-people-fill"></i>
        </div>
        <div>
          <div class="stat-card-label">Jumlah Karyawan</div>
          <div class="stat-card-value">{{ $totalVoucher }}</div>
          <div class="stat-card-caption">Active: {{ $totalVoucher }}</div>
        </div>
      </div>
    </a>
  </div>

  {{-- Card 2: Divisi --}}
  <div class="col-6 col-md-3">
    <a href="{{ route('divisi-jabatan.index') }}" class="card-link-wrapper">
      <div class="stat-card">
        <div class="stat-card-icon" style="background:#dcfce7;color:#16a34a;">
          <i class="bi bi-tags"></i>
        </div>
        <div>
          <div class="stat-card-label">Divisi</div>
          <div class="stat-card-value">{{ $totalPosBiaya }}</div>
          <div class="stat-card-caption">Departments</div>
        </div>
      </div>
    </a>
  </div>

  {{-- Card 3: Jabatan --}}
  <div class="col-6 col-md-3">
    <a href="{{ route('divisi-jabatan.index') }}" class="card-link-wrapper">
      <div class="stat-card">
        <div class="stat-card-icon" style="background:#fef9c3;color:#ca8a04;">
          <i class="bi bi-building"></i>
        </div>
        <div>
          <div class="stat-card-label">Jabatan</div>
          <div class="stat-card-value">{{ $totalBagian }}</div>
          <div class="stat-card-caption">Posisi Jabatan</div>
        </div>
      </div>
    </a>
  </div>

  {{-- Card 4: Payroll Bulan Ini --}}
  <div class="col-6 col-md-3">
    <a href="{{ route('penggajian.index') }}" class="card-link-wrapper">
      <div class="stat-card">
        <div class="stat-card-icon" style="background:#fee2e2;color:#dc2626;">
          <i class="bi bi-cash-coin"></i>
        </div>
        <div>
          <div class="stat-card-label">Payroll Bulan Ini</div>
          <div class="stat-card-value">Rp {{ number_format($totalRealisasiBulanIni ?? 0, 0, ',', '.') }}</div>
          <div class="stat-card-caption">Total gaji</div>
        </div>
      </div>
    </a>
  </div>

</div>

{{-- ================= RINGKASAN STATUS + GRAFIK ================= --}}
<div class="row g-3 mb-3">

  {{-- Data Kehadiran --}}
  <div class="col-12 col-lg-5">
    <div class="dashboard-panel">
      <div class="panel-header">Data Kehadiran</div>
      <div class="panel-body">

        @php
          $totalStatus = max(1, $countDraft + $countApproved + $countRejected);
          $pctDraft    = round($countDraft / $totalStatus * 100);
          $pctApproved = round($countApproved / $totalStatus * 100);
          $pctRejected = max(0, 100 - $pctDraft - $pctApproved);
        @endphp

        <div class="d-flex justify-content-between text-center mb-3">
          <div>
            <div class="fw-bold fs-5" style="color:#16a34a;">{{ $countDraft + $countApproved }}</div>
            <div class="text-muted small">Total Hadir</div>
          </div>
          <div>
            <div class="fw-bold fs-5" style="color:#d97706;">{{ $countApproved }}</div>
            <div class="text-muted small">Terlambat</div>
          </div>
          <div>
            <div class="fw-bold fs-5" style="color:#dc2626;">{{ $countRejected }}</div>
            <div class="text-muted small">Tidak Hadir</div>
          </div>
          <div>
            <div class="fw-bold fs-5" style="color:#2563eb;">{{ $pctDraft + $pctApproved }}%</div>
            <div class="text-muted small">Rate</div>
          </div>
        </div>

        <div class="progress-track d-flex overflow-hidden rounded" style="height:10px; background: #e2e8f0;">
          <div style="width:{{ $pctDraft }}%; background:#16a34a;"></div>
          <div style="width:{{ $pctApproved }}%; background:#d97706;"></div>
          <div style="width:{{ $pctRejected }}%; background:#dc2626;"></div>
        </div>

        <div class="d-flex justify-content-between text-muted small mt-2" style="font-size: 11px;">
          <span>Tepat Waktu ({{ $pctDraft }}%)</span>
          <span>Terlambat ({{ $pctApproved }}%)</span>
          <span>Tidak Hadir ({{ $pctRejected }}%)</span>
        </div>

      </div>
    </div>
  </div>

  {{-- Statistik Aktivitas --}}
  <div class="col-12 col-lg-7">
    <div class="dashboard-panel">
      <div class="panel-header">Statistik Aktivitas</div>
      <div class="panel-body">
        <canvas id="chartAktivitas" height="180"></canvas>
      </div>
    </div>
  </div>

</div>

{{-- ================= AKTIVITAS + ULANG TAHUN ================= --}}
<div class="row g-3">

  {{-- Aktivitas Terakhir --}}
  <div class="col-12 col-lg-6">
    <div class="dashboard-panel">
      <div class="panel-header">Aktivitas Terakhir Setiap Karyawan</div>
      <div class="panel-body">
        @forelse($recentVouchers as $voucher)
          @php
            $fullName  = optional($voucher->karyawan)->nama ?? ($voucher->user->name ?? 'Karyawan');
            $firstName = ucfirst(explode(' ', trim($fullName))[0]);
          @endphp
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar-circle" style="background:#16a34a; flex-shrink: 0;">
              <i class="bi bi-check-circle" style="font-size:14px;"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold" style="font-size:13px; color:#0f172a;">
                {{ $firstName }} melakukan absensi
              </div>
              <div class="text-muted small" style="font-size: 11px;">
                {{ $voucher->created_at->translatedFormat('M d, H:i') }}
              </div>
            </div>
          </div>
        @empty
          <p class="text-muted mb-0 small">Belum ada aktivitas.</p>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Ulang Tahun Mendatang --}}
  <div class="col-12 col-lg-6">
    <div class="dashboard-panel">
      <div class="panel-header">Ulang Tahun Mendatang</div>
      <div class="panel-body">
        @forelse($upcomingBirthdays as $b)
          @php
            $color = '#' . substr(md5($b->nama), 0, 6);
          @endphp
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar-circle" style="background:{{ $color }}; flex-shrink: 0;">
                {{ strtoupper(substr($b->nama, 0, 2)) }}
              </div>
              <div>
                <div class="fw-semibold" style="font-size:13px; color:#0f172a;">{{ strtoupper($b->nama) }}</div>
                <div class="text-muted small" style="font-size: 11px;">
                  @if($b->days_until == 0)
                    <span class="text-success fw-bold">Hari ini!</span>
                  @elseif($b->days_until == 1)
                    Besok
                  @else
                    {{ $b->next_birthday->translatedFormat('M d') }}
                  @endif
                  (Usia {{ $b->age_turning }} tahun)
                </div>
              </div>
            </div>
            <i class="bi bi-gift-fill" style="color:#d97706;"></i>
          </div>
        @empty
          <p class="text-muted mb-0 small">Belum ada ulang tahun mendatang.</p>
        @endforelse
      </div>
    </div>
  </div>

</div>

@endsection

@section('scripts')
{{-- CDN Library Chart.js, SweetAlert2, & Canvas-Confetti --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1. CHART AKTIVITAS (HARI INI)
  const ctx = document.getElementById('chartAktivitas');
  if (ctx) {
    const hadirVal = {{ (int)($totalHadirHariIni ?? 0) }};
    const cutiVal  = {{ (int)($totalCutiHariIni ?? 0) }};
    const alfaVal  = {{ (int)($totalAlfaHariIni ?? 0) }};

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Hadir', 'Cuti / Izin', 'Alfa'],
        datasets: [{
          label: 'Aktivitas',
          data: [hadirVal, cutiVal, alfaVal],
          backgroundColor: ['#16A34A', '#D97706', '#DC2626'],
          borderRadius: 6,
          maxBarThickness: 40
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
        }
      }
    });
  }

  // 2. POP-UP ANIMASI ULANG TAHUN MODERN
  @if(isset($isBirthday) && $isBirthday)
    // Animasi Hujan Kertas Warna-Warni Meluncur Bertahap
    var end = Date.now() + (3 * 1000);
    var colors = ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#3b82f6'];

    (function frame() {
      confetti({
        particleCount: 4,
        angle: 60,
        spread: 55,
        origin: { x: 0 },
        colors: colors
      });
      confetti({
        particleCount: 4,
        angle: 120,
        spread: 55,
        origin: { x: 1 },
        colors: colors
      });

      if (Date.now() < end) {
        requestAnimationFrame(frame);
      }
    }());

    // Pop-Up Modal UI Custom
    Swal.fire({
      html: `
        <div class="birthday-icon-box">🎂</div>
        <h3 style="font-weight: 800; color: #0f172a; margin-bottom: 8px; font-size: 22px;">
          Selamat Ulang Tahun!
        </h3>
        <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 0;">
          Happy Birthday, <b style="color: #4f46e5;">{{ auth()->user()->name }}</b>! 🎉<br>
          Semoga panjang umur, sehat selalu, dan makin sukses kariernya bersama perusahaan! ✨
        </p>
      `,
      showConfirmButton: true,
      confirmButtonText: 'Terima Kasih! ❤️',
      customClass: {
        popup: 'birthday-popup-card',
        confirmButton: 'birthday-btn-custom'
      },
      buttonsStyling: false,
      backdrop: `rgba(15, 23, 42, 0.4)`
    });
  @endif
});
</script>
@endsection