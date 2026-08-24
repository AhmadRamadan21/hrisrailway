@extends('layouts.app')

@section('content')

{{-- STYLES KHUSUS KEGIATAN HARIAN --}}
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

  .btn-blue-icon {
    background-color: #2563eb;
    color: #ffffff;
    border: none;
  }
  .btn-blue-icon:hover {
    background-color: #1d4ed8;
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
      padding: 3px 6px;
      font-size: 11px;
    }
  }
</style>

{{-- HEADER & TOMBOL TAMBAH --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0" style="color: #0f172a;">Kegiatan Harian</h4>
  <button class="btn btn-blue rounded-pill px-3 btn-sm fs-md-6" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan">
    <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Laporan</span> Kegiatan
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

{{-- ===================== FILTER PANEL ===================== --}}
<div class="panel">
  <div class="panel-body">
    <div class="fw-bold mb-2" style="font-size: 14px; color: #334155;">Filter Kegiatan</div>
    <form method="GET" action="{{ route('kegiatan-harian.index') }}" class="row g-2 align-items-end">
      
      @if($isAdminView)
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold text-muted mb-1">Karyawan</label>
          <select name="karyawan_id" class="form-select form-select-sm">
            <option value="">Semua Karyawan</option>
            @foreach($karyawans as $k)
              <option value="{{ $k->id }}" @selected(request('karyawan_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <div class="col-12 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Tanggal</label>
        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal') }}">
      </div>

      {{-- TOMBOL TAMPILKAN & RESET DIBUAT PAS DAN PROPORSIONAL --}}
      <div class="col-12 col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-blue btn-sm px-3 fw-medium">
          <i class="bi bi-search me-1"></i> Tampilkan
        </button>
        <a href="{{ route('kegiatan-harian.index') }}" class="btn btn-outline-blue btn-sm px-3 fw-medium" title="Reset Filter">
          <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </a>
      </div>

    </form>
  </div>
</div>

{{-- ===================== TABEL KEGIATAN ===================== --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">No</th>
            @if($isAdminView)
              <th>Karyawan</th>
            @endif
            <th>Tanggal</th>
            <th>Kegiatan</th>
            <th class="text-end pe-4">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($kegiatan as $i => $row)
            <tr>
              <td class="ps-4">{{ $kegiatan->firstItem() + $i }}</td>
              @if($isAdminView)
                <td class="text-nowrap fw-medium text-dark">{{ $row->karyawan->nama ?? '-' }}</td>
              @endif
              <td class="text-nowrap fw-medium text-secondary">{{ $row->tanggal->format('d M Y') }}</td>
              <td>
                <div class="text-wrap text-secondary" style="max-width: 400px; line-height: 1.4;">
                  {{ Str::limit($row->kegiatan, 100) }}
                </div>
              </td>
              <td class="text-end pe-4 text-nowrap">
                <button class="btn btn-outline-secondary btn-sm btn-action-sm me-1 rounded-2 text-dark" data-bs-toggle="modal" data-bs-target="#modalEditKegiatan{{ $row->id }}" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <form method="POST" action="{{ route('kegiatan-harian.destroy', $row) }}" class="d-inline" onsubmit="return confirm('Hapus laporan kegiatan ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-sm btn-action-sm rounded-2" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ $isAdminView ? 5 : 4 }}" class="text-center py-4 text-muted">Belum ada riwayat kegiatan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- MODAL EDIT (Diluar tabel agar tidak conflict dengan datatables) --}}
@foreach($kegiatan as $row)
  <div class="modal fade" id="modalEditKegiatan{{ $row->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('kegiatan-harian.update', $row) }}" class="w-100">
        @csrf
        @method('PUT')
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-bottom-0 pt-4 pb-2 px-4">
            <h5 class="modal-title fw-bold" style="color: #0f172a;"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Laporan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body px-4">
            
            @if($isAdminView)
              <div class="mb-3">
                <label class="form-label small fw-semibold text-dark">Karyawan</label>
                <select name="karyawan_id" class="form-select rounded-3" required>
                  @foreach($karyawans as $k)
                    <option value="{{ $k->id }}" @selected($row->karyawan_id == $k->id)>{{ $k->nama }}</option>
                  @endforeach
                </select>
              </div>
            @else
              <input type="hidden" name="karyawan_id" value="{{ $row->karyawan_id }}">
            @endif

            <div class="mb-3">
              <label class="form-label small fw-semibold text-dark">Tanggal</label>
              <input type="date" name="tanggal" class="form-control rounded-3" value="{{ $row->tanggal->format('Y-m-d') }}" required>
            </div>

            <div class="mb-2">
              <label class="form-label small fw-semibold text-dark">Deskripsi Kegiatan</label>
              <textarea name="kegiatan" class="form-control rounded-3" rows="5" required>{{ $row->kegiatan }}</textarea>
            </div>

          </div>
          <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-blue rounded-pill px-4 fw-medium">Simpan Perubahan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endforeach

{{-- PAGINASI --}}
@if(method_exists($kegiatan, 'links'))
  <div class="d-flex justify-content-end mt-3">
    {{ $kegiatan->links() }}
  </div>
@endif

{{-- ===================== MODAL TAMBAH KEGIATAN ===================== --}}
<div class="modal fade" id="modalTambahKegiatan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 pb-4 pt-0 text-center">

        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
             style="width:56px; height:56px;">
          <i class="bi bi-journal-plus fs-3 text-primary"></i>
        </div>
        
        <h5 class="fw-bold mb-1" style="color: #0f172a;">Laporan Kegiatan Harian</h5>
        <p class="text-muted small mb-4">Catat pekerjaan dan aktivitas harian Anda</p>

        <form method="POST" action="{{ route('kegiatan-harian.store') }}" class="text-start">
          @csrf

          @if($isAdminView)
            <div class="mb-3">
              <label class="form-label small fw-semibold">Karyawan <span class="text-danger">*</span></label>
              <select name="karyawan_id" class="form-select form-select-sm" required>
                <option value="">- Pilih Karyawan -</option>
                @foreach($karyawans as $k)
                  <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
              </select>
            </div>
          @elseif($currentKaryawan)
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
          @else
            <div class="alert alert-warning text-start small mb-3">
              <i class="bi bi-exclamation-circle-fill me-1"></i> Data karyawan untuk akun Anda tidak ditemukan. Silakan hubungi admin.
            </div>
          @endif

          @if($isAdminView || $currentKaryawan)
            <div class="mb-3">
              <label class="form-label small fw-semibold">Tanggal Kegiatan <span class="text-danger">*</span></label>
              <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Deskripsi Kegiatan <span class="text-danger">*</span></label>
              <textarea name="kegiatan" class="form-control form-control-sm" rows="4" placeholder="Tuliskan kegiatan, tugas, atau laporan harian Anda..." required></textarea>
            </div>

            <button type="submit" class="btn btn-blue w-100 py-2 font-weight-semibold">
              <i class="bi bi-check-lg me-1"></i> Simpan Laporan
            </button>
          @endif
        </form>

      </div>
    </div>
  </div>
</div>

@endsection