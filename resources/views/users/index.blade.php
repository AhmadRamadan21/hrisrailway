@extends('layouts.app')

@php($pageTitle = 'Data Pengguna')

@section('content')
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
  <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom-0">
    <h5 class="fw-bold mb-0 text-dark">Daftar Pengguna</h5>
    <a href="{{ url('/pengguna/create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
      <i class="bi bi-plus-lg me-1"></i> Tambah User
    </a>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Nama</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Bagian</th>
            <th>Status</th>
            <th class="text-end pe-4">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr>
              <td class="ps-4">
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 11px;">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                  </div>
                  <span class="fw-semibold text-dark">{{ $user->name }}</span>
                </div>
              </td>
              <td>{{ $user->username }}</td>
              <td>{{ $user->email }}</td>
              <td>
                <span class="badge bg-primary-subtle text-primary fw-medium px-2 py-1 rounded-pill">
                  {{ $user->role->name ?? '-' }}
                </span>
              </td>
              <td>{{ $user->bagian->nama_bagian ?? '-' }}</td>
              <td>
                @if($user->status === 'active')
                  <span class="badge bg-success-subtle text-success fw-medium px-2 py-1 rounded-pill">
                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                  </span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary fw-medium px-2 py-1 rounded-pill">
                    Nonaktif
                  </span>
                @endif
              </td>
              <td class="text-end pe-4">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ url('/pengguna/'.$user->id.'/edit') }}" class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  @if($user->id !== auth()->id())
                    <form method="POST" action="{{ url('/pengguna/'.$user->id) }}" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1" title="Hapus">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Belum ada data pengguna.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection