@extends('layouts.app')

@php($pageTitle = 'Tambah User')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
      
      {{-- HEADER CARD --}}
      <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom-0">
        <div>
          <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah User Baru
          </h5>
          <small class="text-muted">Isi formulir di bawah ini untuk menambahkan akun pengguna baru</small>
        </div>
        <a href="{{ url('/pengguna') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
      </div>

      {{-- BODY FORM --}}
      <div class="card-body p-4">
        <form method="POST" action="{{ url('/pengguna') }}">
          @csrf

          <div class="row g-3">
            
            {{-- KIRI: NAMA LENGKAP --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">
                <i class="bi bi-person me-1"></i> Nama Lengkap <span class="text-danger">*</span>
              </label>
              <select name="name" id="karyawanSelect" class="form-select rounded-3 @error('name') is-invalid @enderror" required>
                <option value="" selected disabled>-- Pilih Karyawan --</option>
                @foreach($karyawans as $karyawan)
                  @php($hasAccount = \App\Models\User::where('email', $karyawan->email)->exists())
                  <option value="{{ $karyawan->nama }}" data-email="{{ $karyawan->email }}" data-has-account="{{ $hasAccount ? 'true' : 'false' }}" {{ old('name') == $karyawan->nama ? 'selected' : '' }}>
                    {{ $karyawan->nama }} {{ $hasAccount ? '(Sudah Punya Akun)' : '' }}
                  </option>
                @endforeach
              </select>
              @error('name') 
                <div class="invalid-feedback">{{ $message }}</div> 
              @enderror
            </div>

            {{-- KANAN: USERNAME --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">
                <i class="bi bi-at me-1"></i> Username <span class="text-danger">*</span>
              </label>
              <input type="text" name="username" class="form-control rounded-3 @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Masukkan username" required>
              @error('username') 
                <div class="invalid-feedback">{{ $message }}</div> 
              @enderror
            </div>

            {{-- KIRI: EMAIL --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">
                <i class="bi bi-envelope me-1"></i> Email <span class="text-danger">*</span>
              </label>
              <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="contoh@email.com" required>
              @error('email') 
                <div class="invalid-feedback">{{ $message }}</div> 
              @enderror
            </div>

            {{-- KANAN: PASSWORD --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">
                <i class="bi bi-key me-1"></i> Password <span class="text-danger">*</span>
              </label>
              <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" placeholder="••••••••" required>
              @error('password') 
                <div class="invalid-feedback">{{ $message }}</div> 
              @enderror
            </div>

            {{-- KIRI: ROLE --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">
                <i class="bi bi-shield-check me-1"></i> Role <span class="text-danger">*</span>
              </label>
              <select name="role_id" class="form-select rounded-3 @error('role_id') is-invalid @enderror" required>
                <option value="" selected disabled>-- Pilih Role --</option>
                @foreach($roles as $role)
                  <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                  </option>
                @endforeach
              </select>
              @error('role_id') 
                <div class="invalid-feedback">{{ $message }}</div> 
              @enderror
            </div>

            {{-- KANAN: BAGIAN --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">
                <i class="bi bi-building me-1"></i> Bagian / Departemen
              </label>
              <select name="bagian_id" class="form-select rounded-3 @error('bagian_id') is-invalid @enderror">
                <option value="" selected>-- Pilih Bagian --</option>
                @foreach($bagians as $bagian)
                  <option value="{{ $bagian->id }}" {{ old('bagian_id') == $bagian->id ? 'selected' : '' }}>
                    {{ $bagian->nama_bagian }}
                  </option>
                @endforeach
              </select>
              @error('bagian_id') 
                <div class="invalid-feedback">{{ $message }}</div> 
              @enderror
            </div>

            {{-- STATUS (FULL WIDTH ATAU SETENGAH) --}}
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark small">
                <i class="bi bi-toggle-on me-1"></i> Status Akun <span class="text-danger">*</span>
              </label>
              <select name="status" class="form-select rounded-3" required>
                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
              </select>
            </div>

          </div>

          {{-- TOMBOL ACTION --}}
          <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="{{ url('/pengguna') }}" class="btn btn-light rounded-pill px-4 fw-semibold text-secondary">
              Batal
            </a>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
              <i class="bi bi-save me-1"></i> Simpan User
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<script>
  document.getElementById('karyawanSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const email = selectedOption.getAttribute('data-email');
    const hasAccount = selectedOption.getAttribute('data-has-account');
    
    if (hasAccount === 'true') {
      alert('Oopps! Karyawan ini sudah memiliki akun pengguna.');
      this.value = ''; // Reset pilihan
      document.querySelector('input[name="email"]').value = '';
      return;
    }
    
    if (email) {
      document.querySelector('input[name="email"]').value = email;
    }
  });
</script>
@endsection