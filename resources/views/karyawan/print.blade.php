<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Data Karyawan</title>
  <style>
    * { font-family: 'Times New Roman', Times, serif; box-sizing: border-box; }
    body { margin: 0; padding: 20px; color: #000; }
    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
    .header h2 { margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
    .header p { margin: 5px 0 0; font-size: 14px; }
    h3 { text-align: center; margin-bottom: 5px; font-size: 16px; font-weight: bold; }
    p.sub { text-align: center; color: #333; margin-top: 0; margin-bottom: 20px; font-size: 13px; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th, td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: top; }
    th { background: #f0f0f0; font-weight: bold; text-align: center; }
    .text-center { text-align: center; }
  </style>
</head>
<body>

  <div class="header">
    <h2>PT INTI BUMI PERKASA</h2>
    <p>Jalan Mochamad Toha No 77 Bandung</p>
  </div>

  <h3>LAPORAN DATA KARYAWAN</h3>
  <p class="sub">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</p>

  <table>
    <thead>
      <tr>
        <th style="width: 20px;">No</th>
        <th style="width: 55px;">No Pegawai</th>
        <th>Nama Lengkap</th>
        <th>Email</th>
        <th>No HP</th>
        <th style="width: 60px;">Tgl Lahir</th>
        <th>Divisi</th>
        <th>Jabatan</th>
        <th style="width: 120px;">Alamat</th>
        <th style="width: 40px;">Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($karyawans as $i => $k)
        <tr>
          <td class="text-center">{{ $i + 1 }}</td>
          <td class="text-center">{{ $k->no_pegawai ?? '-' }}</td>
          <td>{{ $k->nama }}</td>
          <td>{{ $k->email }}</td>
          <td>{{ $k->no_hp ?? '-' }}</td>
          <td>{{ $k->tanggal_lahir ? $k->tanggal_lahir->format('d/m/Y') : '-' }}</td>
          <td>{{ $k->divisi->nama_divisi ?? '-' }}</td>
          <td>{{ $k->jabatan->nama_jabatan ?? '-' }}</td>
          <td>{{ $k->alamat ?? '-' }}</td>
          <td class="text-center">{{ $k->status }}</td>
        </tr>
      @empty
        <tr><td colspan="10" class="text-center">Belum ada data karyawan.</td></tr>
      @endforelse
    </tbody>
  </table>

</body>
</html>