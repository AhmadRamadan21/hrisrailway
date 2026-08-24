@extends('layouts.app')

@php($pageTitle = 'Tambah Karyawan Baru')

@section('content')

{{-- HEADER & BREADCRUMB --}}
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1 text-dark">Tambah Karyawan Baru</h4>
    <p class="text-muted small mb-0">Isi formulir di bawah ini untuk menambahkan pegawai baru ke sistem</p>
  </div>
  <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
    <i class="bi bi-arrow-left me-1"></i> Kembali
  </a>
</div>

{{-- FORM TAMBAH KARYAWAN --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
  <div class="card-body p-4">
    <form action="{{ route('karyawan.store') }}" method="POST">
      @csrf
      
      <div class="row g-3">
        {{-- Nama Lengkap --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">Nama Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="nama" class="form-control rounded-3 @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap" required>
          @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- No Pegawai --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">No Pegawai / NIK</label>
          <input type="text" name="no_pegawai" class="form-control rounded-3 @error('no_pegawai') is-invalid @enderror" value="{{ old('no_pegawai') }}" placeholder="Contoh: EMP-001">
          @error('no_pegawai')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Email --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@perusahaan.com" required>
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- No HP --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">No HP / WhatsApp</label>
          <input type="text" name="no_hp" class="form-control rounded-3 @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" placeholder="08123456789">
          @error('no_hp')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Tanggal Lahir --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" class="form-control rounded-3 @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}">
          @error('tanggal_lahir')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Status --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">Status Kerja <span class="text-danger">*</span></label>
          <select name="status" class="form-select rounded-3 @error('status') is-invalid @enderror" required>
            <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="Nonaktif" {{ old('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
          </select>
          @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Divisi --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">Divisi</label>
          <select name="divisi_id" class="form-select rounded-3 @error('divisi_id') is-invalid @enderror">
            <option value="" selected>-- Pilih Divisi --</option>
            @foreach($divisis as $d)
              <option value="{{ $d->id }}" {{ old('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
            @endforeach
          </select>
          @error('divisi_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Jabatan --}}
        <div class="col-md-6">
          <label class="form-label fw-semibold text-dark small">Jabatan</label>
          <select name="jabatan_id" class="form-select rounded-3 @error('jabatan_id') is-invalid @enderror">
            <option value="" selected>-- Pilih Jabatan --</option>
            @foreach($jabatans as $j)
              <option value="{{ $j->id }}" {{ old('jabatan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
            @endforeach
          </select>
          @error('jabatan_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Alamat --}}
        <div class="col-12">
          <label class="form-label fw-semibold text-dark small">Alamat Domisili</label>
          <textarea name="alamat" class="form-control rounded-3 @error('alamat') is-invalid @enderror" rows="3" placeholder="Masukkan alamat lengkap karyawan">{{ old('alamat') }}</textarea>
          @error('alamat')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <hr class="my-4 text-muted opacity-25">

      {{-- Action Buttons --}}
      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('karyawan.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
          <i class="bi bi-save me-1"></i> Simpan Data Karyawan
        </button>
      </div>

    </form>
  </div>
</div>

@endsection