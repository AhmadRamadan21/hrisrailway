@extends('layouts.app')

@section('content')

{{-- STYLES KHUSUS PENGAJUAN CUTI --}}
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

  /* Custom Stat Card Styling */
  .stat-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: all .15s ease-in-out;
    cursor: pointer;
  }
  .stat-card:hover {
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    transform: translateY(-1px);
  }
  button.stat-card {
    appearance: none;
    -webkit-appearance: none;
    font: inherit;
    color: inherit;
  }
  button.stat-card:focus {
    outline: 2px solid #93c5fd;
    outline-offset: 2px;
  }

  /* Active Filter Card State */
  .stat-card.active-card {
    border-width: 2px !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
  }
  .stat-card.active-card:has(.text-warning) { border-color: #f59e0b !important; background: #fffbeb; }
  .stat-card.active-card:has(.text-success) { border-color: #22c55e !important; background: #f0fdf4; }
  .stat-card.active-card:has(.text-danger)  { border-color: #ef4444 !important; background: #fef2f2; }
  .stat-card.active-card:not(:has(.text-warning)):not(:has(.text-success)):not(:has(.text-danger)) {
    border-color: #2563eb !important; background: #eff6ff;
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

  /* Responsive Adjustments untuk Tampilan HP */
  @media (max-width: 767.98px) {
    .panel-body {
      padding: 12px 14px;
    }
    .table-responsive {
      font-size: 13px;
    }
    .btn-action-sm {
      padding: 3px 8px;
      font-size: 11px;
    }
  }
</style>

{{-- HEADER & TOMBOL AJUKAN CUTI --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0" style="color: #0f172a;">Pengajuan Cuti</h4>
  <button class="btn btn-blue rounded-pill px-3 btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjukanCuti">
    <i class="bi bi-plus-lg me-1"></i> Ajukan Cuti
  </button>
</div>

{{-- NOTIFIKASI --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(!$currentKaryawan)
  <div class="alert alert-warning border-0 shadow-sm mb-3">
    <i class="bi bi-exclamation-circle-fill me-2"></i>Data karyawan untuk akun Anda tidak ditemukan. Silakan hubungi admin untuk mengaitkan akun dengan data karyawan.
  </div>
@endif

{{-- ===================== STAT CARDS (RINGKASAN) ===================== --}}
@php
  $cutiCards = [
    ['label' => 'Total Pengajuan', 'count' => $total ?? 0, 'status' => null, 'color' => 'text-dark'],
    ['label' => 'Menunggu', 'count' => $waiting ?? 0, 'status' => 'Menunggu', 'color' => 'text-warning'],
    ['label' => 'Disetujui', 'count' => $approved ?? 0, 'status' => 'Disetujui', 'color' => 'text-success'],
    ['label' => 'Ditolak', 'count' => $rejected ?? 0, 'status' => 'Ditolak', 'color' => 'text-danger'],
  ];
@endphp

<div class="row g-2 mb-3">
  @foreach($cutiCards as $card)
    <div class="col-6 col-md-3">
      <form method="GET" action="{{ route('cuti.index') }}">
        @if($card['status'])
          <input type="hidden" name="status" value="{{ $card['status'] }}">
        @endif
        <button type="submit" class="stat-card p-3 w-100 text-start {{ request('status') === $card['status'] ? 'active-card' : '' }}">
          <div class="text-muted small fw-medium mb-1">{{ $card['label'] }}</div>
          <div class="h3 fw-bold mb-0 {{ $card['color'] }}">{{ $card['count'] }}</div>
        </button>
      </form>
    </div>
  @endforeach
</div>

{{-- ===================== TABEL RIWAYAT CUTI ===================== --}}
<div class="panel">
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 50px;">No</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Durasi</th>
            <th>Alasan</th>
            <th>Status</th>
            <th class="text-end pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $i => $row)
            <tr>
              <td class="ps-3">{{ $items->firstItem() + $i }}</td>
              <td class="text-nowrap fw-medium text-secondary">{{ $row->tanggal_mulai->format('d M Y') }}</td>
              <td class="text-nowrap fw-medium text-secondary">{{ $row->tanggal_selesai->format('d M Y') }}</td>
              <td class="text-nowrap">{{ $row->durasi }} Hari</td>
              <td>
                <div class="text-wrap" style="max-width: 300px; line-height: 1.4;">
                  {{ Str::limit($row->alasan, 90) }}
                </div>
              </td>
              <td class="text-nowrap">
                @php
                  $badgeStyle = match($row->status) {
                    'Disetujui' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                    'Ditolak'   => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                    default     => 'bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25',
                  };
                @endphp
                <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold {{ $badgeStyle }}">{{ $row->status }}</span>
              </td>
              <td class="text-end pe-3 text-nowrap">
                <a href="{{ route('cuti.surat', $row) }}" class="btn btn-outline-secondary btn-sm btn-action-sm me-1 rounded-2 text-dark" title="Lihat Surat Cuti">
                  <i class="bi bi-file-earmark-text"></i>
                </a>
                <a href="{{ route('cuti.pdf', $row) }}" class="btn btn-sm btn-action-sm me-1 rounded-2 text-white" style="background-color: #0f172a;" title="Download PDF">
                  <i class="bi bi-file-earmark-pdf"></i>
                </a>
                @if($row->status === 'Menunggu')
                  <form method="POST" action="{{ route('cuti.destroy', $row) }}" class="d-inline" onsubmit="return confirm('Batalkan pengajuan cuti ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm btn-action-sm rounded-2" title="Batalkan Pengajuan">
                      <i class="bi bi-x-circle me-1"></i>Batalkan
                    </button>
                  </form>
                @else
                  <span class="text-muted small italic fs-7">Sudah diproses</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat pengajuan cuti.</td>
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

{{-- ===================== MODAL AJUKAN CUTI ===================== --}}
<div class="modal fade" id="modalAjukanCuti" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 pb-4 pt-0 text-center">

        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
             style="width:56px; height:56px;">
          <i class="bi bi-calendar-event fs-3 text-primary"></i>
        </div>
        
        <h5 class="fw-bold mb-1" style="color: #0f172a;">Ajukan Cuti</h5>
        <p class="text-muted small mb-4">Isi form di bawah untuk mengajukan permohonan cuti</p>

        @if($currentKaryawan)
          <form method="POST" action="{{ route('cuti.store') }}" class="text-start">
            @csrf

            <div class="bg-light rounded-3 p-2.5 mb-3 d-flex align-items-center gap-3 border">
              <div class="d-flex align-items-center justify-content-center rounded-circle bg-white border text-primary fw-bold"
                   style="width:40px; height:40px; font-size: 14px;">
                {{ strtoupper(substr($currentKaryawan->nama, 0, 2)) }}
              </div>
              <div>
                <div class="fw-bold text-dark" style="font-size: 14px;">{{ $currentKaryawan->nama }}</div>
                <small class="text-muted d-block" style="font-size: 11px;">
                  {{ optional($currentKaryawan->jabatan)->nama_jabatan ?? '-' }} &bull; {{ optional($currentKaryawan->divisi)->nama_divisi ?? '-' }}
                </small>
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" required>
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" class="form-control form-control-sm" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Alasan Cuti <span class="text-danger">*</span></label>
              <textarea name="alasan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan alasan pengajuan cuti Anda..." required></textarea>
            </div>

            <button type="submit" class="btn btn-blue w-100 py-2 font-weight-semibold">
              <i class="bi bi-send me-1"></i> Kirim Pengajuan
            </button>
          </form>
        @else
          <div class="alert alert-warning text-start small mb-0">
            <i class="bi bi-exclamation-circle-fill me-1"></i> Data karyawan untuk akun Anda tidak ditemukan. Silakan hubungi admin.
          </div>
        @endif

      </div>
    </div>
  </div>
</div>

@endsection