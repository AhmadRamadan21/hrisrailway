@extends('layouts.app')

@section('content')

{{-- STYLES KHUSUS DIVISI & JABATAN --}}
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
    padding: 20px;
  }

  /* Tab Data Divisi / Data Jabatan */
  #divisiJabatanTabs {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    border-bottom: 1px solid #e2e8f0;
  }
  #divisiJabatanTabs .nav-link {
    color: #64748b;
    font-weight: 600;
    border: none;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    padding: 10px 18px;
    background: transparent;
    transition: all .15s ease-in-out;
  }
  #divisiJabatanTabs .nav-link:hover {
    color: #2563eb;
  }
  #divisiJabatanTabs .nav-link.active {
    color: #2563eb;
    background: transparent;
    border-bottom: 3px solid #2563eb;
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
    border-color: #bfdbfe;
    background-color: #eff6ff;
  }
  .btn-outline-blue:hover, .btn-outline-blue:focus {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
  }

  /* Responsive Adjustments */
  @media (max-width: 767.98px) {
    .panel-body {
      padding: 14px;
    }
    .table-responsive {
      font-size: 13px;
    }
  }
</style>

{{-- HEADER HALAMAN --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0" style="color: #0f172a;">Data Divisi & Jabatan</h4>
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

<div class="panel">
  <div class="panel-body">

    {{-- TAB NAVIGATION --}}
    <ul class="nav nav-tabs mb-4" id="divisiJabatanTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active d-flex align-items-center gap-2" id="tab-divisi-btn" data-bs-toggle="tab"
                data-bs-target="#tabDivisi" type="button" role="tab">
          <i class="bi bi-diagram-3 fs-6"></i> Data Divisi
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link d-flex align-items-center gap-2" id="tab-jabatan-btn" data-bs-toggle="tab"
                data-bs-target="#tabJabatan" type="button" role="tab">
          <i class="bi bi-bookmark-fill fs-6"></i> Data Jabatan
        </button>
      </li>
    </ul>

    <div class="tab-content" id="divisiJabatanTabsContent">

      {{-- ===================== TAB DATA DIVISI ===================== --}}
      <div class="tab-pane fade show active" id="tabDivisi" role="tabpanel">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0 text-dark">Daftar Divisi</h6>
          <button class="btn btn-blue btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahDivisi">
            <i class="bi bi-plus-lg me-1"></i> Tambah Divisi
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableDivisi">
            <thead class="table-light">
              <tr>
                <th class="ps-3" style="width:70px;">No</th>
                <th>Nama Divisi</th>
                <th class="text-end pe-3" style="width:180px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($divisis as $index => $d)
                <tr>
                  <td class="ps-3">{{ $index + 1 }}</td>
                  <td class="fw-medium text-dark">{{ $d->nama_divisi }}</td>
                  <td class="text-end pe-3">
                    <button class="btn btn-outline-blue btn-sm rounded-2 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#modalEditDivisi{{ $d->id }}" title="Edit Divisi">
                      <i class="bi bi-pencil-fill me-1"></i> Edit
                    </button>
                    <form action="{{ route('divisi.destroy', $d) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus divisi ini?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm rounded-2 px-2.5 py-1" title="Hapus Divisi">
                        <i class="bi bi-trash-fill me-1"></i> Hapus
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data divisi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ===================== TAB DATA JABATAN ===================== --}}
      <div class="tab-pane fade" id="tabJabatan" role="tabpanel">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0 text-dark">Daftar Jabatan</h6>
          <button class="btn btn-blue btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahJabatan">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jabatan
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableJabatan">
            <thead class="table-light">
              <tr>
                <th class="ps-3" style="width:70px;">No</th>
                <th>Nama Jabatan</th>
                <th>Divisi</th>
                <th class="text-end pe-3" style="width:180px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($jabatans as $index => $j)
                <tr>
                  <td class="ps-3">{{ $index + 1 }}</td>
                  <td class="fw-medium text-dark">{{ $j->nama_jabatan }}</td>
                  <td>
                    <span class="badge bg-light text-secondary border px-2 py-1 fw-normal">
                      {{ $j->divisi->nama_divisi ?? 'Tanpa Divisi' }}
                    </span>
                  </td>
                  <td class="text-end pe-3">
                    <button class="btn btn-outline-blue btn-sm rounded-2 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#modalEditJabatan{{ $j->id }}" title="Edit Jabatan">
                      <i class="bi bi-pencil-fill me-1"></i> Edit
                    </button>
                    <form action="{{ route('jabatan.destroy', $j) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm rounded-2 px-2.5 py-1" title="Hapus Jabatan">
                        <i class="bi bi-trash-fill me-1"></i> Hapus
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data jabatan.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- ===================== SEMUA MODAL ===================== --}}

<!-- Modal Tambah Divisi -->
<div class="modal fade" id="modalTambahDivisi" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('divisi.store') }}" method="POST" class="w-100">
      @csrf
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold" style="color: #0f172a;">Tambah Divisi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nama Divisi <span class="text-danger">*</span></label>
            <input type="text" name="nama_divisi" class="form-control form-control-sm" placeholder="Contoh: HRD, IT, Marketing" required>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-blue btn-sm px-3">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Tambah Jabatan -->
<div class="modal fade" id="modalTambahJabatan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('jabatan.store') }}" method="POST" class="w-100">
      @csrf
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold" style="color: #0f172a;">Tambah Jabatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nama Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="nama_jabatan" class="form-control form-control-sm" placeholder="Contoh: Manager, Senior Developer" required>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-semibold">Divisi Terkait</label>
            <select name="divisi_id" class="form-select form-select-sm">
              <option value="">- Tanpa Divisi -</option>
              @foreach($divisis as $d)
                <option value="{{ $d->id }}">{{ $d->nama_divisi }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-blue btn-sm px-3">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Divisi (satu per divisi) -->
@foreach($divisis as $d)
  <div class="modal fade" id="modalEditDivisi{{ $d->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form action="{{ route('divisi.update', $d) }}" method="POST" class="w-100">
        @csrf @method('PUT')
        <div class="modal-content border-0 shadow">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold" style="color: #0f172a;">Edit Divisi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label small fw-semibold">Nama Divisi <span class="text-danger">*</span></label>
              <input type="text" name="nama_divisi" class="form-control form-control-sm" value="{{ $d->nama_divisi }}" required>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-blue btn-sm px-3">Simpan Perubahan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endforeach

<!-- Modal Edit Jabatan (satu per jabatan) -->
@foreach($jabatans as $j)
  <div class="modal fade" id="modalEditJabatan{{ $j->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form action="{{ route('jabatan.update', $j) }}" method="POST" class="w-100">
        @csrf @method('PUT')
        <div class="modal-content border-0 shadow">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold" style="color: #0f172a;">Edit Jabatan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label small fw-semibold">Nama Jabatan <span class="text-danger">*</span></label>
              <input type="text" name="nama_jabatan" class="form-control form-control-sm" value="{{ $j->nama_jabatan }}" required>
            </div>
            <div class="mb-2">
              <label class="form-label small fw-semibold">Divisi Terkait</label>
              <select name="divisi_id" class="form-select form-select-sm">
                <option value="">- Tanpa Divisi -</option>
                @foreach($divisis as $d)
                  <option value="{{ $d->id }}" {{ $j->divisi_id == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-blue btn-sm px-3">Simpan Perubahan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endforeach

@endsection

@section('scripts')
<script>
// Resizing event listener agar lebar tabel pada tab yang sempat tersembunyi dapat terhitung sempurna saat diklik
document.addEventListener('DOMContentLoaded', function () {
  const jabatanTabBtn = document.getElementById('tab-jabatan-btn');
  if (jabatanTabBtn) {
    jabatanTabBtn.addEventListener('shown.bs.tab', function () {
      window.dispatchEvent(new Event('resize'));
    });
  }
});
</script>
@endsection