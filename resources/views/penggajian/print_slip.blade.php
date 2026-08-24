<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Slip Gaji - {{ $penggajian->karyawan->nama ?? 'Karyawan' }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 20mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
      color: #000000;
      background-color: #ffffff;
      margin: 0;
      padding: 20px;
      line-height: 1.4;
    }
    .container {
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
    }
    @media print {
      .no-print { display: none !important; }
      body { padding: 0; }
    }
  </style>
</head>
<body>

@if(empty($isPdf))
  <div class="no-print" style="margin-bottom: 20px; text-align: right; max-width: 800px; margin-left: auto; margin-right: auto;">
    <button onclick="window.print()" style="background: #0f172a; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: bold; cursor: pointer;">
      <i class="bi bi-printer"></i> Cetak / Save PDF
    </button>
  </div>
@endif

  <div class="container">
    @include('penggajian.print_slip_content', ['penggajian' => $penggajian])
  </div>

</body>
</html>
