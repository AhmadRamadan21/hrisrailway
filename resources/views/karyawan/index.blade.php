@extends('layouts.app')

@php($pageTitle = 'Manajemen Karyawan')

@section('content')
    {{-- HEADER HALAMAN & TOMBOL TAMBAH --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Manajemen Karyawan</h4>
            <p class="text-muted small mb-0">Kelola data seluruh pegawai, struktur divisi, dan informasi status kerja</p>
        </div>
        <a href="{{ route('karyawan.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-sm-inline">Tambah</span> Karyawan
        </a>
    </div>

    {{-- ===================== FILTER PANEL ===================== --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="fw-bold mb-3 text-dark small d-flex align-items-center gap-2">
                <i class="bi bi-funnel text-primary"></i> Filter & Pencarian Karyawan
            </div>
            <div class="row g-2 align-items-center">

                {{-- Input Search --}}
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="filterSearch" class="form-control form-control-sm border-start-0 bg-light"
                            placeholder="Cari nama, email, no. pegawai...">
                    </div>
                </div>

                {{-- Dropdown Divisi --}}
                <div class="col-6 col-md-2">
                    <select id="filterDivisi" class="form-select form-select-sm bg-light">
                        <option value="">Semua Divisi</option>
                        @foreach ($divisis as $d)
                            <option value="{{ $d->nama_divisi }}">{{ $d->nama_divisi }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown Jabatan --}}
                <div class="col-6 col-md-2">
                    <select id="filterJabatan" class="form-select form-select-sm bg-light">
                        <option value="">Semua Jabatan</option>
                        @foreach ($jabatans as $j)
                            <option value="{{ $j->nama_jabatan }}">{{ $j->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown Status --}}
                <div class="col-6 col-md-2">
                    <select id="filterStatus" class="form-select form-select-sm bg-light">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="col-6 col-md-2 d-flex gap-1 justify-content-end">
                    <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary w-100 rounded-2"
                        title="Reset filter">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <a href="{{ route('karyawan.export.excel') }}" class="btn btn-sm btn-outline-success w-100 rounded-2"
                        title="Export Excel">
                        <i class="bi bi-file-earmark-excel"></i>
                    </a>
                    <a href="{{ route('karyawan.export.pdf') }}" target="_blank"
                        class="btn btn-sm btn-outline-danger w-100 rounded-2" title="Export PDF">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- ===================== TABEL KARYAWAN ===================== --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableKaryawan" data-no-datatable>
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th>Profil</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Divisi</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>No Pegawai</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawans as $i => $k)
                            <tr data-search="{{ strtolower($k->nama . ' ' . $k->email . ' ' . $k->no_pegawai) }}"
                                data-divisi="{{ $k->divisi->nama_divisi ?? '' }}"
                                data-jabatan="{{ $k->jabatan->nama_jabatan ?? '' }}" data-status="{{ $k->status }}">
                                <td class="ps-4 fw-medium text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="avatar-circle"
                                        style="width: 36px; height: 36px; font-size: 12px; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        {{ strtoupper(substr($k->nama, 0, 2)) }}
                                    </div>
                                </td>
                                <td class="fw-semibold text-dark text-nowrap">{{ $k->nama }}</td>
                                <td class="text-muted">{{ $k->email }}</td>
                                <td class="text-nowrap">{{ $k->divisi->nama_divisi ?? '-' }}</td>
                                <td class="text-nowrap">{{ $k->jabatan->nama_jabatan ?? '-' }}</td>
                                <td>
                                    @if ($k->status === 'Aktif')
                                        <span class="badge bg-success-subtle text-success fw-medium px-2 py-1 rounded-pill">
                                            <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle text-secondary fw-medium px-2 py-1 rounded-pill">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-nowrap font-monospace text-muted small">{{ $k->no_pegawai ?? '-' }}</td>
                                <td class="text-end pe-4 text-nowrap">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="btn btn-sm btn-outline-info rounded-2 px-2 py-1"
                                            data-bs-toggle="modal" data-bs-target="#modalLihatKaryawan{{ $k->id }}"
                                            title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEditKaryawan{{ $k->id }}"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('karyawan.destroy', $k->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1"
                                                title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada data karyawan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL LIHAT (PER KARYAWAN) ===================== --}}
    @foreach ($karyawans as $k)
        <div class="modal fade" id="modalLihatKaryawan{{ $k->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-white py-3 px-4 border-bottom-0">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-person-badge text-primary me-2"></i>Detail Karyawan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <!-- Profile Banner -->
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4"
                            style="background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); border: 1px solid #dbeafe;">
                            @if (optional($k->user)->foto)
                                <img src="{{ asset(optional($k->user)->foto) }}" alt="Foto"
                                    class="rounded-circle shadow-sm object-fit-cover"
                                    style="width: 56px; height: 56px; border: 2px solid #fff;">
                            @else
                                <div class="avatar-circle shadow-sm"
                                    style="width: 56px; height: 56px; font-size: 18px; background: #6366f1; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                    {{ strtoupper(substr($k->nama, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <h5 class="fw-bold mb-1 text-dark" style="color: #1e293b;">{{ $k->nama }}</h5>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span
                                        class="badge bg-white text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-medium shadow-sm"
                                        style="font-size: 11px;">
                                        {{ $k->jabatan->nama_jabatan ?? 'Tanpa Jabatan' }}
                                    </span>
                                    <span
                                        class="badge {{ $k->status === 'Aktif' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle' }} border px-2 py-1 rounded-pill fw-medium shadow-sm"
                                        style="font-size: 11px;">
                                        <i
                                            class="bi {{ $k->status === 'Aktif' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>{{ $k->status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Grid -->
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 rounded-3 h-100"
                                    style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="small text-muted mb-1" style="font-size: 12px;"><i
                                            class="bi bi-upc-scan me-1"></i> No Pegawai</div>
                                    <div class="fw-semibold text-dark font-monospace">{{ $k->no_pegawai ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 h-100"
                                    style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="small text-muted mb-1" style="font-size: 12px;"><i
                                            class="bi bi-diagram-3 me-1"></i> Divisi</div>
                                    <div class="fw-semibold text-dark">{{ $k->divisi->nama_divisi ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="small text-muted mb-1" style="font-size: 12px;"><i
                                            class="bi bi-envelope me-1"></i> Email</div>
                                    <div class="fw-semibold text-dark">{{ $k->email }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 h-100"
                                    style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="small text-muted mb-1" style="font-size: 12px;"><i
                                            class="bi bi-telephone me-1"></i> No HP</div>
                                    <div class="fw-semibold text-dark">{{ $k->no_hp ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 h-100"
                                    style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="small text-muted mb-1" style="font-size: 12px;"><i
                                            class="bi bi-calendar-event me-1"></i> Tanggal Lahir</div>
                                    <div class="fw-semibold text-dark">
                                        {{ $k->tanggal_lahir ? $k->tanggal_lahir->format('d M Y') : '-' }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                                    <div class="small text-muted mb-1" style="font-size: 12px;"><i
                                            class="bi bi-geo-alt me-1"></i> Alamat</div>
                                    <div class="fw-medium text-dark lh-sm" style="font-size: 13.5px;">
                                        {{ $k->alamat ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- ===================== MODAL EDIT (PER KARYAWAN) ===================== --}}
    @foreach ($karyawans as $k)
        <div class="modal fade" id="modalEditKaryawan{{ $k->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form action="{{ route('karyawan.update', $k->id) }}" method="POST" class="w-100">
                    @csrf
                    @method('PUT')
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="modal-header bg-white py-3 px-4 border-bottom-0">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Karyawan
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 pt-0">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Nama Lengkap <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control rounded-3"
                                        value="{{ old('nama', $k->nama) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">No Pegawai</label>
                                    <input type="text" name="no_pegawai" class="form-control rounded-3"
                                        value="{{ old('no_pegawai', $k->no_pegawai) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Email <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control rounded-3"
                                        value="{{ old('email', $k->email) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">No HP</label>
                                    <input type="text" name="no_hp" class="form-control rounded-3"
                                        value="{{ old('no_hp', $k->no_hp) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control rounded-3"
                                        value="{{ old('tanggal_lahir', optional($k->tanggal_lahir)->format('Y-m-d')) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Status <span
                                            class="text-danger">*</span></label>
                                    <select name="status" class="form-select rounded-3" required>
                                        <option value="Aktif"
                                            {{ old('status', $k->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Nonaktif"
                                            {{ old('status', $k->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Divisi</label>
                                    <select name="divisi_id" class="form-select rounded-3">
                                        <option value="">-- Pilih Divisi --</option>
                                        @foreach ($divisis as $d)
                                            <option value="{{ $d->id }}"
                                                {{ old('divisi_id', $k->divisi_id) == $d->id ? 'selected' : '' }}>
                                                {{ $d->nama_divisi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Jabatan</label>
                                    <select name="jabatan_id" class="form-select rounded-3">
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach ($jabatans as $j)
                                            <option value="{{ $j->id }}"
                                                {{ old('jabatan_id', $k->jabatan_id) == $j->id ? 'selected' : '' }}>
                                                {{ $j->nama_jabatan }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small">Alamat</label>
                                    <textarea name="alamat" class="form-control rounded-3" rows="2">{{ old('alamat', $k->alamat) }}</textarea>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer bg-light px-4 py-3 border-top-0">
                            <button type="button" class="btn btn-light rounded-pill px-3"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Simpan
                                Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('filterSearch');
            const divisiSelect = document.getElementById('filterDivisi');
            const jabatanSelect = document.getElementById('filterJabatan');
            const statusSelect = document.getElementById('filterStatus');
            const resetBtn = document.getElementById('btnResetFilter');
            const rows = document.querySelectorAll('#tableKaryawan tbody tr');

            function applyFilter() {
                const q = (searchInput.value || '').toLowerCase().trim();
                const divisi = divisiSelect.value;
                const jabatan = jabatanSelect.value;
                const status = statusSelect.value;

                rows.forEach(row => {
                    if (!row.dataset.search) return;
                    const matchSearch = !q || row.dataset.search.includes(q);
                    const matchDivisi = !divisi || row.dataset.divisi === divisi;
                    const matchJabatan = !jabatan || row.dataset.jabatan === jabatan;
                    const matchStatus = !status || row.dataset.status === status;

                    row.style.display = (matchSearch && matchDivisi && matchJabatan && matchStatus) ? '' :
                        'none';
                });
            }

            [searchInput, divisiSelect, jabatanSelect, statusSelect].forEach(el => {
                if (el) {
                    el.addEventListener('input', applyFilter);
                    el.addEventListener('change', applyFilter);
                }
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    divisiSelect.value = '';
                    jabatanSelect.value = '';
                    statusSelect.value = '';
                    applyFilter();
                });
            }
        });
    </script>
@endsection
