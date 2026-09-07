<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Karyawan;
use App\Models\PosBiaya;
use App\Models\Voucher;
use App\Models\Absensi;
use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. STAT CARDS UTAMA
        $totalVoucher  = Karyawan::count();
        $totalPosBiaya = PosBiaya::count();
        $totalBagian   = Bagian::count();

        $totalRealisasiBulanIni = Voucher::where('status', 'Approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nilai');

        // 2. DATA KEHADIRAN HARI INI
        $today = Carbon::today();
        $currentTime = Carbon::now()->format('H:i:s');

        // Total yang Hadir Tepat Waktu
        $countDraft = Absensi::whereDate('tanggal', $today)
            ->where('status', 'Hadir')
            ->count();

        // Total yang Terlambat
        $countApproved = Absensi::whereDate('tanggal', $today)
            ->where('status', 'Telat')
            ->count();

        // Total yang Cuti
        $countCutiHariIni = 0;
        if (class_exists(Cuti::class)) {
            $countCutiHariIni = Cuti::whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->where('status', 'Approved')
                ->count();
        }

        // Hitung Alpa/Tidak Hadir yang sudah tercatat di DB
        $countAlpaRecorded = Absensi::whereDate('tanggal', $today)
            ->where('status', 'Alpa')
            ->count();

        // LOGIKA PENENTUAN TIDAK HADIR
        // Jika sudah melewati jam 15:00, sisa karyawan yang belum absen dihitung Tidak Hadir / Alpa
        if ($currentTime > '15:00:00') {
            $totalSudahAbsenOrIzin = $countDraft + $countApproved + $countCutiHariIni + $countAlpaRecorded;
            $countRejected = max(0, $totalVoucher - $totalSudahAbsenOrIzin) + $countAlpaRecorded;
        } else {
            // Sebelum jam 15:00, yang dihitung Tidak Hadir hanya yang sudah bertanda Alpa
            $countRejected = $countAlpaRecorded;
        }

        // 3. STATISTIK HARI INI UNTUK CHART.JS
        $totalHadirHariIni = Absensi::whereDate('tanggal', $today)
            ->whereIn('status', ['Hadir', 'Telat'])
            ->count();

        if (class_exists(Cuti::class)) {
            $totalCutiHariIni = Cuti::whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->where('status', 'Approved')
                ->count();
        } else {
            $totalCutiHariIni = Absensi::whereDate('tanggal', $today)
                ->whereIn('status', ['Cuti', 'Izin'])
                ->count();
        }

        $totalAlfaHariIni = $countRejected; // Menggunakan variabel Tidak Hadir yang sudah dikalkulasi dengan aturan jam 15:00

        // 4. DATA AKTIVITAS TERAKHIR
        $recentVouchers = Absensi::with('karyawan')
            ->latest()
            ->take(5)
            ->get();

        // 5. FITUR DETEKSI ULANG TAHUN KARYAWAN YANG LOGIN
        $user = auth()->user();
        $isBirthday = false;

        // Cari data karyawan berdasarkan email akun user yang login
        $karyawanLogin = Karyawan::where('email', $user->email)->first();

        if ($karyawanLogin && $karyawanLogin->tanggal_lahir) {
            $ultah = Carbon::parse($karyawanLogin->tanggal_lahir);

            // Cocokkan Bulan & Tanggal saja (Format: MM-DD)
            if ($ultah->format('m-d') === $today->format('m-d')) {
                $isBirthday = true;
            }
        }

        // 6. ULANG TAHUN MENDATANG
        $upcomingBirthdays = Karyawan::whereNotNull('tanggal_lahir')
            ->get()
            ->map(function ($k) use ($today) {
                $birthDate = Carbon::parse($k->tanggal_lahir);
                $nextBday = $birthDate->copy()->year($today->year);

                // Jika ulang tahun tahun ini sudah lewat (bukan hari ini), maka ulang tahun berikutnya adalah tahun depan
                if ($nextBday->isPast() && !$nextBday->isToday()) {
                    $nextBday->addYear();
                }

                $k->next_birthday = $nextBday;
                $k->days_until = $today->diffInDays($nextBday, false);
                $k->age_turning = $nextBday->year - $birthDate->year;

                return $k;
            })
            // Filter opsional: hanya tampilkan yang ultahnya maksimal 6 bulan ke depan agar lebih relevan
            ->filter(fn($k) => $k->days_until >= 0 && $k->days_until <= 180)
            ->sortBy('days_until')
            ->take(5);

        return view('dashboard', compact(
            'totalVoucher',
            'totalPosBiaya',
            'totalBagian',
            'totalRealisasiBulanIni',
            'countDraft',
            'countApproved',
            'countRejected',
            'recentVouchers',
            'totalHadirHariIni',
            'totalCutiHariIni',
            'totalAlfaHariIni',
            'isBirthday',       // <-- Variabel Flag Ulang Tahun
            'karyawanLogin',    // <-- Data Karyawan Login
            'upcomingBirthdays' // <-- Data Ulang Tahun Mendatang
        ));
    }

    public function profile()
    {
        $user = auth()->user();

        $karyawan = Karyawan::with(['divisi', 'jabatan'])
            ->where('email', $user->email)
            ->first();

        return view('profile', compact('user', 'karyawan'));
    }

    public function updateProfile(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');

            // Delete old photo if exists (stored under public/...)
            if ($user->foto && File::exists(public_path($user->foto))) {
                File::delete(public_path($user->foto));
            }

            // Prepare destination under public/uploads/profil
            $destinationPath = public_path('uploads/profil');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);

            // Save relative public path so `asset($user->foto)` works
            $user->foto = 'uploads/profil/' . $filename;
            $user->save();
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}
