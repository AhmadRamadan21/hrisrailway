<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Karyawan;
use Carbon\Carbon;

class AiChatController extends Controller
{
    public function index()
    {
        $pageTitle = 'AI HRIS';
        return view('ai_chat.index', compact('pageTitle'));
    }

    private function getLinkedKaryawan($user)
    {
        if (!$user) return null;

        try {
            $linked = \App\Models\PenggunaKaryawan::where('username', $user->username)->with('karyawan')->first();
            if ($linked && $linked->karyawan) {
                return $linked->karyawan;
            }
        } catch (\Throwable $e) {}

        $karyawan = Karyawan::where('email', $user->email)
            ->orWhere('nama', $user->name)
            ->orWhere('no_pegawai', $user->username)
            ->first();

        if ($karyawan && !empty($user->username)) {
            try {
                \App\Models\PenggunaKaryawan::firstOrCreate(
                    ['karyawan_id' => $karyawan->id],
                    [
                        'username' => $user->username,
                        'role'     => $user->hasRole(['Admin', 'Super Admin']) ? 'Admin' : 'User'
                    ]
                );
            } catch (\Throwable $e) {}
        }

        return $karyawan;
    }

    private function parseDateFromMessage($msg)
    {
        $msg = strtolower($msg);

        if (str_contains($msg, 'kemarin')) {
            return Carbon::yesterday();
        }
        if (str_contains($msg, 'hari ini')) {
            return Carbon::today();
        }
        if (preg_match('/(\d+)\s*hari\s*lalu/', $msg, $m)) {
            return Carbon::today()->subDays((int)$m[1]);
        }

        $months = [
            'januari' => 1, 'jan' => 1,
            'februari' => 2, 'feb' => 2,
            'maret' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5,
            'juni' => 6, 'jun' => 6,
            'juli' => 7, 'jul' => 7,
            'agustus' => 8, 'agus' => 8, 'ags' => 8,
            'september' => 9, 'sep' => 9,
            'oktober' => 10, 'okt' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12,
        ];

        foreach ($months as $name => $num) {
            if (preg_match('/(?:tanggal\s*)?(\d{1,2})\s*' . $name . '(?:\s*(\d{4}))?/', $msg, $m)) {
                $day = (int)$m[1];
                $year = !empty($m[2]) ? (int)$m[2] : Carbon::today()->year;
                try {
                    return Carbon::createFromDate($year, $num, $day)->startOfDay();
                } catch (\Throwable $e) {}
            }
        }

        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $msg, $m)) {
            return Carbon::createFromDate($m[1], $m[2], $m[3])->startOfDay();
        }
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $msg, $m)) {
            return Carbon::createFromDate($m[3], $m[2], $m[1])->startOfDay();
        }

        return null;
    }

    private function parseMonthFromMessage($msg)
    {
        $msg = strtolower($msg);

        if (str_contains($msg, 'bulan lalu')) {
            $dt = Carbon::now()->subMonth();
            return ['bulan' => $dt->month, 'tahun' => $dt->year, 'nama' => $dt->translatedFormat('F Y')];
        }
        if (str_contains($msg, 'bulan ini')) {
            $dt = Carbon::now();
            return ['bulan' => $dt->month, 'tahun' => $dt->year, 'nama' => $dt->translatedFormat('F Y')];
        }

        $months = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        foreach ($months as $name => $num) {
            if (str_contains($msg, $name)) {
                $year = Carbon::now()->year;
                if (preg_match('/' . $name . '\s*(\d{4})/', $msg, $m)) {
                    $year = (int)$m[1];
                }
                return ['bulan' => $num, 'tahun' => $year, 'nama' => ucfirst($name) . " " . $year];
            }
        }

        return null;
    }

    private function parseKaryawanFromMessage($msg)
    {
        $karyawans = Karyawan::all();
        $msgLower = strtolower($msg);
        
        foreach ($karyawans as $k) {
            $namaLower = strtolower($k->nama);
            if (str_contains($msgLower, $namaLower)) {
                return $k;
            }
            
            // Cek dengan nama depan saja
            $namaParts = explode(' ', $namaLower);
            $namaDepan = $namaParts[0];
            if (count($namaParts) > 1 && strlen($namaDepan) > 2 && str_contains($msgLower, $namaDepan)) {
                return $k;
            }
        }
        return null;
    }

    public function processMessage(Request $request)
    {
        $user = auth()->user();
        $message = trim($request->input('message', ''));

        if (empty($message)) {
            return response()->json([
                'success' => false,
                'reply' => 'Silakan ketikkan pertanyaan atau topik yang ingin Anda tanyakan.'
            ]);
        }

        // Ambil data karyawan pengakses dengan pencocokan cerdas
        $karyawan = $this->getLinkedKaryawan($user);
        $namaPanggilan = $karyawan ? $karyawan->nama : $user->name;

        // Ambil presensi hari ini
        $todayAbsensi = null;
        if ($karyawan) {
            $todayAbsensi = Absensi::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', Carbon::today())
                ->first();
        }

        $lowerMsg = strtolower($message);
        $reply = '';

        // 0. PENCARIAN DINAMIS DI TABEL KNOWLEDGE BASE AI (INPUTAN ADMIN)
        try {
            $dynamicKnowledges = \App\Models\AiKnowledgeBase::where('is_active', true)->latest()->get();
            foreach ($dynamicKnowledges as $item) {
                $keywords = array_filter(array_map('trim', explode(',', strtolower($item->keywords))));
                foreach ($keywords as $kw) {
                    if (!empty($kw) && str_contains($lowerMsg, $kw)) {
                        $jawabanText = str_replace(['{nama}', '{user}'], [$namaPanggilan, $user->name], $item->jawaban);
                        return response()->json([
                            'success' => true,
                            'reply' => $jawabanText,
                            'user_name' => $namaPanggilan,
                            'time' => Carbon::now()->format('H:i')
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback ke logika bawaan jika tabel belum siap
        }

        // 1. QUERY ANALITIK REKAPAN BULANAN (HISTORICAL MONTHLY RECAP)
        $parsedMonth = $this->parseMonthFromMessage($lowerMsg);
        if ($parsedMonth && $this->parseDateFromMessage($lowerMsg) === null && (str_contains($lowerMsg, 'rekap') || str_contains($lowerMsg, 'absen') || str_contains($lowerMsg, 'presensi') || str_contains($lowerMsg, 'laporan'))) {
            $m = $parsedMonth['bulan'];
            $y = $parsedMonth['tahun'];
            $namaBulan = $parsedMonth['nama'];

            $queryMonth = Absensi::whereMonth('tanggal', $m)->whereYear('tanggal', $y);
            $totalAbsen = (clone $queryMonth)->count();
            $tepatWaktu = (clone $queryMonth)->where('status', 'Hadir')->count();
            $totalTelat = (clone $queryMonth)->where('status', 'Telat')->count();
            $totalHadir = $tepatWaktu + $totalTelat;
            $totalAlpa = (clone $queryMonth)->where('status', 'Alpa')->count();

            $reply = "📊 **Rekapitulasi Presensi Bulan {$namaBulan}:**\n\n" .
                     "- Total Catatan Absensi: **{$totalAbsen} data**\n" .
                     "- 🟢 Total Hadir: **{$totalHadir} kali** *(Tepat Waktu: {$tepatWaktu}, Telat: {$totalTelat})*\n" .
                     "- 🔴 Status Alpa: **{$totalAlpa} kali**\n\n" .
                     "💡 *Rincian per karyawan dapat difilter di menu **[Absensi]**.*";
        }

        // 2. QUERY ANALITIK: KARYAWAN TELAT (HISTORICAL & REALTIME)
        elseif (str_contains($lowerMsg, 'telat') || str_contains($lowerMsg, 'terlambat')) {
            $targetDate = $this->parseDateFromMessage($lowerMsg) ?? Carbon::today();
            $labelDate = $targetDate->isToday() ? "Hari Ini (" . $targetDate->format('d M Y') . ")" : $targetDate->format('d M Y');

            $telatList = Absensi::with(['karyawan.divisi'])
                ->whereDate('tanggal', $targetDate)
                ->where('status', 'Telat')
                ->get();

            if ($telatList->count() > 0) {
                $reply = "⏰ **Daftar Karyawan Telat Tanggal {$labelDate}:**\n\n";
                foreach ($telatList as $i => $item) {
                    $nama = $item->karyawan?->nama ?? 'Karyawan';
                    $divisi = $item->karyawan?->divisi?->nama_divisi ?? '-';
                    $jam = $item->masuk ? Carbon::parse($item->masuk)->format('H:i') : '-';
                    $reply .= ($i + 1) . ". **{$nama}** ({$divisi}) - Jam Presensi: **{$jam} WIB**\n";
                }
                $reply .= "\nTotal karyawan telat: **" . $telatList->count() . " orang**.";
            } else {
                $reply = "🎉 **Kabar Baik!** Pada tanggal {$labelDate} **tidak ada karyawan yang terlambat**.";
            }
        }

        // 3. QUERY ANALITIK: KARYAWAN TIDAK MASUK / ALPA (HISTORICAL & REALTIME)
        elseif (str_contains($lowerMsg, 'tidak masuk') || str_contains($lowerMsg, 'alpa') || str_contains($lowerMsg, 'tidak hadir') || str_contains($lowerMsg, 'belum absen') || str_contains($lowerMsg, 'belum presensi')) {
            $targetDate = $this->parseDateFromMessage($lowerMsg) ?? Carbon::today();
            $labelDate = $targetDate->isToday() ? "Hari Ini (" . $targetDate->format('d M Y') . ")" : $targetDate->format('d M Y');

            $absenIds = Absensi::whereDate('tanggal', $targetDate)->pluck('karyawan_id')->toArray();
            $belumAbsenList = Karyawan::with('divisi')->whereNotIn('id', $absenIds)->get();
            $alpaList = Absensi::with('karyawan.divisi')->whereDate('tanggal', $targetDate)->where('status', 'Alpa')->get();

            $reply = "📌 **Data Karyawan Tidak Masuk / Belum Presensi Tanggal {$labelDate}:**\n\n";

            if ($alpaList->count() > 0) {
                $reply .= "🔴 **Status Alpa (" . $alpaList->count() . " orang):**\n";
                foreach ($alpaList as $i => $item) {
                    $reply .= "- **" . ($item->karyawan?->nama ?? 'Karyawan') . "** (" . ($item->karyawan?->divisi?->nama_divisi ?? '-') . ")\n";
                }
                $reply .= "\n";
            }

            if ($belumAbsenList->count() > 0) {
                $reply .= "🟡 **Belum Melakukan Scan QR / Absen (" . $belumAbsenList->count() . " orang):**\n";
                foreach ($belumAbsenList as $i => $item) {
                    $reply .= "- **{$item->nama}** (" . ($item->divisi?->nama_divisi ?? '-') . ")\n";
                }
            } else {
                $reply .= "✅ Semua karyawan yang terdaftar telah melakukan presensi pada tanggal tersebut.";
            }
        }

        // 4. QUERY ANALITIK: REKAPAN ABSENSI HARI / TANGGAL TERTENTU ATAU KARYAWAN TERTENTU
        elseif ((str_contains($lowerMsg, 'rekap') || str_contains($lowerMsg, 'absen') || str_contains($lowerMsg, 'presensi')) && $this->parseDateFromMessage($lowerMsg) !== null) {
            $targetDate = $this->parseDateFromMessage($lowerMsg);
            $labelDate = $targetDate->format('d M Y');
            
            $targetKaryawan = null;
            if ($user->hasRole(['Admin', 'Super Admin'])) {
                $targetKaryawan = $this->parseKaryawanFromMessage($lowerMsg);
            } else {
                $targetKaryawan = $karyawan; // User biasa hanya bisa lihat miliknya sendiri
            }

            if ($targetKaryawan) {
                // Absensi Karyawan Spesifik
                $absensi = Absensi::where('karyawan_id', $targetKaryawan->id)->whereDate('tanggal', $targetDate)->first();
                if ($absensi) {
                    $jam = $absensi->masuk ? Carbon::parse($absensi->masuk)->format('H:i') : '-';
                    $reply = "📌 **Data Presensi {$targetKaryawan->nama} ({$labelDate})**\n\n";
                    $reply .= "- Status: **{$absensi->status}**\n";
                    $reply .= "- Jam Masuk: **{$jam} WIB**\n";
                    if ($absensi->distance !== null) {
                        $reply .= "- Jarak ke Kantor: **{$absensi->distance} meter**\n";
                    }
                } else {
                    $reply = "Karyawan **{$targetKaryawan->nama}** tidak memiliki catatan presensi (Belum Absen) pada tanggal {$labelDate}.";
                }
            } else {
                // Rekap Keseluruhan Karyawan
                $queryDate = Absensi::with(['karyawan.divisi'])->whereDate('tanggal', $targetDate);
                $totalAbsen = (clone $queryDate)->count();
                $tepatWaktu = (clone $queryDate)->where('status', 'Hadir')->count();
                $countTelat = (clone $queryDate)->where('status', 'Telat')->count();
                $countHadir = $tepatWaktu + $countTelat;
                $countAlpa = (clone $queryDate)->where('status', 'Alpa')->count();

                $reply = "📊 **Rekapitulasi Presensi Tanggal {$labelDate}:**\n\n" .
                         "- Total Presensi Terdaftar: **{$totalAbsen} orang**\n" .
                         "- 🟢 Total Hadir: **{$countHadir} orang** *(Tepat Waktu: {$tepatWaktu}, Telat: {$countTelat})*\n" .
                         "- 🔴 Status Alpa: **{$countAlpa} orang**\n\n" .
                         "💡 *Ketik 'siapa yang telat tanggal {$labelDate}' untuk rincian nama karyawan.*";
            }
        }

        // 3. QUERY ANALITIK: KARYAWAN BERDASARKAN DIVISI & DAFTAR DIVISI
        elseif (str_contains($lowerMsg, 'divisi')) {
            $divisis = \App\Models\Divisi::all();
            $matchedDivisi = null;
            foreach ($divisis as $d) {
                if (str_contains($lowerMsg, strtolower($d->nama_divisi))) {
                    $matchedDivisi = $d;
                    break;
                }
            }

            if ($matchedDivisi) {
                $members = Karyawan::with('jabatan')->where('divisi_id', $matchedDivisi->id)->get();
                $reply = "🏢 **Daftar Karyawan Divisi {$matchedDivisi->nama_divisi}:**\n\n";
                if ($members->count() > 0) {
                    foreach ($members as $i => $k) {
                        $jabatan = $k->jabatan?->nama_jabatan ?? '-';
                        $reply .= ($i + 1) . ". **{$k->nama}** - Jabatan: **{$jabatan}** (Email: {$k->email})\n";
                    }
                    $reply .= "\nTotal anggota: **" . $members->count() . " orang**.";
                } else {
                    $reply .= "Belum ada karyawan yang terdaftar di divisi {$matchedDivisi->nama_divisi}.";
                }
            } else {
                $reply = "🏢 **Daftar Divisi di PT Inti Bumi Perkasa:**\n\n";
                foreach ($divisis as $i => $d) {
                    $count = Karyawan::where('divisi_id', $d->id)->count();
                    $reply .= ($i + 1) . ". **Divisi {$d->nama_divisi}** - Total: **{$count} Karyawan**\n";
                }
                $reply .= "\n💡 *Tips: Ketik 'karyawan divisi IT' atau nama divisi lainnya untuk melihat anggota spesifik.*";
            }
        }

        // 4. QUERY ANALITIK: REKAP DATA PENGGAJIAN PERUSAHAAN
        elseif (str_contains($lowerMsg, 'data penggajian') || str_contains($lowerMsg, 'rekap gaji') || str_contains($lowerMsg, 'total gaji') || str_contains($lowerMsg, 'gaji bulan ini')) {
            $totalGaji = \App\Models\Penggajian::sum('total');
            $countPayroll = \App\Models\Penggajian::count();
            $paidPayroll = \App\Models\Penggajian::where('status', 'Dibayar')->orWhere('status', 'Paid')->count();
            $pendingPayroll = \App\Models\Penggajian::where('status', 'Draft')->orWhere('status', 'Pending')->count();

            $reply = "💰 **Ringkasan Data Penggajian Perusahaan:**\n\n" .
                     "- Total Records Payroll: **{$countPayroll} data**\n" .
                     "- Total Nominal Gaji Terdata: **Rp " . number_format($totalGaji, 0, ',', '.') . "**\n" .
                     "- Status Dibayar: **{$paidPayroll} data**\n" .
                     "- Status Pending/Draft: **{$pendingPayroll} data**\n\n" .
                     "💡 *Rincian lengkap dan cetak slip gaji dapat diakses di menu **[Penggajian]**.*";
        }

        // 5. QUERY ANALITIK: KARYAWAN SEDANG CUTI HARI INI
        elseif (str_contains($lowerMsg, 'sedang cuti') || str_contains($lowerMsg, 'cuti hari ini') || str_contains($lowerMsg, 'siapa yang cuti')) {
            $todayCuti = \App\Models\PengajuanCuti::with(['karyawan.divisi'])
                ->where('status', 'Approved')
                ->whereDate('tanggal_mulai', '<=', Carbon::today())
                ->whereDate('tanggal_selesai', '>=', Carbon::today())
                ->get();

            if ($todayCuti->count() > 0) {
                $reply = "📅 **Daftar Karyawan yang Sedang Cuti Hari Ini (" . Carbon::today()->format('d M Y') . "):**\n\n";
                foreach ($todayCuti as $i => $c) {
                    $nama = $c->karyawan?->nama ?? 'Karyawan';
                    $divisi = $c->karyawan?->divisi?->nama_divisi ?? '-';
                    $tglMulai = Carbon::parse($c->tanggal_mulai)->format('d M Y');
                    $tglSelesai = Carbon::parse($c->tanggal_selesai)->format('d M Y');
                    $reply .= ($i + 1) . ". **{$nama}** ({$divisi})\n   - Periode: {$tglMulai} s/d {$tglSelesai}\n   - Alasan: *{$c->alasan}*\n";
                }
            } else {
                $reply = "🎉 **Informasi Cuti:** Hari ini (" . Carbon::today()->format('d M Y') . ") **tidak ada karyawan yang sedang mengambil cuti**.";
            }
        }

        // 6. SAPAAN & PERKENALAN
        elseif (preg_match('/\b(halo|hai|hi|pagi|siang|sore|malam|ping|assalamualaikum|assalam|bantuan|tolong)\b/', $lowerMsg)) {
            $reply = "Halo **{$namaPanggilan}** 👋! Saya **AI HRIS** PT Inti Bumi Perkasa. Ada yang bisa saya bantu hari ini?\n\nAnda bisa menanyakan hal seputar:\n- 📍 **Absensi & Scan QR** (Lokasi GPS, Radius, Wi-Fi Kantor)\n- ⏰ **Status Presensi Hari Ini**\n- 📅 **Pengajuan & Sisa Cuti**\n- 💰 **Penggajian & Slip Gaji**\n- 👤 **Profil Karyawan & Akun**";
        }
        
        // 2. STATUS ABSENSI HARI INI
        elseif (str_contains($lowerMsg, 'absen hari ini') || str_contains($lowerMsg, 'status absen') || str_contains($lowerMsg, 'sudah absen')) {
            if (!$karyawan) {
                $reply = "Akun Anda (**{$user->name}**) belum dikaitkan dengan data Karyawan. Silakan hubungi bagian HR / Admin untuk penautan akun.";
            } elseif ($todayAbsensi && $todayAbsensi->masuk) {
                $jam = Carbon::parse($todayAbsensi->masuk)->format('H:i');
                $reply = "📌 **Status Presensi Anda Hari Ini:**\n- Tanggal: **" . Carbon::today()->format('d M Y') . "**\n- Jam Presensi: **{$jam} WIB**\n- Status: **" . strtoupper($todayAbsensi->status) . "**\n" . ($todayAbsensi->distance !== null ? "- Jarak ke Kantor: **{$todayAbsensi->distance} meter**\n" : "") . "\n✅ Anda sudah menyelesaikan presensi untuk hari ini.";
            } else {
                $reply = "📌 **Status Presensi Anda Hari Ini:**\n- Tanggal: **" . Carbon::today()->format('d M Y') . "**\n- Status: **Belum Presensi**\n\n👉 Silakan buka menu **[Scan QR]** untuk melakukan presensi dengan kamera HP Anda!";
            }
        }

        // 3. SEPUTAR ABSENSI VIA QR, GPS, RADIUS, & WI-FI KANTOR
        elseif (preg_match('/\b(qr|scan|gps|wifi|wi-fi|ip|lokasi|radius)\b/', $lowerMsg)) {
            $reply = "📍 **Panduan Presensi QR & Persyaratan:**\n\n" .
                     "1. **Buka Menu Scan QR**: Pilih ikon **Scan QR** pada navigasi sidebar.\n" .
                     "2. **Akses Kamera**: Izinkan browser mengakses kamera smartphone/laptop Anda.\n" .
                     "3. **Sambungan Wi-Fi Kantor**: Pastikan perangkat terhubung ke Wi-Fi Resmi Kantor (IP Target `182.10.100.190` / subnet kantor).\n" .
                     "4. **Lokasi GPS**: Lokasi Anda harus berada di area kantor **PT INTI Bandung** (`-6.9380420, 107.6067810`) dengan radius maksimal **100 meter**.\n" .
                     "5. **Ketentuan Jam Presensi**:\n" .
                     "   - 🟢 Sebelum **08:00 WIB** = Status **Hadir**\n" .
                     "   - 🟡 08:00 - 15:00 WIB = Status **Telat**\n" .
                     "   - 🔴 Setelah 15:00 WIB = Status **Alpa**\n\n" .
                     "💡 *Catatan: Presensi hanya dilakukan **1 kali per hari** (tidak ada jam keluar).*";
        }

        // 4. SEPUTAR CUTI
        elseif (str_contains($lowerMsg, 'cuti') || str_contains($lowerMsg, 'izin') || str_contains($lowerMsg, 'sakit')) {
            if ($karyawan) {
                $totalCutiSubmitted = Cuti::where('karyawan_id', $karyawan->id)->count();
                $approvedCuti = Cuti::where('karyawan_id', $karyawan->id)->where('status', 'Approved')->count();
                $pendingCuti = Cuti::where('karyawan_id', $karyawan->id)->where('status', 'Pending')->count();

                $reply = "📅 **Informasi Cuti Karyawan ({$karyawan->nama}):**\n\n" .
                         "- Total Pengajuan Cuti: **{$totalCutiSubmitted} kali**\n" .
                         "- Cuti Disetujui: **{$approvedCuti}**\n" .
                         "- Cuti Menunggu Persetujuan: **{$pendingCuti}**\n\n" .
                         "💡 **Cara Mengajukan Cuti Baru:**\n" .
                         "1. Masuk ke menu **[Cuti]** di sidebar.\n" .
                         "2. Klik tombol **+ Ajukan Cuti**.\n" .
                         "3. Isi tanggal mulai, tanggal selesai, alasan, dan lampirkan dokumen pendukung (jika sakit).\n" .
                         "4. Tunggu verifikasi dan persetujuan dari Super Admin / HR.";
            } else {
                $reply = "📅 **Informasi Fitur Cuti:**\nPengajuan cuti dilakukan melalui menu **[Cuti]**. Anda dapat memilih tanggal mulai, tanggal selesai, serta jenis cuti (Cuti Tahunan, Sakit, Izin). Persetujuan akan diproses oleh Admin.";
            }
        }

        // 5. SEPUTAR GAJI / PENGGAJIAN & SLIP GAJI
        elseif (str_contains($lowerMsg, 'gaji') || str_contains($lowerMsg, 'penggajian') || str_contains($lowerMsg, 'payrol') || str_contains($lowerMsg, 'slip')) {
            $parsedMonth = $this->parseMonthFromMessage($lowerMsg);
            $targetKaryawan = null;

            if ($user->hasRole(['Admin', 'Super Admin'])) {
                $targetKaryawan = $this->parseKaryawanFromMessage($lowerMsg);
            } else {
                $targetKaryawan = $karyawan; // User biasa hanya bisa query miliknya sendiri
            }

            if ($parsedMonth && $targetKaryawan) {
                $penggajian = \App\Models\Penggajian::where('karyawan_id', $targetKaryawan->id)
                    ->where('bulan', $parsedMonth['bulan'])
                    ->where('tahun', $parsedMonth['tahun'])
                    ->first();
                
                if ($penggajian) {
                    $total = number_format($penggajian->total, 0, ',', '.');
                    $pdfLink = route('penggajian.pdf', $penggajian->id);
                    $reply = "💰 **Data Gaji {$targetKaryawan->nama} ({$parsedMonth['nama']})**\n\n";
                    $reply .= "Total Gaji Bersih: **Rp {$total}**\nStatus: **{$penggajian->status}**\n\n";
                    $reply .= "📄 Anda dapat mengunduh file Slip Gaji (PDF) melalui tombol di bawah ini:\n<br>";
                    $reply .= "<a href='{$pdfLink}' class='btn btn-sm btn-primary mt-2 shadow-sm fw-semibold' target='_blank'><i class='bi bi-file-earmark-pdf me-1'></i> Download Slip Gaji PDF</a>";
                } else {
                    $reply = "Mohon maaf, data penggajian untuk **{$targetKaryawan->nama}** pada bulan **{$parsedMonth['nama']}** belum tersedia.";
                }
            } elseif ($parsedMonth && $user->hasRole(['Admin', 'Super Admin'])) {
                // Tanya rekap bulan tertentu
                $queryMonth = \App\Models\Penggajian::where('bulan', $parsedMonth['bulan'])->where('tahun', $parsedMonth['tahun']);
                $totalNominal = $queryMonth->sum('total');
                $count = $queryMonth->count();
                
                if ($count > 0) {
                    $excelLink = route('penggajian.export.excel', ['bulan' => $parsedMonth['bulan'], 'tahun' => $parsedMonth['tahun']]);
                    $reply = "💰 **Rekap Gaji Semua Karyawan ({$parsedMonth['nama']})**\n\n";
                    $reply .= "Total Data Gaji: **{$count} Karyawan**\n";
                    $reply .= "Total Nominal Keseluruhan: **Rp " . number_format($totalNominal, 0, ',', '.') . "**\n\n";
                    $reply .= "📄 Anda dapat mengunduh seluruh data penggajian bulan ini dalam format Excel melalui tombol di bawah ini:\n<br>";
                    $reply .= "<a href='{$excelLink}' class='btn btn-sm btn-success mt-2 mb-3 shadow-sm fw-semibold' target='_blank'><i class='bi bi-file-earmark-excel me-1'></i> Download Rekap Excel</a>\n<br>";
                    $reply .= "💡 *Untuk melihat slip spesifik per orang (PDF), sebutkan nama karyawannya (Contoh: 'Slip gaji Budi bulan lalu').*";
                } else {
                    $reply = "Belum ada data penggajian yang di-generate untuk semua karyawan pada bulan **{$parsedMonth['nama']}**.";
                }
            } else {
                $reply = "💰 **Informasi Fitur Penggajian & Slip Gaji:**\n\n" .
                         "Anda dapat menanyakan data gaji dengan menyertakan nama bulan. Misalnya:\n" .
                         "- *'Berapa gaji saya bulan ini?'*\n" .
                         "- *'Minta slip gaji bulan lalu'*";
                
                if ($user->hasRole(['Admin', 'Super Admin'])) {
                    $reply .= "\n- *'Slip gaji budi bulan juli'*";
                }
            }
        }

        // 6. SEPUTAR VOUCHER & KEGIATAN HARIAN
        elseif (str_contains($lowerMsg, 'voucher') || str_contains($lowerMsg, 'kegiatan') || str_contains($lowerMsg, 'log')) {
            $reply = "🎁 **Fitur Tambahan HRIS:**\n\n" .
                     "- 🎫 **Voucher**: Manajemen voucher belanja/diskon karyawan yang diberikan oleh perusahaan.\n" .
                     "- 📝 **Kegiatan Harian**: Catatan aktivitas harian pegawai. Anda dapat mengisi laporan pekerjaan yang diselesaikan setiap harinya pada menu **[Kegiatan Harian]**.";
        }

        // 7. PROFILE & AKUN KARYAWAN
        elseif (str_contains($lowerMsg, 'profil') || str_contains($lowerMsg, 'profile') || str_contains($lowerMsg, 'password') || str_contains($lowerMsg, 'akun') || str_contains($lowerMsg, 'user')) {
            $reply = "👤 **Informasi Akun Anda:**\n\n" .
                     "- Nama Pengguna: **{$user->name}**\n" .
                     "- Role / Peran: **" . ($user->roles->pluck('name')->first() ?? 'User') . "**\n" .
                     ($karyawan ? "- NIK Karyawan: **{$karyawan->nik}**\n- Divisi: **" . (optional($karyawan->divisi)->nama_divisi ?? '-') . "**\n" : "") . "\n" .
                     "💡 Untuk mengganti foto profil atau password akun, klik foto profil di pojok kanan atas layar lalu pilih **Edit Profile**.";
        }

        // 8. TENTANG PERUSAHAAN / HRIS
        elseif (str_contains($lowerMsg, 'perusahaan') || str_contains($lowerMsg, 'inti bumi') || str_contains($lowerMsg, 'hris') || str_contains($lowerMsg, 'ibp')) {
            $reply = "🏢 **PT Inti Bumi Perkasa (IBP) - HRIS Portal**\n\n" .
                     "Sistem Informasi Sumber Daya Manusia terpadu yang memfasilitasi Manajemen Absensi Presensi QR & GPS, Pengajuan Cuti, Laporan Kegiatan, Penggajian Terintegrasi, serta Voucher Karyawan.";
        }

        // 9. JAWABAN DEFAULT / GENERAL HELP
        else {
            $reply = "Terima kasih atas pertanyaan Anda, **{$namaPanggilan}** 😊.\n\n" .
                     "Saya dapat membantu Anda memberikan informasi cepat seputar:\n" .
                     "1. 📍 **Absensi QR & Lokasi GPS Kantor**\n" .
                     "2. ⏰ **Cek Status Presensi Hari Ini**\n" .
                     "3. 📅 **Cara Pengajuan Cuti**\n" .
                     "4. 💰 **Informasi Penggajian & Slip Gaji**\n" .
                     "5. 👤 **Pengaturan Profil & Akun**\n\n" .
                     "Silakan pilih topik di atas atau ketikkan pertanyaan spesifik Anda!";
        }

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'user_name' => $namaPanggilan,
            'time' => Carbon::now()->format('H:i')
        ]);
    }
}
