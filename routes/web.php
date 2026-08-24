<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisiJabatanController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PenggunaKaryawanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\KegiatanHarianController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AiKnowledgeBaseController;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Root Landing Page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
})->name('welcome');

// Halaman yang butuh login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:Super Admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Divisi & Jabatan
    Route::get('/divisi-jabatan', [DivisiJabatanController::class, 'index'])->name('divisi-jabatan.index');
    Route::post('/divisi', [DivisiJabatanController::class, 'storeDivisi'])->name('divisi.store');
    Route::put('/divisi/{divisi}', [DivisiJabatanController::class, 'updateDivisi'])->name('divisi.update');
    Route::delete('/divisi/{divisi}', [DivisiJabatanController::class, 'destroyDivisi'])->name('divisi.destroy');

    Route::post('/jabatan', [DivisiJabatanController::class, 'storeJabatan'])->name('jabatan.store');
    Route::put('/jabatan/{jabatan}', [DivisiJabatanController::class, 'updateJabatan'])->name('jabatan.update');
    Route::delete('/jabatan/{jabatan}', [DivisiJabatanController::class, 'destroyJabatan'])->name('jabatan.destroy');

    // ===================== KARYAWAN =====================
    // Route khusus / spesifik wajib di atas Resource
    Route::get('/karyawan/export/excel', [KaryawanController::class, 'exportExcel'])->name('karyawan.export.excel');
    Route::get('/karyawan/export/pdf', [KaryawanController::class, 'exportPdf'])->name('karyawan.export.pdf');
    
    // Resource otomatis menangani index, create, store, show, edit, update, destroy
    Route::resource('karyawan', KaryawanController::class);

    // Pengguna (pakai UserController & tabel users — 1 sistem akun terpusat)
    Route::get('/pengguna', [UserController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/create', [UserController::class, 'create'])->name('pengguna.create');
    Route::post('/pengguna', [UserController::class, 'store'])->name('pengguna.store');
    Route::get('/pengguna/{user}/edit', [UserController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('pengguna.destroy');

    // Absensi
    Route::get('/absensi/export/excel', [AbsensiController::class, 'exportExcel'])->name('absensi.export.excel');
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/selfie', [AbsensiController::class, 'selfieIndex'])->name('absensi.selfie');
    Route::get('/scan-qr', [AbsensiController::class, 'scanQr'])->name('scan-qr.index');
    Route::post('/scan-qr/process', [AbsensiController::class, 'processQrScan'])->name('scan-qr.process');
    Route::post('/absensi/masuk', [AbsensiController::class, 'masuk'])->name('absensi.masuk');
    Route::post('/absensi/keluar', [AbsensiController::class, 'keluar'])->name('absensi.keluar');

    // Kegiatan Harian
    Route::get('/kegiatan-harian', [KegiatanHarianController::class, 'index'])->name('kegiatan-harian.index');
    Route::post('/kegiatan-harian', [KegiatanHarianController::class, 'store'])->name('kegiatan-harian.store');
    Route::put('/kegiatan-harian/{kegiatan}', [KegiatanHarianController::class, 'update'])->name('kegiatan-harian.update');
    Route::delete('/kegiatan-harian/{kegiatan}', [KegiatanHarianController::class, 'destroy'])->name('kegiatan-harian.destroy');

    // Cuti
    Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
    Route::post('/cuti', [CutiController::class, 'store'])->name('cuti.store');
    Route::put('/cuti/{item}', [CutiController::class, 'update'])->name('cuti.update');
    Route::patch('/cuti/{item}/approve', [CutiController::class, 'approve'])->name('cuti.approve');
    Route::patch('/cuti/{item}/reject', [CutiController::class, 'reject'])->name('cuti.reject');
    Route::get('/cuti/{item}/surat', [CutiController::class, 'surat'])->name('cuti.surat');
    Route::get('/cuti/{item}/pdf', [CutiController::class, 'pdf'])->name('cuti.pdf');
    Route::delete('/cuti/{item}', [CutiController::class, 'destroy'])->name('cuti.destroy');

    // Penggajian
    Route::get('/penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
    Route::post('/penggajian/generate', [PenggajianController::class, 'generate'])->name('penggajian.generate');
    Route::post('/penggajian/reset', [PenggajianController::class, 'reset'])->name('penggajian.reset');
    Route::post('/penggajian/preview', [PenggajianController::class, 'preview'])->name('penggajian.preview');
    Route::post('/penggajian/store', [PenggajianController::class, 'store'])->name('penggajian.store');
    Route::delete('/penggajian/{id}', [PenggajianController::class, 'destroy'])->name('penggajian.destroy');
    Route::get('/penggajian/{id}', [PenggajianController::class, 'show'])->name('penggajian.show');
    Route::get('/penggajian/export/excel', [PenggajianController::class, 'exportExcel'])->name('penggajian.export.excel');
    Route::get('/penggajian/{id}/pdf', [PenggajianController::class, 'exportPdfItem'])->name('penggajian.pdf');
    Route::get('/penggajian/{id}/word', [PenggajianController::class, 'exportWordItem'])->name('penggajian.word');
    Route::get('/penggajian/{id}/excel', [PenggajianController::class, 'exportExcelItem'])->name('penggajian.excel');

    // Notifikasi
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');

    // AI HRIS Dedicated Page & Send Route
    Route::get('/ai-chat', [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-chat/send', [AiChatController::class, 'processMessage'])->name('ai-chat.send');

    // Admin Kelola AI Knowledge Base
    Route::resource('ai-knowledge', AiKnowledgeBaseController::class)->except(['create', 'show', 'edit']);

    // Profil pengguna
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
});