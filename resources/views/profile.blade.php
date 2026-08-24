@extends('layouts.app')

@php($pageTitle = 'Profil Saya')

@section('content')

{{-- HEADER HALAMAN --}}
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">Profil Saya</h4>
    <p class="text-muted small mb-0">Informasi detail akun pengguna dan tautan data karyawan Anda</p>
  </div>
</div>

<div class="row g-4">
  
  {{-- KARTU RINGKASAN PROFIL (SISI KIRI) --}}
  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 text-center p-4">
      <div class="card-body">
        
        {{-- Avatar Circle / Foto Profil --}}
        @if(auth()->user()->foto)
          <img src="{{ asset('storage/' . auth()->user()->foto) }}" id="avatarPreview" alt="Profile Photo" class="rounded-circle mx-auto mb-3 shadow-sm object-fit-cover" style="width: 100px; height: 100px; border: 3px solid #e2e8f0;">
        @else
          <div id="avatarInitials" class="avatar-circle mx-auto mb-3 shadow-sm" style="width: 100px; height: 100px; font-size: 32px; background: #2563eb;">
            {{ strtoupper(substr($user->name ?? $user->username, 0, 2)) }}
          </div>
          <img src="" id="avatarPreview" alt="Profile Photo" class="rounded-circle mx-auto mb-3 shadow-sm object-fit-cover d-none" style="width: 100px; height: 100px; border: 3px solid #e2e8f0;">
        @endif

        {{-- Form Ganti Foto --}}
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="mb-3">
          @csrf
          <div class="input-group input-group-sm mb-2 px-3">
            <input type="file" name="foto" id="fotoInput" class="form-control rounded-start-pill @error('foto') is-invalid @enderror" accept=".jpg,.jpeg,.png" required style="font-size: 11px;">
            <button class="btn btn-outline-primary rounded-end-pill" type="submit" style="font-size: 11px;"><i class="bi bi-upload"></i> Upload</button>
          </div>
          @error('foto')
            <div class="small text-danger mt-1 text-center">{{ $message }}</div>
          @enderror
          @if(session('success'))
            <div class="small text-success mt-1 text-center">{{ session('success') }}</div>
          @endif
        </form>

        <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
        <p class="text-muted small mb-2">@_{{ $user->username }}</p>

        <span class="badge bg-primary-subtle text-primary fw-medium px-3 py-1 rounded-pill mb-3">
          <i class="bi bi-shield-check me-1"></i>{{ $user->role->name ?? 'User' }}
        </span>

        <hr class="my-3 text-muted opacity-25">

        {{-- Status Keterhubungan --}}
        <div class="d-flex align-items-center justify-content-between text-start bg-light p-3 rounded-3">
          <span class="small text-muted fw-semibold">Status Karyawan</span>
          @if($karyawan)
            <span class="badge bg-success-subtle text-success fw-medium px-2 py-1 rounded-pill">
              <i class="bi bi-link-45deg me-1"></i>Terhubung
            </span>
          @else
            <span class="badge bg-warning-subtle text-warning fw-medium px-2 py-1 rounded-pill">
              <i class="bi bi-exclamation-triangle me-1"></i>Belum Terhubung
            </span>
          @endif
        </div>

      </div>
    </div>
  </div>

  {{-- DETAIL DATA AKUN & KARYAWAN (SISI KANAN) --}}
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white py-3 px-4 border-bottom-0">
        <h5 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-person-lines-fill text-primary"></i> Detail Informasi
        </h5>
      </div>
      <div class="card-body p-4 pt-0">
        
        {{-- SECTION 1: DATA AKUN --}}
        <h6 class="fw-bold text-primary small text-uppercase mb-3" style="letter-spacing: 0.5px;">Data Akun (Sistem)</h6>
        <div class="row g-3 mb-4">
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded-3">
              <span class="text-muted small d-block mb-1">Nama Pengguna</span>
              <span class="fw-semibold text-dark">{{ $user->name }}</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded-3">
              <span class="text-muted small d-block mb-1">Username</span>
              <span class="fw-semibold text-dark font-monospace">{{ $user->username }}</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded-3">
              <span class="text-muted small d-block mb-1">Email System</span>
              <span class="fw-semibold text-dark">{{ $user->email }}</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded-3">
              <span class="text-muted small d-block mb-1">Role Akses</span>
              <span class="fw-semibold text-dark">{{ $user->role->name ?? '-' }}</span>
            </div>
          </div>
        </div>

        <hr class="my-4 text-muted opacity-25">

        {{-- SECTION 2: DATA KARYAWAN --}}
        <h6 class="fw-bold text-primary small text-uppercase mb-3" style="letter-spacing: 0.5px;">Data Karyawan Terkait</h6>
        
        @if($karyawan)
          <div class="row g-3">
            <div class="col-sm-6">
              <div class="p-3 bg-light rounded-3">
                <span class="text-muted small d-block mb-1">Nama Lengkap Karyawan</span>
                <span class="fw-semibold text-dark">{{ $karyawan->nama }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 bg-light rounded-3">
                <span class="text-muted small d-block mb-1">No. Pegawai / NIK</span>
                <span class="fw-semibold text-dark font-monospace">{{ $karyawan->no_pegawai ?? '-' }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 bg-light rounded-3">
                <span class="text-muted small d-block mb-1">Divisi</span>
                <span class="fw-semibold text-dark">{{ optional($karyawan->divisi)->nama_divisi ?? '-' }}</span>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 bg-light rounded-3">
                <span class="text-muted small d-block mb-1">Jabatan</span>
                <span class="fw-semibold text-dark">{{ optional($karyawan->jabatan)->nama_jabatan ?? '-' }}</span>
              </div>
            </div>
          </div>
        @else
          <div class="alert alert-warning border-0 rounded-3 d-flex align-items-center gap-3 p-3 mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
            <div>
              <div class="fw-bold">Akun Belum Terhubung</div>
              <div class="small">Akun Anda belum dikaitkan dengan data karyawan. Silakan hubungi Administrator untuk melakukan sinkronisasi data.</div>
            </div>
          </div>
        @endif

      </div>
    </div>
  </div>

</div>

<script>
  document.getElementById('fotoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const preview = document.getElementById('avatarPreview');
        const initials = document.getElementById('avatarInitials');
        
        preview.src = e.target.result;
        preview.classList.remove('d-none');
        
        if(initials) {
          initials.classList.add('d-none');
        }
      }
      reader.readAsDataURL(file);
    }
  });
</script>
@endsection