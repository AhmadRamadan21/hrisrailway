<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Surat Cuti - {{ $item->karyawan->nama ?? 'Karyawan' }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 20mm 25mm;
    }
    body {
      font-family: 'Times New Roman', Times, serif;
      font-size: 14px;
      color: #000000;
      background-color: #ffffff;
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }
    .surat-header {
      text-align: center;
      border-bottom: 2px solid #000;
      padding-bottom: 10px;
      margin-bottom: 30px;
    }
    .surat-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .surat-header p {
      margin: 5px 0 0;
      font-size: 14px;
    }
    .surat-table {
      width: 100%;
      margin-bottom: 20px;
    }
    .surat-table td {
      padding: 6px 0;
      vertical-align: top;
    }
    .label-col {
      width: 180px;
      font-weight: bold;
    }
    .signature-section {
      width: 100%;
      margin-top: 60px;
      text-align: center;
    }
    .signature-box-left {
      float: left;
      width: 40%;
    }
    .signature-box-right {
      float: right;
      width: 40%;
    }
    .clearfix::after {
      content: "";
      clear: both;
      display: table;
    }
    .signature-name {
      margin-top: 80px;
      text-decoration: underline;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div class="surat-header">
    <h3>FORMULIR PENGAJUAN CUTI</h3>
    <p><strong>PT INTI BUMI PERKASA</strong><br>Jalan Mochamad Toha No 77 Bandung</p>
  </div>
  
  <p>Yang bertanda tangan di bawah ini:</p>
  
  <table class="surat-table">
    <tr>
      <td class="label-col">Nama</td>
      <td>: {{ $item->karyawan->nama ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label-col">NIK / No. Pegawai</td>
      <td>: {{ $item->karyawan->nik ?? $item->karyawan->no_pegawai ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label-col">Jabatan</td>
      <td>: {{ optional($item->karyawan->jabatan)->nama_jabatan ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label-col">Divisi</td>
      <td>: {{ optional($item->karyawan->divisi)->nama_divisi ?? '-' }}</td>
    </tr>
  </table>
  
  <p>Dengan ini mengajukan permohonan cuti selama <strong>{{ $item->durasi }} hari kerja</strong>, yang akan dilaksanakan pada:</p>
  
  <table class="surat-table">
    <tr>
      <td class="label-col">Tanggal Mulai</td>
      <td>: {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
      <td class="label-col">Tanggal Selesai</td>
      <td>: {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
      <td class="label-col">Keperluan / Alasan</td>
      <td>: {{ $item->alasan ?? '-' }}</td>
    </tr>
  </table>
  
  <p style="text-align: justify;">Demikian surat permohonan cuti ini saya buat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kebijaksanaannya, saya ucapkan terima kasih.</p>
  
  <div class="signature-section clearfix">
    <div class="signature-box-left">
      <p>
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
        <p style="text-decoration: underline; font-weight: bold; margin-top: 10px;">Anna Aulia</p>
      @else
        <p class="signature-name">( ......................................... )</p>
      @endif
    </div>
    
    <div class="signature-box-right">
      <p>Pemohon,<br>&nbsp;</p>
      <p class="signature-name">{{ $item->karyawan->nama ?? '( ......................................... )' }}</p>
    </div>
  </div>

</body>
</html>
