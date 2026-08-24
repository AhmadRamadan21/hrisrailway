<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AdminNotification;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\PenggunaKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AbsensiController extends Controller
{
    // Jam Batas Absensi Masuk
    private $jamBatasTepatWaktu = '08:00:00'; // Sebelum jam 8 = Hadir
    private $jamBatasMaksimal   = '15:00:00'; // Setelah jam 15:00 = Alpa

    // Koordinat Target Kantor (-6.899056783893525, 107.6415330332956) & Radius Max 500m
    private $officeLatitude     = -6.899056783893525;
    private $officeLongitude    = 107.6415330332956;
    private $officeRadiusMeters = 500;

    // Wi-Fi Kantor Target IP Address
    private $officeWifiIp = '182.10.100.190';

    private function getClientIp(Request $request)
    {
        // Karena Laravel sudah mengonfigurasi trustProxies(at: '*') di bootstrap/app.php,
        // $request->ip() akan secara otomatis mengekstrak IP publik asli peranti pengguna dari header X-Forwarded-For Ngrok
        return $request->ip();
    }

    private function isOfficeIp(Request $request)
    {
        if (env('ENABLE_WIFI_IP_CHECK', true) === false) {
            return true;
        }

        $clientIp = $this->getClientIp($request);
        $officeIp = env('OFFICE_WIFI_IP', $this->officeWifiIp);
        $rawAllowed = env('ALLOWED_OFFICE_IPS', '182.10.100.190,182.10.98.64,2404:c0:a302:4582:5803:9a9f:e7c1:f807');
        $allowedIps = array_filter(array_map('trim', explode(',', $rawAllowed)));

        // 1. Cek jika IP publik / IPv6 persis sama dengan daftar allowed IP
        if (in_array($clientIp, $allowedIps) || $clientIp === $officeIp || $clientIp === '2404:c0:a302:4582:5803:9a9f:e7c1:f807') {
            return true;
        }

        // 2. Cek jika IPv4 diawali dengan prefix pool IP ISP Wi-Fi Kantor (182.10.* atau 192.168.*)
        if (str_starts_with($clientIp, '182.10.') || str_starts_with($clientIp, '192.168.')) {
            return true;
        }

        // 3. Cek jika peranti HP menggunakan IPv6 dari Wi-Fi Kantor (2404:c0: / 2404: / 2400:)
        if (str_starts_with($clientIp, '2404:c0:') || str_starts_with($clientIp, '2404:') || str_starts_with($clientIp, '2400:')) {
            return true;
        }

        // 4. Cek jika IP lokal / loopback
        if ($clientIp === '127.0.0.1' || $clientIp === '::1') {
            return true;
        }

        return false;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function getLinkedKaryawan($user)
    {
        if (!$user) return null;

        // 1. Cek pencocokan username via tabel pengguna_karyawans
        try {
            $linked = PenggunaKaryawan::where('username', $user->username)
                ->with(['karyawan.divisi'])
                ->first();
            if ($linked && $linked->karyawan) {
                return $linked->karyawan;
            }
        } catch (\Throwable $e) {
            Log::debug('PenggunaKaryawan lookup failed: ' . $e->getMessage());
        }

        // 2. Fallback cerdas: Cari di tabel karyawans berdasarkan email, nama, atau no_pegawai
        $karyawan = Karyawan::with('divisi')
            ->where('email', $user->email)
            ->orWhere('nama', $user->name)
            ->orWhere('no_pegawai', $user->username)
            ->first();

        // 3. Otomatis buatkan link ke pengguna_karyawans agar terhubung permanen
        if ($karyawan && !empty($user->username)) {
            try {
                PenggunaKaryawan::firstOrCreate(
                    ['karyawan_id' => $karyawan->id],
                    [
                        'username' => $user->username,
                        'role'     => $user->hasRole(['Admin', 'Super Admin']) ? 'Admin' : 'User'
                    ]
                );
            } catch (\Throwable $e) {
                // Ignore constraint duplicates
            }
        }

        return $karyawan;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $currentKaryawan = $this->getLinkedKaryawan($user);
        $linked = $currentKaryawan ? PenggunaKaryawan::where('karyawan_id', $currentKaryawan->id)->first() : null;

        $divisis = Divisi::orderBy('nama_divisi')->get();
        $karyawans = Karyawan::orderBy('nama')->get();

        $totalAbsensi = 0;
        $countHadir = 0;
        $countTelat = 0;
        $countAlpa = 0;
        $recent = [];

        if (class_exists(Absensi::class)) {
            $query = Absensi::with(['karyawan.divisi']);

            if ($user->hasRole('User') && $currentKaryawan) {
                $query->where('karyawan_id', $currentKaryawan->id);
            }

            $from = $request->input('from', Carbon::today()->toDateString());
            $to   = $request->input('to', Carbon::today()->toDateString());

            $query->whereDate('tanggal', '>=', $from)
                  ->whereDate('tanggal', '<=', $to);

            $totalAbsensi = (clone $query)->count();
            $countHadir = (clone $query)->whereIn('status', ['Hadir', 'Telat'])->count();
            $countTelat = (clone $query)->where('status', 'Telat')->count();
            $countAlpa = (clone $query)->where('status', 'Alpa')->count();

            $filtered = (clone $query)
                ->when($request->input('divisi_id'), fn($q, $divisi) => $q->whereHas('karyawan.divisi', fn($sub) => $sub->where('id', $divisi)))
                ->when($request->input('karyawan_id'), fn($q, $karyawan) => $q->where('karyawan_id', $karyawan))
                ->when($request->input('status'), function($q, $status) {
                    if ($status === 'Hadir') {
                        return $q->whereIn('status', ['Hadir', 'Telat']);
                    }
                    return $q->where('status', $status);
                });
            $recent = $filtered->latest()->paginate(15)->withQueryString();
        }

        return view('absensi.index', compact(
            'linked', 'currentKaryawan', 'divisis', 'karyawans',
            'totalAbsensi', 'countHadir', 'countTelat', 'countAlpa', 'recent'
        ));
    }

    public function selfieIndex()
    {
        $user = auth()->user();
        $currentKaryawan = $this->getLinkedKaryawan($user);
        $linked = $currentKaryawan ? PenggunaKaryawan::where('karyawan_id', $currentKaryawan->id)->first() : null;

        $todayAbsensi = null;
        if ($currentKaryawan) {
            $todayAbsensi = Absensi::where('karyawan_id', $currentKaryawan->id)
                ->whereDate('tanggal', Carbon::today())
                ->first();
        }

        $officeLat = (float) env('OFFICE_LATITUDE', $this->officeLatitude);
        $officeLon = (float) env('OFFICE_LONGITUDE', $this->officeLongitude);
        $maxRadius = (int) env('OFFICE_RADIUS_METERS', $this->officeRadiusMeters);

        return view('absensi.selfie', compact('linked', 'currentKaryawan', 'todayAbsensi', 'officeLat', 'officeLon', 'maxRadius'));
    }

    public function masuk(Request $request)
    {
        if (class_exists(Absensi::class)) {
            $user = auth()->user();
            $linked = PenggunaKaryawan::where('username', $user->username)->first();
            $karyawanId = $linked?->karyawan_id;

            $rules = ['photo' => 'nullable|string'];
            if ($user->hasRole('User')) {
                $rules['photo'] = 'required|string';
            }

            $request->validate($rules, [
                'photo.required' => 'Silakan ambil foto selfie sebelum melakukan absensi masuk.',
            ]);

            // VALIDASI GEOFENCING KOORDINAT GPS (RADIUS 500M)
            $lat = $request->input('latitude');
            $lon = $request->input('longitude');
            $targetLat = (float) env('OFFICE_LATITUDE', $this->officeLatitude);
            $targetLon = (float) env('OFFICE_LONGITUDE', $this->officeLongitude);
            $maxRadius = (int) env('OFFICE_RADIUS_METERS', $this->officeRadiusMeters);

            $distance = null;
            if ($lat !== null && $lon !== null && $lat !== '' && $lon !== '') {
                $distance = round($this->calculateDistance((float)$lat, (float)$lon, $targetLat, $targetLon));
            }

            if ($distance === null || $distance > $maxRadius) {
                $distText = $distance !== null ? "Jarak Anda saat ini: {$distance} meter dari PLN ULP BANDUNG TIMUR (Maksimal: {$maxRadius}m)." : "Lokasi GPS peranti Anda belum terdeteksi/diaktifkan.";
                $errMsg = "Absensi Selfie Ditolak: Fitur absensi selfie hanya dapat digunakan jika Anda berada di dalam radius {$maxRadius} meter dari PLN ULP BANDUNG TIMUR (-6.89905678, 107.64153303). {$distText}";
                
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'location_error' => true,
                        'distance' => $distance,
                        'max_radius' => $maxRadius,
                        'message' => $errMsg
                    ], 422);
                }
                return back()->with('error', $errMsg);
            }

            if ($user->hasRole('User')) {
                if (!$karyawanId) {
                    $msg = 'Data karyawan tidak ditemukan untuk akun Anda.';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                    return back()->with('error', $msg);
                }
            } else {
                $request->validate(['karyawan_id' => 'required|exists:karyawans,id']);
                $karyawanId = $request->input('karyawan_id');
            }

            $abs = Absensi::firstOrNew([
                'karyawan_id' => $karyawanId,
                'tanggal' => Carbon::today(),
            ]);

            if ($abs->exists && $abs->masuk) {
                $msg = 'Absensi masuk hari ini sudah pernah dicatat.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'already_done' => true, 'message' => $msg]);
                }
                return back()->with('warning', $msg);
            }

            $nowTime = Carbon::now()->format('H:i:s');
            
            // LOGIKA STATUS ABSEN
            if ($nowTime > $this->jamBatasMaksimal) {
                $statusAbsen = 'Alpa';
            } elseif ($nowTime > $this->jamBatasTepatWaktu) {
                $statusAbsen = 'Telat';
            } else {
                $statusAbsen = 'Hadir';
            }

            $abs->masuk = $nowTime;
            $abs->status = $statusAbsen;
            $abs->photo = $request->input('photo');
            $abs->distance = $distance;
            $abs->save();

            AdminNotification::create([
                'user_id' => auth()->id(),
                'absensi_id' => $abs->id,
                'title' => 'Absensi Masuk Selfie',
                'message' => auth()->user()->name . ' melakukan absensi masuk selfie (Status: ' . $statusAbsen . ' - Jarak: ' . $distance . 'm).',
                'is_read' => false,
            ]);

            $successMsg = 'Absensi masuk selfie berhasil dicatat (Status: ' . $statusAbsen . ' - Jarak: ' . $distance . 'm).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'mode' => 'masuk',
                    'status' => $statusAbsen,
                    'distance' => $distance,
                    'time' => Carbon::now()->format('H:i:s'),
                    'date' => Carbon::now()->format('d M Y'),
                    'message' => $successMsg
                ]);
            }

            return back()->with('success', $successMsg);
        }

        return back()->with('success', 'Absensi masuk (demo) tercatat.');
    }

    public function keluar(Request $request)
    {
        if (class_exists(Absensi::class)) {
            $user = auth()->user();
            $linked = PenggunaKaryawan::where('username', $user->username)->first();
            $karyawanId = $linked?->karyawan_id;

            // VALIDASI GEOFENCING KOORDINAT GPS (RADIUS 500M)
            $lat = $request->input('latitude');
            $lon = $request->input('longitude');
            $targetLat = (float) env('OFFICE_LATITUDE', $this->officeLatitude);
            $targetLon = (float) env('OFFICE_LONGITUDE', $this->officeLongitude);
            $maxRadius = (int) env('OFFICE_RADIUS_METERS', $this->officeRadiusMeters);

            $distance = null;
            if ($lat !== null && $lon !== null && $lat !== '' && $lon !== '') {
                $distance = round($this->calculateDistance((float)$lat, (float)$lon, $targetLat, $targetLon));
            }

            if ($distance === null || $distance > $maxRadius) {
                $distText = $distance !== null ? "Jarak Anda saat ini: {$distance} meter dari PLN ULP BANDUNG TIMUR (Maksimal: {$maxRadius}m)." : "Lokasi GPS peranti Anda belum terdeteksi/diaktifkan.";
                $errMsg = "Absensi Selfie Ditolak: Fitur absensi selfie hanya dapat digunakan jika Anda berada di dalam radius {$maxRadius} meter dari PLN ULP BANDUNG TIMUR (-6.89905678, 107.64153303). {$distText}";
                
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'location_error' => true,
                        'distance' => $distance,
                        'max_radius' => $maxRadius,
                        'message' => $errMsg
                    ], 422);
                }
                return back()->with('error', $errMsg);
            }

            if ($user->hasRole('User')) {
                if (!$karyawanId) {
                    $msg = 'Data karyawan tidak ditemukan untuk akun Anda.';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                    return back()->with('error', $msg);
                }
            } else {
                $request->validate(['karyawan_id' => 'required|exists:karyawans,id']);
                $karyawanId = $request->input('karyawan_id');
            }

            $abs = Absensi::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', Carbon::today())
                ->first();

            if ($abs) {
                $abs->keluar = Carbon::now()->format('H:i:s');
                if ($distance !== null) {
                    $abs->distance = $distance;
                }
                $abs->save();

                AdminNotification::create([
                    'user_id' => auth()->id(),
                    'absensi_id' => $abs->id,
                    'title' => 'Absensi Keluar Selfie',
                    'message' => auth()->user()->name . ' telah melakukan absensi keluar selfie (Jarak: ' . $distance . 'm).',
                    'is_read' => false,
                ]);

                $successMsg = 'Absensi keluar selfie berhasil dicatat (Jarak: ' . $distance . 'm).';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'mode' => 'keluar',
                        'distance' => $distance,
                        'time' => Carbon::now()->format('H:i:s'),
                        'date' => Carbon::now()->format('d M Y'),
                        'message' => $successMsg
                    ]);
                }

                return back()->with('success', $successMsg);
            }

            $notFoundMsg = 'Catatan absensi masuk tidak ditemukan untuk hari ini.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $notFoundMsg], 422);
            }

            return back()->with('error', $notFoundMsg);
        }

        return back()->with('success', 'Absensi keluar (demo) tercatat.');
    }

    public function scanQr()
    {
        $user = auth()->user();
        $currentKaryawan = $this->getLinkedKaryawan($user);
        $linked = $currentKaryawan ? PenggunaKaryawan::where('karyawan_id', $currentKaryawan->id)->first() : null;

        $todayAbsensi = null;
        if ($currentKaryawan) {
            $todayAbsensi = Absensi::where('karyawan_id', $currentKaryawan->id)
                ->whereDate('tanggal', Carbon::today())
                ->first();
        }

        return view('absensi.scan', compact('linked', 'currentKaryawan', 'todayAbsensi'));
    }

    public function processQrScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $user = auth()->user();
        $currentKaryawan = $this->getLinkedKaryawan($user);
        $karyawanId = $currentKaryawan?->id;

        if (!$karyawanId && $user->hasRole('User')) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan untuk akun Anda. Silakan hubungi admin.'
            ], 422);
        }

        if (!$karyawanId) {
            $karyawan = Karyawan::first();
            $karyawanId = $karyawan?->id;
        }

        if (!$karyawanId) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan belum tersedia di sistem.'
            ], 422);
        }

        // VALIDASI IP ADDRESS WI-FI KANTOR
        if (!$this->isOfficeIp($request)) {
            $clientIp = $this->getClientIp($request);
            $officeIp = env('OFFICE_WIFI_IP', $this->officeWifiIp);
            $errMsg = 'Absensi Gagal: Perangkat Anda terdeteksi menggunakan IP: ' . $clientIp . '. Harus terhubung ke Wi-Fi Kantor (' . $officeIp . ').';

            return response()->json([
                'success' => false,
                'ip_error' => true,
                'client_ip' => $clientIp,
                'office_ip' => $officeIp,
                'message' => $errMsg
            ]);
        }

        // VALIDASI LOKASI GPS KOORDINAT KANTOR
        $latitude  = $request->input('latitude');
        $longitude = $request->input('longitude');

        $targetLat = (float) env('QR_OFFICE_LATITUDE', -6.9381630);
        $targetLon = (float) env('QR_OFFICE_LONGITUDE', 107.6071170);
        $maxRadius = (int) env('OFFICE_RADIUS_METERS', $this->officeRadiusMeters);

        $distance = null;
        if ($latitude !== null && $longitude !== null && $latitude !== '' && $longitude !== '') {
            $distance = round($this->calculateDistance((float)$latitude, (float)$longitude, $targetLat, $targetLon));
            if ($distance > $maxRadius) {
                $errMsg = $user->hasRole(['Admin', 'Super Admin'])
                    ? 'Absensi Gagal: Lokasi Anda berada di luar radius area kantor (' . $distance . ' meter dari kantor). Maksimal radius: ' . $maxRadius . 'm.'
                    : 'Absensi Gagal: Posisi Anda berada di luar radius area kantor.';
                return response()->json([
                    'success' => false,
                    'location_error' => true,
                    'distance' => $distance,
                    'max_radius' => $maxRadius,
                    'message' => $errMsg
                ]);
            }
        }

        $today = Carbon::today();
        $nowTime = Carbon::now()->format('H:i:s');
        $isAdmin = $user->hasRole(['Admin', 'Super Admin']);

        $absensi = Absensi::firstOrNew([
            'karyawan_id' => $karyawanId,
            'tanggal' => $today,
        ]);

        if ($distance !== null) {
            $absensi->distance = $distance;
        }

        if (!$absensi->exists || empty($absensi->masuk)) {
            // LOGIKA STATUS ABSEN VIA QR
            if ($nowTime > $this->jamBatasMaksimal) {
                $statusAbsen = 'Alpa';
            } elseif ($nowTime > $this->jamBatasTepatWaktu) {
                $statusAbsen = 'Telat';
            } else {
                $statusAbsen = 'Hadir';
            }

            $absensi->masuk = $nowTime;
            $absensi->status = $statusAbsen;
            if ($request->input('photo')) {
                $absensi->photo = $request->input('photo');
            }
            $absensi->save();

            $distText = $distance !== null ? " - Jarak: {$distance}m" : "";

            AdminNotification::create([
                'user_id' => $user->id,
                'absensi_id' => $absensi->id,
                'title' => 'Absensi Masuk via QR',
                'message' => $user->name . ' melakukan absensi via Scan QR (' . $statusAbsen . $distText . ').',
                'is_read' => false,
            ]);

            $msg = 'Absensi (' . $statusAbsen . ') berhasil dicatat!';
            if ($isAdmin && $distance !== null) {
                $msg .= ' (Jarak: ' . $distance . ' meter)';
            }

            return response()->json([
                'success' => true,
                'mode' => 'masuk',
                'status' => $statusAbsen,
                'distance' => $distance,
                'is_admin' => $isAdmin,
                'time' => Carbon::now()->format('H:i:s'),
                'date' => Carbon::now()->format('d M Y'),
                'message' => $msg
            ]);
        } else {
            $jamAbsen = Carbon::parse($absensi->masuk)->format('H:i');
            return response()->json([
                'success' => false,
                'already_done' => true,
                'message' => 'Anda sudah melakukan absensi hari ini (Jam: ' . $jamAbsen . ' WIB - Status: ' . $absensi->status . ').'
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        if (!class_exists(Absensi::class)) {
            return back()->with('error', 'Fitur absensi belum tersedia.');
        }

        $user = auth()->user();
        $currentKaryawan = $this->getLinkedKaryawan($user);
        $query = Absensi::with(['karyawan.divisi']);

        if ($user->hasRole('User') && $currentKaryawan) {
            $query->where('karyawan_id', $currentKaryawan->id);
        }

        $from = $request->input('from', Carbon::today()->toDateString());
        $to   = $request->input('to', Carbon::today()->toDateString());

        $query->whereDate('tanggal', '>=', $from)
              ->whereDate('tanggal', '<=', $to);

        $filtered = (clone $query)
            ->when($request->input('divisi_id'), fn($q, $divisi) => $q->whereHas('karyawan.divisi', fn($sub) => $sub->where('id', $divisi)))
            ->when($request->input('karyawan_id'), fn($q, $karyawan) => $q->where('karyawan_id', $karyawan))
            ->when($request->input('status'), fn($q, $status) => $q->where('status', $status));

        // Get all filtered records
        $items = $filtered->orderBy('tanggal', 'asc')->get();

        $filename = "rekap-absensi-{$from}-sd-{$to}.xls";

        return $this->generateExcelResponse($items, $filename);
    }

    private function generateExcelResponse($items, $filename)
    {
        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>Data Absensi</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        $html .= '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>
                    table { border-collapse: collapse; font-family: "Segoe UI", Arial, sans-serif; font-size: 12px; }
                    th, td { border: 1px solid #000; padding: 6px 10px; text-align: left; vertical-align: middle; }
                    th { background: #e2e8f0; color: #000000; font-weight: bold; text-align: center; }
                    .center { text-align: center; }
                  </style></head><body>';
        
        $html .= '<table><thead><tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Jam Masuk</th>
                    <th>Status</th>
                  </tr></thead><tbody>';

        foreach ($items as $i => $row) {
            $tanggal = optional($row->tanggal)->format('d M Y') ?? '-';
            $nama = e(strtoupper($row->karyawan->nama ?? '-'));
            $divisi = e(strtoupper(optional($row->karyawan->divisi)->nama_divisi ?? '-'));
            $masuk = $row->masuk ? \Carbon\Carbon::parse($row->masuk)->format('H:i') : '-';
            $status = e($row->status);

            $html .= '<tr>'
                . '<td class="center">' . ($i + 1) . '</td>'
                . '<td class="center">' . $tanggal . '</td>'
                . '<td>' . $nama . '</td>'
                . '<td>' . $divisi . '</td>'
                . '<td class="center">' . $masuk . '</td>'
                . '<td class="center">' . $status . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}