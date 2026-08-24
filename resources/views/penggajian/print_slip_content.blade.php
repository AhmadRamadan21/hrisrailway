@php
  if (!function_exists('terbilang_slip')) {
      function terbilang_slip($angka) {
          $angka = abs((float)$angka);
          $baca = array('', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas');
          $terbilang = '';
          
          if ($angka < 12) {
              $terbilang = ' ' . $baca[(int)$angka];
          } else if ($angka < 20) {
              $terbilang = terbilang_slip($angka - 10) . ' Belas';
          } else if ($angka < 100) {
              $terbilang = terbilang_slip(floor($angka / 10)) . ' Puluh' . terbilang_slip($angka % 10);
          } else if ($angka < 200) {
              $terbilang = ' Seratus' . terbilang_slip($angka - 100);
          } else if ($angka < 1000) {
              $terbilang = terbilang_slip(floor($angka / 100)) . ' Ratus' . terbilang_slip($angka % 100);
          } else if ($angka < 2000) {
              $terbilang = ' Seribu' . terbilang_slip($angka - 1000);
          } else if ($angka < 1000000) {
              $terbilang = terbilang_slip(floor($angka / 1000)) . ' Ribu' . terbilang_slip(fmod($angka, 1000));
          } else if ($angka < 1000000000) {
              $terbilang = terbilang_slip(floor($angka / 1000000)) . ' Juta' . terbilang_slip(fmod($angka, 1000000));
          } else if ($angka < 1000000000000) {
              $terbilang = terbilang_slip(floor($angka / 1000000000)) . ' Miliar' . terbilang_slip(fmod($angka, 1000000000));
          } else {
              $terbilang = terbilang_slip(floor($angka / 1000000000000)) . ' Triliun' . terbilang_slip(fmod($angka, 1000000000000));
          }
          return trim($terbilang);
      }
  }

  $karyawan = $penggajian->karyawan;
  $gajiPokok = $penggajian->gaji_pokok ?? 0;
  $makanTransport = $penggajian->makan_transport ?? 760000;
  $jumlahGaji = $gajiPokok + $makanTransport;
  
  $tjJabatan = $penggajian->tj_jabatan ?? 100000000;
  $bonus = $penggajian->bonus ?? 15000000;
  $thr = $penggajian->thr ?? 0;
  $jumlahTunjanganTdkTetap = $tjJabatan + $bonus + $thr;
  
  $rapel = 0;
  $gajiKotor = $jumlahGaji + $jumlahTunjanganTdkTetap + $rapel;
  
  $potBpjs = $penggajian->pot_bpjs ?? ($gajiPokok * 0.04);
  $potDplk = $penggajian->pot_dplk ?? 0;
  $potKoperasi = $penggajian->pot_koperasi ?? 0;
  $potAbsensi = $penggajian->pot_absensi ?? 0;
  $potLainnya = $penggajian->pot_lainnya ?? 0;
  $jumlahPotongan = $potBpjs + $potDplk + $potKoperasi + $potAbsensi + $potLainnya;
  
  $gajiBersih = $penggajian->total ?? ($gajiKotor - $jumlahPotongan);
  $terbilangText = terbilang_slip($gajiBersih);
@endphp

<div class="slip-content-body">
  {{-- COMPANY HEADER --}}
  <div style="text-align: center; margin-bottom: 12px;">
    <h2 style="margin: 0; font-size: 17px; font-weight: bold; letter-spacing: 0.5px;">PT INTI BUMI PERKASA</h2>
    <p style="margin: 3px 0 0 0; font-size: 13px;">Jalan Mochamad Toha No 77 bandung</p>
  </div>

  {{-- EMPLOYEE METADATA GRID --}}
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
    <tr>
      <td style="width: 12%; padding: 3px 0;">Nama</td>
      <td style="width: 38%; padding: 3px 0;">: <strong>{{ strtoupper($karyawan->nama ?? '-') }}</strong></td>
      <td style="width: 12%; padding: 3px 0;">Bagian</td>
      <td style="width: 38%; padding: 3px 0;">: {{ strtoupper(optional($karyawan->divisi)->nama_divisi ?? 'OPERASIONAL') }}</td>
    </tr>
    <tr>
      <td style="padding: 3px 0;">Jabatan</td>
      <td style="padding: 3px 0;">: {{ strtoupper(optional($karyawan->jabatan)->nama_jabatan ?? 'STAF') }}</td>
      <td style="padding: 3px 0;">Lokasi</td>
      <td style="padding: 3px 0;">: BANDUNG</td>
    </tr>
  </table>

  <div style="border-top: 1.5px solid #000; margin: 10px 0 15px 0;"></div>

  {{-- TITLE --}}
  <div style="text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px; letter-spacing: 0.5px; text-transform: uppercase;">
    RINCIAN GAJI BULAN {{ strtoupper(\Carbon\Carbon::createFromDate($penggajian->tahun, $penggajian->bulan, 1)->translatedFormat('F Y')) }}
  </div>

  {{-- 2-COLUMN SALARY & DEDUCTION DETAILS --}}
  <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
    <tr>
      {{-- LEFT COLUMN: EARNINGS & TUNJANGAN --}}
      <td style="width: 52%; padding-right: 25px; vertical-align: top;">
        <table style="width: 100%; border-collapse: collapse;">
          <tr>
            <td style="font-weight: bold; width: 50%;">GAJI POKOK</td>
            <td style="width: 8%;">: Rp.</td>
            <td style="text-align: right; font-weight: bold; width: 42%;">{{ number_format($gajiPokok, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="font-weight: bold;" colspan="3">FASILITAS</td>
          </tr>
          <tr>
            <td style="padding-left: 15px;">- MAKAN & TRANSPORTASI</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($makanTransport, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">JUMLAH GAJI</td>
            <td style="font-weight: bold;">: Rp.</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($jumlahGaji, 2, ',', '.') }}</td>
          </tr>
          <tr><td colspan="3" style="height: 6px;"></td></tr>
          <tr>
            <td style="font-weight: bold;" colspan="3">TUNJANGAN TDK TETAP</td>
          </tr>
          <tr>
            <td style="padding-left: 15px;">- JABATAN</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($tjJabatan, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="padding-left: 15px;">- KOMUNIKASI</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($bonus, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="padding-left: 15px;">- THR</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($thr, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">JUMLAH TUNJANGAN TDK TETAP</td>
            <td style="font-weight: bold;">: Rp.</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($jumlahTunjanganTdkTetap, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">RAPEL/INSENTIF</td>
            <td style="font-weight: bold;">: Rp.</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($rapel, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">GAJI KOTOR</td>
            <td style="font-weight: bold;">: Rp.</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($gajiKotor, 2, ',', '.') }}</td>
          </tr>
        </table>
      </td>

      {{-- RIGHT COLUMN: DEDUCTIONS --}}
      <td style="width: 48%; vertical-align: top;">
        <table style="width: 100%; border-collapse: collapse;">
          <tr>
            <td style="font-weight: bold;" colspan="3">POTONGAN</td>
          </tr>
          <tr>
            <td style="width: 45%;">BPJS</td>
            <td style="width: 8%;">: Rp.</td>
            <td style="text-align: right; width: 47%;">{{ number_format($potBpjs, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td>DPLK</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($potDplk, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td>KOPERASI</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($potKoperasi, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td>ABSENSI</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($potAbsensi, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td>LAIN-LAIN</td>
            <td>: Rp.</td>
            <td style="text-align: right;">{{ number_format($potLainnya, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">JUMLAH POTONGAN</td>
            <td style="font-weight: bold;">: Rp.</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($jumlahPotongan, 2, ',', '.') }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <div style="margin-top: 25px; margin-bottom: 20px;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
      <tr>
        <td style="font-weight: bold; width: 25%;">GAJI BERSIH</td>
        <td style="font-weight: bold; width: 75%;">: Rp. {{ number_format($gajiBersih, 2, ',', '.') }}</td>
      </tr>
    </table>
  </div>

  {{-- TERBILANG --}}
  <div style="margin-top: 25px; font-size: 13px;">
    <div style="font-style: italic; margin-bottom: 2px;">Terbilang:</div>
    <div style="font-style: italic; font-weight: 500;">{{ $terbilangText }}</div>
  </div>
</div>
