@extends('layouts.app')

@section('content')

{{-- STYLES KHUSUS PREVIEW PENGGAJIAN --}}
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

  /* Custom Theme Palette */
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

  /* Input Styling */
  .input-preview {
    font-size: 13.5px;
    border-radius: 8px;
    padding: 8px 12px;
    text-align: right;
    transition: all 0.2s;
  }
  
  .input-tunjangan {
    border: 1px solid #e2e8f0;
    background-color: #f8fafc;
  }
  .input-tunjangan:focus {
    border-color: #6366f1;
    background-color: #ffffff;
    box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
  }
  
  .input-potongan {
    border: 1px solid #fecaca;
    background-color: #fef2f2;
    color: #b91c1c;
  }
  .input-potongan:focus {
    border-color: #ef4444;
    background-color: #ffffff;
    box-shadow: 0 0 0 0.25rem rgba(239, 68, 68, 0.15);
  }

  .table-preview th {
    font-size: 13.5px;
    font-weight: 600;
    color: #475569;
    white-space: nowrap;
    vertical-align: middle;
    background-color: #f9fafb;
    border-bottom: 2px solid #f3f4f6;
  }
</style>

{{-- HEADER HALAMAN & TOMBOL KEMBALI --}}
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
  <h3 class="fw-semibold mb-0" style="color: #334155;">Preview Data Penggajian</h3>
  <a href="{{ route('penggajian.index') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-medium">
    <i class="bi bi-arrow-left me-1"></i> Kembali
  </a>
</div>

{{-- PANEL UTAMA --}}
<div class="panel">
  <div class="panel-body">
    
    {{-- SUB-HEADER PERIODE & JUMLAH KARYAWAN --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="fw-semibold text-secondary mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-calendar3 text-primary"></i> Periode: {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y') }}
      </h5>
      <span class="badge px-3 py-2 rounded-pill fw-medium fs-6" style="background-color: #e0e7ff; color: #4f46e5;">
        {{ count($karyawans) }} Karyawan
      </span>
    </div>

    {{-- INFORMASI ALERT --}}
    <div class="alert alert-info border-0 rounded-3 d-flex align-items-start gap-2 mb-4" style="background-color: #e0f2fe; color: #0369a1;">
      <i class="bi bi-info-circle-fill fs-5 mt-n1"></i>
      <div class="small lh-base">
        Silakan tinjau dan sesuaikan komponen tunjangan dan potongan di bawah ini. <strong>BPJS (Kesehatan 1% & TK 3%)</strong> akan dihitung secara otomatis berdasarkan Gaji Pokok saat Anda menekan tombol Generate Final.
      </div>
    </div>

    {{-- FORM PREVIEW UNTUK EDIT NILAI BEFORE GENERATE FINAL --}}
    <form method="POST" action="{{ route('penggajian.store') }}">
      @csrf
      <input type="hidden" name="bulan" value="{{ $bulan }}">
      <input type="hidden" name="tahun" value="{{ $tahun }}">

      {{-- TABEL PREVIEW DATA --}}
      <div class="table-responsive">
        <table class="table table-hover align-middle table-preview mb-0">
          <thead>
            <tr>
              <th style="width: 40px;">No</th>
              <th>Karyawan</th>
              <th>Gaji Pokok</th>
              <th>Makan & Transport</th>
              <th>Tj. Jabatan</th>
              <th>Komunikasi/Bonus</th>
              <th>THR</th>
              <th>Pot. DPLK</th>
              <th>Pot. Koperasi</th>
              <th>Pot. Absensi</th>
              <th>Pot. Lainnya</th>
            </tr>
          </thead>
          <tbody>
            @forelse($karyawans as $index => $karyawan)
              @php
                $existing = $existingGaji[$karyawan->id] ?? null;
                $gajiPokok = $existing->gaji_pokok ?? $karyawan->gaji_pokok ?? 3977678;
                // Sample default values for initial preview matching company standards
                $makanTransport = $existing ? 760000 : 760000;
                $tjJabatan = $existing ? 100000000 : 100000000;
                $bonus = $existing ? 15000000 : 15000000;
                $thr = $existing ? 0 : 0;
              @endphp
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                  <div class="fw-bold text-dark text-nowrap">{{ strtoupper($karyawan->nama) }}</div>
                  <small class="text-muted">{{ $karyawan->no_pegawai ?? $karyawan->nik ?? ('20190100' . ($index + 1)) }}</small>
                  <input type="hidden" name="gaji[{{ $index }}][karyawan_id]" value="{{ $karyawan->id }}">
                </td>
                <td style="min-width: 140px;">
                  <input type="number" name="gaji[{{ $index }}][gaji_pokok]" class="form-control form-control-sm input-preview border-primary fw-bold text-primary" value="{{ $gajiPokok }}">
                </td>
                
                {{-- INPUT TUNJANGAN --}}
                <td style="min-width: 130px;">
                  <input type="number" name="gaji[{{ $index }}][makan_transport]" class="form-control form-control-sm input-preview input-tunjangan" value="{{ $makanTransport }}">
                </td>
                <td style="min-width: 130px;">
                  <input type="number" name="gaji[{{ $index }}][tj_jabatan]" class="form-control form-control-sm input-preview input-tunjangan" value="{{ $tjJabatan }}">
                </td>
                <td style="min-width: 130px;">
                  <input type="number" name="gaji[{{ $index }}][bonus]" class="form-control form-control-sm input-preview input-tunjangan" value="{{ $bonus }}">
                </td>
                <td style="min-width: 130px;">
                  <input type="number" name="gaji[{{ $index }}][thr]" class="form-control form-control-sm input-preview input-tunjangan" value="{{ $thr }}">
                </td>

                {{-- INPUT POTONGAN (BORDER MERAH) --}}
                <td style="min-width: 110px;">
                  <input type="number" name="gaji[{{ $index }}][pot_dplk]" class="form-control form-control-sm input-preview input-potongan" value="0">
                </td>
                <td style="min-width: 110px;">
                  <input type="number" name="gaji[{{ $index }}][pot_koperasi]" class="form-control form-control-sm input-preview input-potongan" value="0">
                </td>
                <td style="min-width: 110px;">
                  <input type="number" name="gaji[{{ $index }}][pot_absensi]" class="form-control form-control-sm input-preview input-potongan" value="0">
                </td>
                <td style="min-width: 110px;">
                  <input type="number" name="gaji[{{ $index }}][pot_lainnya]" class="form-control form-control-sm input-preview input-potongan" value="0">
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="11" class="text-center text-muted py-4">Tidak ada data karyawan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- FOOTER ACTION DI BAWAH TABEL --}}
      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('penggajian.index') }}" class="btn btn-light btn-md px-4 py-2 rounded-pill fw-medium text-secondary">
          Batal
        </a>
        <button type="submit" class="btn btn-blue btn-md px-4 py-2 rounded-pill fw-medium">
          <i class="bi bi-check2-circle me-1"></i> Generate Final
        </button>
      </div>

    </form>
  </div>
</div>

@endsection