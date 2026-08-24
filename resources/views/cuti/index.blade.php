@extends('layouts.app')

@section('content')

{{-- STYLES KHUSUS MANAJEMEN CUTI --}}
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

  /* Responsive Adjustments untuk Tampilan HP */
  @media (max-width: 767.98px) {
    .panel-body {
      padding: 12px 14px;
    }
    .table-responsive {
      font-size: 13px;
    }
    .btn-action-sm {
      padding: 3px 6px;
      font-size: 11px;
    }
  }
</style>

{{-- HEADER HALAMAN --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0" style="color: #0f172a;">Manajemen Cuti</h4>
</div>

{{-- NOTIFIKASI --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- ===================== STAT CARDS (SUMMARY) ===================== --}}
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
        @if(request()->filled('karyawan_id'))
          <input type="hidden" name="karyawan_id" value="{{ request('karyawan_id') }}">
        @endif
        @if(request()->filled('divisi_id'))
          <input type="hidden" name="divisi_id" value="{{ request('divisi_id') }}">
        @endif
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

{{-- ===================== FILTER PANEL ===================== --}}
<div class="panel">
  <div class="panel-body">
    <div class="fw-bold mb-2" style="font-size: 14px; color: #334155;">Filter Pengajuan Cuti</div>
    <form method="GET" action="{{ route('cuti.index') }}" class="row g-2 align-items-end">
      
      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Semua Status</option>
          <option value="Menunggu" {{ request('status')=='Menunggu' ? 'selected':'' }}>Menunggu</option>
          <option value="Disetujui" {{ request('status')=='Disetujui' ? 'selected':'' }}>Disetujui</option>
          <option value="Ditolak" {{ request('status')=='Ditolak' ? 'selected':'' }}>Ditolak</option>
        </select>
      </div>

      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Divisi</label>
        <select name="divisi_id" class="form-select form-select-sm">
          <option value="">Semua Divisi</option>
          @foreach($divisis as $d)
            <option value="{{ $d->id }}" {{ request('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Karyawan</label>
        <select name="karyawan_id" class="form-select form-select-sm">
          <option value="">Semua Karyawan</option>
          @foreach($karyawans as $k)
            <option value="{{ $k->id }}" {{ request('karyawan_id')==$k->id ? 'selected':'' }}>{{ $k->nama }}</option>
          @endforeach
        </select>
      </div>

      {{-- TOMBOL TAMPILKAN & RESET PAS DAN SENADA --}}
      <div class="col-12 col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-blue btn-sm px-3 fw-medium">
          <i class="bi bi-search me-1"></i> Tampilkan
        </button>
        <a href="{{ route('cuti.index') }}" class="btn btn-outline-blue btn-sm px-3 fw-medium" title="Reset Filter">
          <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </a>
      </div>

    </form>
  </div>
</div>

{{-- ===================== TABEL MANAJEMEN CUTI ===================== --}}
<div class="panel">
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 50px;">No</th>
            <th>Karyawan</th>
            <th>Divisi</th>
            <th>Tgl Mulai</th>
            <th>Tgl Selesai</th>
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
              <td class="fw-semibold text-dark text-nowrap">{{ $row->karyawan->nama ?? '-' }}</td>
              <td class="text-nowrap">{{ optional($row->karyawan->divisi)->nama_divisi ?? '-' }}</td>
              <td class="text-nowrap">{{ $row->tanggal_mulai->format('d M Y') }}</td>
              <td class="text-nowrap">{{ $row->tanggal_selesai->format('d M Y') }}</td>
              <td class="text-nowrap">{{ $row->durasi }} Hari</td>
              <td>
                <div class="text-wrap" style="max-width: 250px; line-height: 1.4;">
                  {{ Str::limit($row->alasan, 80) }}
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
                  <form method="POST" action="{{ route('cuti.approve', $row) }}" class="d-inline" onsubmit="return confirm('Setujui pengajuan cuti {{ $row->karyawan->nama ?? '' }}?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-blue btn-sm btn-action-sm me-1 rounded-2" title="Setujui Cuti">
                      <i class="bi bi-check-lg me-1"></i>Setujui
                    </button>
                  </form>
                  <form method="POST" action="{{ route('cuti.reject', $row) }}" class="d-inline" onsubmit="return confirm('Tolak pengajuan cuti {{ $row->karyawan->nama ?? '' }}?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-warning btn-sm btn-action-sm me-1 rounded-2 text-dark" title="Tolak Cuti">
                      <i class="bi bi-x-lg me-1"></i>Tolak
                    </button>
                  </form>
                @endif

                <form method="POST" action="{{ route('cuti.destroy', $row) }}" class="d-inline" onsubmit="return confirm('Hapus pengajuan cuti ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-sm btn-action-sm rounded-2" title="Hapus">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">Belum ada data pengajuan cuti.</td>
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