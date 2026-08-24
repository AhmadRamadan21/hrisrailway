@extends('layouts.app')

@section('content')
<style>
  .surat-box-wrapper {
    background: #ffffff;
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06);
    padding: 48px;
    max-width: 850px;
    margin: 0 auto;
    color: #1e293b;
  }
  
  .btn-blue {
    background-color: #6366f1;
    border-color: #6366f1;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    transition: all 0.2s;
  }
  .btn-blue:hover, .btn-blue:focus {
    background-color: #4f46e5;
    color: #ffffff;
    transform: translateY(-1px);
  }
  
  .surat-header {
    text-align: center;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 24px;
    margin-bottom: 32px;
  }
  
  .surat-body {
    line-height: 1.8;
  }
  
  .surat-table td {
    padding: 8px 16px 8px 0;
    vertical-align: top;
  }
  
  .signature-section {
    display: flex;
    justify-content: space-between;
    margin-top: 60px;
  }
  
  .signature-box {
    text-align: center;
    width: 250px;
  }
  
  @media print {
    body * {
      visibility: hidden;
    }
    .surat-box-wrapper, .surat-box-wrapper * {
      visibility: visible;
    }
    .surat-box-wrapper {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      max-width: 100%;
      box-shadow: none;
      padding: 0;
    }
    .no-print {
      display: none !important;
    }
  }
</style>

{{-- TOP BAR ACTIONS --}}
<div class="d-flex justify-content-between align-items-center mb-4 mt-2 mx-auto no-print" style="max-width: 850px;">
  <h3 class="fw-semibold mb-0" style="color: #334155;">Preview Surat Cuti</h3>
  <div class="d-flex gap-2">
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
      <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('cuti.pdf', $item) }}" class="btn btn-blue btn-sm rounded-pill px-3 fw-medium">
      <i class="bi bi-download me-1"></i> Download PDF
    </a>
    <button onclick="window.print()" class="btn btn-outline-blue btn-sm rounded-pill px-3 fw-medium">
      <i class="bi bi-printer me-1"></i> Cetak Surat
    </button>
  </div>
</div>

{{-- SURAT CONTENT --}}
<div class="surat-box-wrapper">
  <div class="surat-header">
    <h3 class="fw-bold mb-1" style="color: #0f172a;">FORMULIR PENGAJUAN CUTI</h3>
    <p class="mb-0 text-secondary"><strong>PT INTI BUMI PERKASA</strong><br>Jalan Mochamad Toha No 77 Bandung</p>
  </div>
  
  <div class="surat-body">
    <p>Yang bertanda tangan di bawah ini:</p>
    
    <table class="surat-table mb-4">
      <tr>
        <td style="width: 150px;" class="fw-semibold">Nama</td>
        <td>: {{ $item->karyawan->nama ?? '-' }}</td>
      </tr>
      <tr>
        <td class="fw-semibold">NIK / No. Pegawai</td>
        <td>: {{ $item->karyawan->nik ?? $item->karyawan->no_pegawai ?? '-' }}</td>
      </tr>
      <tr>
        <td class="fw-semibold">Jabatan</td>
        <td>: {{ optional($item->karyawan->jabatan)->nama_jabatan ?? '-' }}</td>
      </tr>
      <tr>
        <td class="fw-semibold">Divisi</td>
        <td>: {{ optional($item->karyawan->divisi)->nama_divisi ?? '-' }}</td>
      </tr>
    </table>
    
    <p>Dengan ini mengajukan permohonan cuti selama <strong>{{ $item->durasi }} hari kerja</strong>, yang akan dilaksanakan pada:</p>
    
    <table class="surat-table mb-4">
      <tr>
        <td style="width: 150px;" class="fw-semibold">Tanggal Mulai</td>
        <td>: {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
      </tr>
      <tr>
        <td class="fw-semibold">Tanggal Selesai</td>
        <td>: {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y') }}</td>
      </tr>
      <tr>
        <td class="fw-semibold">Keperluan / Alasan</td>
        <td>: {{ $item->alasan ?? '-' }}</td>
      </tr>
    </table>
    
    <p>Demikian surat permohonan cuti ini saya buat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kebijaksanaannya, saya ucapkan terima kasih.</p>
    
    <div class="signature-section">
      <div class="signature-box">
        <p class="mb-2">
          @if($item->status == 'Disetujui')
            <s>Ditolak</s> / Disetujui
          @elseif($item->status == 'Ditolak')
            Ditolak / <s>Disetujui</s>
          @else
            Ditolak / Disetujui
          @endif
          <br><strong>Atasan / HRD</strong>
        </p>
        
        <div style="height: 70px;"></div>
        
        @if($item->status == 'Disetujui' || $item->status == 'Ditolak')
          <p class="mb-0 text-decoration-underline fw-bold">Anna Aulia</p>
        @else
          <p class="mb-0 fw-bold">( ......................................... )</p>
        @endif
      </div>
      
      <div class="signature-box">
        <p class="mb-5">Pemohon,<br>&nbsp;</p>
        <p class="mb-0 text-decoration-underline fw-bold">{{ $item->karyawan->nama ?? '( ......................................... )' }}</p>
      </div>
    </div>
    
  </div>
</div>
@endsection
