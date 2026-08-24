@extends('layouts.app')

@section('content')

{{-- STYLES KHUSUS SLIP GAJI --}}
<style>
  .panel {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    margin-bottom: 20px;
    overflow: hidden;
  }

  .panel-body {
    padding: 16px 20px;
  }

  /* Custom Blue Theme Palette */
  .btn-blue {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
  }
  .btn-blue:hover, .btn-blue:focus {
    background-color: #1d4ed8;
    border-color: #1d4ed8;
    color: #ffffff;
  }

  .btn-outline-blue {
    color: #2563eb;
    border-color: #cbd5e1;
    background-color: #f8fafc;
  }
  .btn-outline-blue:hover, .btn-outline-blue:focus {
    background-color: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
  }

  .text-blue-theme {
    color: #2563eb !important;
  }

  .slip-card-active {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #cbd5e1 !important;
  }

  /* Responsive Adjustments untuk Tampilan HP */
  @media (max-width: 767.98px) {
    .panel-body {
      padding: 12px 14px;
    }
    .table-responsive {
      font-size: 13px;
    }
  }
</style>

{{-- HEADER HALAMAN --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0" style="color: #0f172a;">Slip Gaji Saya</h4>
</div>

{{-- NOTIFIKASI / PERINGATAN KARYAWAN --}}
@if(!$currentKaryawan)
  <div class="alert alert-warning border-0 shadow-sm mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> Data karyawan untuk akun Anda tidak ditemukan. Silakan hubungi admin untuk mengaitkan akun dengan data karyawan.
  </div>
@endif

{{-- ===================== FILTER PERIODE ===================== --}}
<div class="panel">
  <div class="panel-body">
    <div class="fw-bold mb-2" style="font-size: 14px; color: #334155;">Pilih Periode Slip Gaji</div>
    <form method="GET" class="row g-2 align-items-end">
      
      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Bulan</label>
        <select name="bulan" class="form-select form-select-sm">
          @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
              {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
            </option>
          @endfor
        </select>
      </div>

      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Tahun</label>
        <select name="tahun" class="form-select form-select-sm">
          @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
            <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>

      <div class="col-12 col-md-auto d-flex gap-2 ms-auto">
        <button type="submit" class="btn btn-blue btn-sm px-3 fw-medium">
          <i class="bi bi-search me-1"></i> Tampilkan
        </button>
        <a href="{{ request()->url() }}" class="btn btn-outline-blue btn-sm px-3 fw-medium" title="Reset Filter">
          <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </a>
      </div>

    </form>
  </div>
</div>

{{-- ===================== KARTU SLIP GAJI TERPILIH ===================== --}}
@if($slipBulanIni)
  <div class="panel slip-card-active mb-4">
    <div class="panel-body p-4">
      
      {{-- Profil Karyawan & Status Badge --}}
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-blue-theme fw-bold"
               style="width: 48px; height: 48px; font-size: 16px;">
            {{ strtoupper(substr($currentKaryawan->nama ?? 'K', 0, 2)) }}
          </div>
          <div>
            <h5 class="fw-bold mb-0" style="color: #0f172a;">{{ $currentKaryawan->nama ?? 'Nama Karyawan' }}</h5>
            <small class="text-muted">
              {{ optional($currentKaryawan->jabatan)->nama_jabatan ?? '-' }} &bull; {{ optional($currentKaryawan->divisi)->nama_divisi ?? '-' }}
            </small>
          </div>
        </div>

        <div>
          @php
            $badgeStyle = match($slipBulanIni->status) {
              'Generated' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
              default     => 'bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25',
            };
          @endphp
          <span class="badge rounded-pill px-3 py-2 fw-semibold {{ $badgeStyle }}" style="font-size: 12px;">
            <i class="bi bi-check-circle-fill me-1"></i> {{ $slipBulanIni->status }}
          </span>
        </div>
      </div>

      {{-- Periode --}}
      <div class="text-muted small fw-medium mb-3">
        <i class="bi bi-calendar3 me-1"></i> Periode Penggajian: 
        <strong class="text-dark">{{ \Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F') }} {{ $year }}</strong>
      </div>

      {{-- Rincian Komponen Gaji --}}
      <div class="row g-3 p-3 bg-white rounded-3 border mb-3">
        <div class="col-12 col-md-4 border-end-md">
          <div class="text-muted small fw-medium mb-1">Gaji Pokok</div>
          <div class="h5 fw-bold mb-0 text-dark">Rp {{ number_format($slipBulanIni->gaji_pokok, 0, ',', '.') }}</div>
        </div>
        <div class="col-12 col-md-4 border-end-md">
          <div class="text-muted small fw-medium mb-1">Total Tunjangan</div>
          <div class="h5 fw-bold mb-0 text-success">+ Rp {{ number_format($slipBulanIni->tunjangan, 0, ',', '.') }}</div>
        </div>
        <div class="col-12 col-md-4">
          <div class="text-muted small fw-medium mb-1">Total Potongan</div>
          <div class="h5 fw-bold mb-0 text-danger">- Rp {{ number_format($slipBulanIni->potongan, 0, ',', '.') }}</div>
        </div>
      </div>

      {{-- Total Gaji Bersih & Download Button --}}
      <div class="d-flex justify-content-between align-items-center pt-2">
        <div>
          <span class="fw-bold text-dark d-block" style="font-size: 15px;">Gaji Bersih (Take Home Pay)</span>
          <small class="text-muted d-none d-sm-inline">Total pendapatan bersih yang diterima pada periode ini</small>
        </div>
        <div class="text-end">
          <div class="h3 fw-bold text-blue-theme mb-1">
            Rp {{ number_format($slipBulanIni->total, 0, ',', '.') }}
          </div>
          <a href="{{ route('penggajian.pdf', $slipBulanIni->id) }}" class="btn btn-sm btn-outline-danger fw-semibold" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
          </a>
        </div>
      </div>

    </div>
  </div>
@else
  <div class="panel mb-4">
    <div class="panel-body text-center text-muted py-5">
      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 60px; height: 60px;">
        <i class="bi bi-cash-stack fs-2 text-secondary"></i>
      </div>
      <h6 class="fw-bold text-dark mb-1">Slip Gaji Tidak Ditemukan</h6>
      <p class="small mb-0">Belum ada rincian slip gaji untuk periode <strong>{{ \Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F') }} {{ $year }}</strong>.</p>
    </div>
  </div>
@endif

{{-- ===================== TABEL RIWAYAT SLIP GAJI ===================== --}}
<div class="panel">
  <div class="panel-body p-0">
    <div class="px-3 pt-3 pb-2 border-bottom">
      <h6 class="fw-bold mb-0" style="color: #0f172a;">Riwayat Slip Gaji</h6>
    </div>
    
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Periode</th>
            <th>Gaji Pokok</th>
            <th>Tunjangan</th>
            <th>Potongan</th>
            <th>Gaji Bersih</th>
            <th class="text-end">Status</th>
            <th class="text-center pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $row)
            <tr>
              <td class="ps-3 fw-medium text-dark text-nowrap">
                <i class="bi bi-calendar2-check text-muted me-1"></i>
                {{ \Carbon\Carbon::createFromDate(null, $row->bulan, 1)->translatedFormat('F') }} {{ $row->tahun }}
              </td>
              <td class="text-nowrap">Rp {{ number_format($row->gaji_pokok, 0, ',', '.') }}</td>
              <td class="text-nowrap text-success">+Rp {{ number_format($row->tunjangan, 0, ',', '.') }}</td>
              <td class="text-nowrap text-danger">-Rp {{ number_format($row->potongan, 0, ',', '.') }}</td>
              <td class="text-nowrap fw-bold text-dark">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
              <td class="text-end text-nowrap">
                @php
                  $badgeStyle = match($row->status) {
                    'Generated' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                    default     => 'bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25',
                  };
                @endphp
                <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold {{ $badgeStyle }}">{{ $row->status }}</span>
              </td>
              <td class="text-center pe-3 text-nowrap">
                <a href="{{ route('penggajian.pdf', $row->id) }}" class="btn btn-sm btn-light border text-danger" title="Download PDF" target="_blank">
                  <i class="bi bi-file-earmark-pdf-fill"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat slip gaji yang tercatat.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- PAGINASI --}}
@if(method_exists($items, 'links'))
  <div class="d-flex justify-content-end mt-3">
    {{ $items->links() }}
  </div>
@endif

@endsection