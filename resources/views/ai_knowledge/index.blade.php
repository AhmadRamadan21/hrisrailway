@extends('layouts.app')

@section('title', 'Kelola AI Knowledge - Admin HRIS')

@section('content')
<div class="container-fluid px-0">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-robot text-primary me-2"></i>Kelola AI Knowledge</h4>
      <p class="text-muted small mb-0">Atur kata kunci & jawaban otomatis untuk AI HRIS secara dinamis tanpa perlu kodingan.</p>
    </div>
    <button class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateKnowledge">
      <i class="bi bi-plus-lg me-1"></i> Tambah Pengetahuan Baru
    </button>
  </div>

  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 50px;">#</th>
              <th>Topik / Pengetahuan</th>
              <th>Kata Kunci (Keywords)</th>
              <th style="width: 35%;">Jawaban AI</th>
              <th style="width: 100px;">Status</th>
              <th style="width: 130px;" class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($knowledges as $index => $item)
              <tr>
                <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                <td class="fw-bold text-dark">
                  <i class="bi bi-bookmark-star text-primary me-1"></i>{{ $item->topik }}
                </td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    @foreach(array_filter(array_map('trim', explode(',', $item->keywords))) as $kw)
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 fs-7">
                        {{ $kw }}
                      </span>
                    @endforeach
                  </div>
                </td>
                <td>
                  <div class="small text-muted" style="max-height: 80px; overflow-y: auto; white-space: pre-line;">
                    {{ \Illuminate\Support\Str::limit($item->jawaban, 150) }}
                  </div>
                </td>
                <td>
                  @if($item->is_active)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                      <i class="bi bi-check-circle me-1"></i>Aktif
                    </span>
                  @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                      <i class="bi bi-x-circle me-1"></i>Nonaktif
                    </span>
                  @endif
                </td>
                <td class="text-end">
                  <div class="btn-group gap-1">
                    <button class="btn btn-sm btn-outline-primary rounded-2" data-bs-toggle="modal" data-bs-target="#modalEditKnowledge{{ $item->id }}" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" action="{{ route('ai-knowledge.destroy', $item->id) }}" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" title="Hapus">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>

              <!-- Modal Edit Knowledge -->
              <div class="modal fade" id="modalEditKnowledge{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                      <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Pengetahuan AI</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('ai-knowledge.update', $item->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="modal-body p-4">
                        <div class="mb-3">
                          <label class="form-label fw-semibold text-dark">Topik / Judul Pengetahuan</label>
                          <input type="text" class="form-control rounded-3" name="topik" value="{{ $item->topik }}" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold text-dark">Kata Kunci (Keywords)</label>
                          <input type="text" class="form-control rounded-3" name="keywords" value="{{ $item->keywords }}" required>
                          <div class="form-text small text-muted">Pisahkan setiap kata kunci dengan tanda koma (contoh: <code>seragam, baju, pakaian, dresscode</code>).</div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold text-dark">Jawaban AI</label>
                          <textarea class="form-control rounded-3" name="jawaban" rows="6" required>{{ $item->jawaban }}</textarea>
                          <div class="form-text small text-muted">Mendukung format tulisan: <code>**teks tebal**</code>, baris baru. Gunakan tag <code>{nama}</code> untuk menyapa nama karyawan secara otomatis.</div>
                        </div>
                        <div class="form-check form-switch mb-2">
                          <input class="form-check-input" type="checkbox" name="is_active" id="editActive{{ $item->id }}" value="1" {{ $item->is_active ? 'checked' : '' }}>
                          <label class="form-check-label fw-semibold text-dark" for="editActive{{ $item->id }}">Aktifkan Pengetahuan Ini</label>
                        </div>
                      </div>
                      <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Simpan Perubahan</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            @empty
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                  <span>Belum ada data pengetahuan AI. Klik tombol <b>+ Tambah Pengetahuan Baru</b> di atas untuk menambahkan.</span>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Create Knowledge -->
<div class="modal fade" id="modalCreateKnowledge" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-robot text-primary me-2"></i>Tambah Pengetahuan AI Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('ai-knowledge.store') }}">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Topik / Judul Pengetahuan</label>
            <input type="text" class="form-control rounded-3" name="topik" placeholder="Contoh: Aturan Pakaian Seragam Kantor" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Kata Kunci (Keywords)</label>
            <input type="text" class="form-control rounded-3" name="keywords" placeholder="Contoh: seragam, baju, pakaian, dresscode" required>
            <div class="form-text small text-muted">Pisahkan setiap kata kunci dengan tanda koma (contoh: <code>seragam, baju, pakaian, dresscode</code>).</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Jawaban AI</label>
            <textarea class="form-control rounded-3" name="jawaban" rows="6" placeholder="Ketikkan jawaban lengkap AI di sini..." required></textarea>
            <div class="form-text small text-muted">Mendukung format tulisan: <code>**teks tebal**</code>, baris baru. Gunakan tag <code>{nama}</code> untuk menyapa nama karyawan secara otomatis.</div>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_active" id="createActive" value="1" checked>
            <label class="form-check-label fw-semibold text-dark" for="createActive">Aktifkan Pengetahuan Ini</label>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">Simpan Pengetahuan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
