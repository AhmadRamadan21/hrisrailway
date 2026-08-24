@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="fw-bold mb-0">Tambah User</h3>
  <a href="{{ route('pengguna.index') }}" class="text-decoration-none text-dark fw-semibold">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4" style="max-width: 640px;">
  <div class="card-body p-2">

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('pengguna.store') }}" method="POST">
      @csrf

      <div class="mb-3">
        <label class="form-label fw-bold">Username</label>
        <input type="text" name="username" value="{{ old('username') }}"
               class="form-control form-control-lg rounded-3" required>
      </div>

      <div class="mb-1">
        <label class="form-label fw-bold">Password</label>
        <input type="password" name="password"
               class="form-control form-control-lg rounded-3 bg-light" required minlength="6">
      </div>
      <p class="text-muted small mb-3">
        Password disimpan apa adanya (tanpa hashing) mengikuti implementasi login yang ada.
      </p>

      <div class="mb-4">
        <label class="form-label fw-bold">Role</label>
        <select name="role" class="form-select form-select-lg rounded-3" required>
          <option value="user" @selected(old('role')=='user')>User</option>
          <option value="superadmin" @selected(old('role')=='superadmin')>Superadmin</option>
        </select>
      </div>

      <button type="submit" class="btn btn-dark rounded-3 px-4 py-2">
        <i class="bi bi-download"></i> Simpan
      </button>
    </form>

  </div>
</div>

@endsection