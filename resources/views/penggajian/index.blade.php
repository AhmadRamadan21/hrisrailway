@extends('layouts.app')

@section('content')

{{-- STYLES KHUSUS PENGGAJIAN --}}
<style>
  .panel {
    background: #ffffff;
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06);
    margin-bottom: 24px;
    overflow: hidden;
  }

  .panel-body {
    padding: 24px;
  }

  /* Custom Stat Card Styling */
  .stat-card {
    background: #ffffff;
    border-radius: 16px;
    border: none;
    box-shadow: 0 4px 20px -5px rgba(0,0,0,0.05);
    transition: all .25s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    text-decoration: none;
    display: block;
    position: relative;
    overflow: hidden;
  }
  .stat-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    border-radius: 16px;
    border: 2px solid transparent;
    transition: all .25s ease;
    pointer-events: none;
  }
  .stat-card:hover {
    box-shadow: 0 12px 28px -5px rgba(0,0,0,0.08);
    transform: translateY(-3px);
  }

  /* Active Filter Card State */
  .stat-card.active-card {
    box-shadow: 0 12px 28px -5px rgba(99, 102, 241, 0.15);
  }
  .stat-card.active-card::after {
    border-color: #6366f1;
  }
  .stat-card.active-card:has(.text-warning)::after { border-color: #f59e0b; }
  .stat-card.active-card:has(.text-warning) { box-shadow: 0 12px 28px -5px rgba(245, 158, 11, 0.15); }
  .stat-card.active-card:has(.text-success)::after { border-color: #22c55e; }
  .stat-card.active-card:has(.text-success) { box-shadow: 0 12px 28px -5px rgba(34, 197, 94, 0.15); }

  /* Custom Buttons Theme */
  .btn-blue {
    background-color: #6366f1;
    border-color: #6366f1;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    transition: all 0.2s;
  }
  .btn-blue:hover, .btn-blue:focus {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
  }

  .btn-outline-blue {
    color: #6366f1;
    border-color: #e0e7ff;
    background-color: #e0e7ff;
    transition: all 0.2s;
  }
  .btn-outline-blue:hover, .btn-outline-blue:focus {
    background-color: #c7d2fe;
    border-color: #c7d2fe;
    color: #4f46e5;
  }

  @media (max-width: 767.98px) {
    .panel-body { padding: 16px; }
    .table-responsive { font-size: 13.5px; }
  }
</style>

{{-- HEADER HALAMAN & ACTION BUTTONS --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 mt-2">
  <h3 class="fw-semibold mb-0" style="color: #334155;">Penggajian</h3>
  
  <div class="d-flex gap-2">
    {{-- TOMBOL RESET GAJI --}}
    <form method="POST" action="{{ route('penggajian.reset') }}" onsubmit="return confirm('Reset gaji untuk periode ini?')">
      @csrf
      <input type="hidden" name="bulan" value="{{ $month ?? now()->month }}">
      <input type="hidden" name="tahun" value="{{ $year ?? now()->year }}">
      <button type="submit" class="btn btn-danger btn-sm px-3 fw-medium rounded-3">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Gaji
      </button>
    </form>

    {{-- TOMBOL GENERATE (MEMBUKA MODAL) --}}
    <button type="button" class="btn btn-blue btn-sm px-3 fw-medium rounded-3" data-bs-toggle="modal" data-bs-target="#modalGeneratePenggajian">
      <i class="bi bi-gear me-1"></i> Generate Penggajian
    </button>

    {{-- TOMBOL EKSPOR EXCEL --}}
    <a href="{{ route('penggajian.export.excel', ['bulan' => $month ?? now()->month, 'tahun' => $year ?? now()->year]) }}" class="btn btn-outline-secondary btn-sm px-3 fw-medium rounded-3">
      <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
    </a>
  </div>
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

{{-- ===================== STAT CARDS (RINGKASAN) ===================== --}}
@php
  $penggajianQuery = request()->except('page');
  $penggajianCards = [
    ['label' => 'Total Gaji Bulan Ini', 'count' => is_numeric($totalGaji ?? null) ? 'Rp ' . number_format($totalGaji, 0, ',', '.') : ($totalGaji ?? 'Rp 0'), 'status' => null, 'color' => 'text-dark'],
    ['label' => 'Telah Digenerate', 'count' => $countGenerated ?? 0, 'status' => 'Generated', 'color' => 'text-success'],
    ['label' => 'Menunggu Generate', 'count' => $countPending ?? 0, 'status' => 'Pending', 'color' => 'text-warning'],
    ['label' => 'Total Karyawan', 'count' => $totalKaryawan ?? 0, 'status' => null, 'color' => 'text-dark'],
  ];
@endphp

<div class="row g-2 mb-3">
  @foreach($penggajianCards as $card)
    <div class="col-6 col-md-3">
      <a href="{{ route('penggajian.index', array_filter(array_merge($penggajianQuery, ['status' => $card['status']]), fn($value) => $value !== null && $value !== '')) }}" class="text-decoration-none">
        <div class="stat-card p-3 w-100 {{ request('status') === $card['status'] ? 'active-card' : '' }}">
          <div class="text-muted small fw-medium mb-1">{{ $card['label'] }}</div>
          <div class="h4 fw-bold mb-0 {{ $card['color'] }}">{{ $card['count'] }}</div>
        </div>
      </a>
    </div>
  @endforeach
</div>

{{-- ===================== FILTER PANEL ===================== --}}
<div class="panel">
  <div class="panel-body">
    <div class="fw-bold mb-2" style="font-size: 14px; color: #334155;">Filter Periode & Karyawan</div>
    <form method="GET" action="{{ route('penggajian.index') }}" class="row g-2 align-items-end">
      
      <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold text-muted mb-1">Bulan</label>
        <select name="bulan" class="form-select form-select-sm">
          <option value="">Pilih Bulan</option>
          @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
              {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
            </option>
          @endfor
        </select>
      </div>

      <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold text-muted mb-1">Tahun</label>
        <select name="tahun" class="form-select form-select-sm">
          @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
            <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>

      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Divisi</label>
        <select name="divisi_id" class="form-select form-select-sm">
          <option value="">Semua Divisi</option>
          @foreach($divisis ?? [] as $d)
            <option value="{{ $d->id }}" {{ request('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-6 col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Karyawan</label>
        <select name="karyawan_id" class="form-select form-select-sm">
          <option value="">Semua Karyawan</option>
          @foreach($karyawans ?? [] as $k)
            <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-blue btn-sm px-3 fw-medium rounded-3">
          <i class="bi bi-search me-1"></i> Tampilkan
        </button>
        <a href="{{ route('penggajian.index') }}" class="btn btn-outline-blue btn-sm px-3 fw-medium rounded-3" title="Reset Filter">
          <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </a>
      </div>

    </form>
  </div>
</div>

{{-- ===================== TABEL PENGGAJIAN ===================== --}}
<div class="panel">
  <div class="panel-body p-0">
    
    {{-- CONTROLS TOP BAR (Show entries & Search) --}}
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2 small text-secondary fw-semibold">
        Show
        <select id="perPageSelect" class="form-select form-select-sm d-inline-block w-auto rounded-3 border-slate-300" style="font-size: 13px;" onchange="changePerPage(this.value)">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        </select>
        entries
      </div>

      <div class="d-flex align-items-center gap-2 small text-secondary fw-semibold">
        Search:
        <input type="text" id="tableSearchInput" class="form-control form-control-sm rounded-3 border-slate-300" style="width: 200px; font-size: 13px;" placeholder="" onkeyup="filterTable()">
      </div>
    </div>

    {{-- TABEL DATA PENGGAJIAN --}}
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="penggajianTable">
        <thead style="background-color: #f9fafb; border-bottom: 2px solid #f3f4f6;">
          <tr>
            <th class="ps-4 py-3 text-secondary fw-semibold" style="font-size: 13.5px; width: 50px;">
              No
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Periode
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Karyawan
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Divisi
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Jabatan
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Gaji Pokok
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Tunjangan
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Potongan
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Gaji Bersih
            </th>
            <th class="py-3 text-secondary fw-semibold text-nowrap" style="font-size: 13.5px;">
              Status
            </th>
            <th class="text-center py-3 text-secondary fw-semibold pe-4 text-nowrap" style="font-size: 13.5px;">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $i => $row)
            @php
              $nama = $row->karyawan->nama ?? '-';
              $words = explode(' ', trim($nama));
              $initials = count($words) >= 2 
                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                : strtoupper(substr($nama, 0, 2));
              
              $bgColors = ['#dbeafe', '#fce7f3', '#e0e7ff', '#dcfce7', '#fef3c7'];
              $textColors = ['#1e40af', '#9d174d', '#3730a3', '#166534', '#92400e'];
              $colorIndex = ($row->karyawan_id ?? 0) % count($bgColors);
            @endphp
            <tr class="searchable-row" style="transition: all 0.2s;">
              <td class="ps-4 fw-medium text-dark">{{ (method_exists($items, 'firstItem') ? $items->firstItem() : 1) + $i }}</td>
              
              {{-- PERIODE --}}
              <td class="text-nowrap">
                <span class="badge rounded-pill fw-medium px-3 py-1.5" style="background-color: #f1f5f9; color: #475569; font-size: 12.5px;">
                  {{ \Carbon\Carbon::createFromDate($row->tahun, $row->bulan, 1)->format('M Y') }}
                </span>
              </td>
              
              {{-- KARYAWAN --}}
              <td class="text-nowrap">
                <div class="d-flex align-items-center gap-2.5">
                  <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" 
                       style="width: 36px; height: 36px; background-color: {{ $bgColors[$colorIndex] }}; color: {{ $textColors[$colorIndex] }}; font-size: 12px;">
                    {{ $initials }}
                  </div>
                  <div>
                    <div class="fw-bold text-dark text-nowrap" style="font-size: 13px;">{{ strtoupper($nama) }}</div>
                    <div class="small text-muted" style="font-size: 11px;">{{ $row->karyawan->no_pegawai ?? $row->karyawan->nik ?? '-' }}</div>
                  </div>
                </div>
              </td>
              
              {{-- DIVISI --}}
              <td class="text-nowrap text-secondary" style="font-size: 13.5px;">
                {{ ucwords(strtolower(optional($row->karyawan->divisi)->nama_divisi ?? '-')) }}
              </td>
              
              {{-- JABATAN --}}
              <td class="text-nowrap text-secondary" style="font-size: 13.5px;">
                {{ ucwords(strtolower(optional($row->karyawan->jabatan)->nama_jabatan ?? '-')) }}
              </td>
              
              {{-- GAJI POKOK --}}
              <td class="text-nowrap fw-medium text-dark">
                Rp {{ number_format($row->gaji_pokok, 0, ',', '.') }}
              </td>
              
              {{-- TUNJANGAN --}}
              <td class="text-nowrap fw-medium text-dark">
                Rp {{ number_format($row->tunjangan, 0, ',', '.') }}
              </td>
              
              {{-- POTONGAN --}}
              <td class="text-nowrap fw-medium text-dark">
                Rp {{ number_format($row->potongan, 0, ',', '.') }}
              </td>
              
              {{-- GAJI BERSIH --}}
              <td class="text-nowrap fw-bold" style="color: #16a34a;">
                Rp {{ number_format($row->total, 0, ',', '.') }}
              </td>
              
              {{-- STATUS --}}
              <td class="text-nowrap">
                <span class="badge rounded-pill px-3 py-1.5 fw-medium" style="background-color: #dcfce7; color: #15803d; font-size: 12px;">
                  {{ $row->status }}
                </span>
              </td>
              
              {{-- AKSI --}}
              <td class="text-end pe-4 text-nowrap">
                <div class="d-inline-flex gap-2">
                  {{-- PDF --}}
                  <a href="{{ route('penggajian.pdf', $row->id) }}" class="btn btn-sm" style="background-color: #f1f5f9; color: #475569; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'" onmouseout="this.style.backgroundColor='#f1f5f9'" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf"></i>
                  </a>
                  
                  {{-- WORD --}}
                  <a href="{{ route('penggajian.word', $row->id) }}" class="btn btn-sm" style="background-color: #f1f5f9; color: #475569; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'" onmouseout="this.style.backgroundColor='#f1f5f9'" title="Export Word">
                    <i class="bi bi-file-earmark-word"></i>
                  </a>
                  
                  {{-- VIEW DETAIL --}}
                  <a href="{{ route('penggajian.show', $row->id) }}" class="btn btn-sm" style="background-color: #eff6ff; color: #3b82f6; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#dbeafe'" onmouseout="this.style.backgroundColor='#eff6ff'" title="Lihat Slip Gaji">
                    <i class="bi bi-eye"></i>
                  </a>
                  
                  {{-- DELETE --}}
                  <form method="POST" action="{{ route('penggajian.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data penggajian ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background-color: #fef2f2; color: #ef4444; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: none; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#fee2e2'" onmouseout="this.style.backgroundColor='#fef2f2'" title="Hapus">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="text-center text-muted py-4">
                Tidak ada data penggajian. Silakan klik tombol <strong>"Generate Penggajian"</strong> untuk membuat data periode ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- FOOTER INFO & PAGINASI --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-top gap-2 bg-white">
      <div class="small text-muted fw-medium">
        Showing {{ method_exists($items, 'firstItem') ? ($items->firstItem() ?? 0) : 1 }} to {{ method_exists($items, 'lastItem') ? ($items->lastItem() ?? count($items)) : count($items) }} of {{ method_exists($items, 'total') ? $items->total() : count($items) }} entries
      </div>
      @if(method_exists($items, 'links'))
        <div>
          {{ $items->links() }}
        </div>
      @endif
    </div>

  </div>
</div>

{{-- ===================== MODAL FORM GENERATE PENGGAJIAN ===================== --}}
<div class="modal fade" id="modalGeneratePenggajian" tabindex="-1" aria-labelledby="modalGenerateLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      
      <form method="POST" action="{{ route('penggajian.preview') }}">
        @csrf
        
        <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-dark" id="modalGenerateLabel">
            <i class="bi bi-gear me-2 text-dark"></i>Form Generate Penggajian
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          
          {{-- Tipe Generate --}}
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark small mb-2">Tipe Generate <span class="text-danger">*</span></label>
            <div class="d-flex gap-4">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="tipe_generate" id="tipeSemua" value="semua" checked onchange="toggleKaryawanSelect()">
                <label class="form-check-input-label fw-medium text-dark" for="tipeSemua">
                  Semua Karyawan
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="tipe_generate" id="tipeSatu" value="satu" onchange="toggleKaryawanSelect()">
                <label class="form-check-input-label fw-medium text-dark" for="tipeSatu">
                  Satu Karyawan
                </label>
              </div>
            </div>
          </div>

          {{-- Dropdown Pilih Karyawan (Tampil jika memilih Satu Karyawan) --}}
          <div class="mb-3 d-none" id="containerPilihKaryawan">
            <label class="form-label fw-semibold text-dark small">Pilih Karyawan <span class="text-danger">*</span></label>
            <select name="karyawan_id" class="form-select rounded-3">
              <option value="">-- Pilih Karyawan --</option>
              @foreach($karyawans ?? [] as $k)
                <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->no_pegawai ?? '-' }})</option>
              @endforeach
            </select>
          </div>

          {{-- Input Bulan & Tahun --}}
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">Bulan <span class="text-danger">*</span></label>
              <select name="bulan" class="form-select rounded-3" required>
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                  </option>
                @endfor
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">Tahun <span class="text-danger">*</span></label>
              <select name="tahun" class="form-select rounded-3" required>
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                  <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
          </div>

          {{-- Alert Information Box --}}
          <div class="alert alert-info border-0 rounded-3 d-flex align-items-start gap-2 mb-0" style="background-color: #e0f2fe; color: #0369a1;">
            <i class="bi bi-info-circle fs-5 flex-shrink-0 mt-0.5"></i>
            <div class="small">
              Anda akan diarahkan ke halaman <strong>Preview Data</strong> di mana Anda bisa mengatur komponen tunjangan dan potongan sebelum data di-generate.
            </div>
          </div>

        </div>

        {{-- Footer Modal --}}
        <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end gap-2">
          <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-blue rounded-pill px-4 fw-medium">
            <i class="bi bi-eye me-1"></i> Preview Data
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
  function toggleKaryawanSelect() {
    const isSatu = document.getElementById('tipeSatu').checked;
    const container = document.getElementById('containerPilihKaryawan');
    if (isSatu) {
      container.classList.remove('d-none');
    } else {
      container.classList.add('d-none');
    }
  }

  function filterTable() {
    const input = document.getElementById('tableSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.searchable-row');
    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(input) ? '' : 'none';
    });
  }

  function changePerPage(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    window.location.href = url.toString();
  }
</script>

@endsection