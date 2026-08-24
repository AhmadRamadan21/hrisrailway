@extends('layouts.app')

@php($pageTitle = 'Data Pengguna')

@section('content')

{{-- STYLES KHUSUS HALAMAN PENGGUNA --}}
<style>
  .panel {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    margin-bottom: 20px;
    overflow: hidden;
  }

  .panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background-color: #ffffff;
  }

  #tablePengguna {
    table-layout: auto !important;
  }
  #tablePengguna th,
  #tablePengguna td {
    white-space: nowrap !important;
    vertical-align: middle;
  }

  /* Avatar Circle Style */
  .avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #eff6ff;
    color: #2563eb;
    font-weight: 700;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #bfdbfe;
    flex-shrink: 0;
  }

  /* Custom Blue Theme Palette */
  .btn-blue {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
  }
  .btn-blue:hover, .btn-blue:focus {
    background-color: #1d4ed8;
    border-color: #1d4ed8;
    color: #ffffff;
  }

  .btn-outline-blue {
    color: #2563eb;
    border-color: #bfdbfe;
    background-color: #eff6ff;
  }
  .btn-outline-blue:hover, .btn-outline-blue:focus {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
  }

  @media (max-width: 767.98px) {
    .panel-header {
      padding: 14px 16px;
    }
  }
</style>

{{-- HEADER HALAMAN --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0" style="color: #0f172a;">Data Pengguna</h4>
</div>

{{-- NOTIFIKASI --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="panel">
  <div class="panel-header d-flex justify-content-between align-items-center">
    <span class="fw-bold text-dark fs-6">Daftar Pengguna</span>
    <a href="{{ url('/pengguna/create') }}" class="btn btn-blue btn-sm rounded-pill px-3">
      <i class="bi bi-plus-lg me-1"></i> Tambah User
    </a>
  </div>

  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tablePengguna">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Nama</th>
            <th>Username</th>
            <th>Email</th>
            <th style="min-width:130px;">Role</th>
            <th style="min-width:120px;">Bagian</th>
            <th style="min-width:110px;">Status</th>
            <th class="text-end pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            @php
              $roleColors = [
                'Super Admin' => ['bg' => '#1e1b4b', 'text' => '#e0e7ff'],
                'Admin'       => ['bg' => '#2563eb', 'text' => '#ffffff'],
                'User'        => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
              ];
              $roleName = $user->role->name ?? '-';
              $roleColor = $roleColors[$roleName] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
            @endphp
            <tr>
              <td class="ps-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div class="avatar-circle">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                  </div>
                  <span class="fw-semibold" style="color:#0f172a;">{{ $user->name }}</span>
                </div>
              </td>
              <td class="text-secondary">{{ $user->username }}</td>
              <td class="text-secondary">{{ $user->email }}</td>
              <td>
                <span style="background:{{ $roleColor['bg'] }}; color:{{ $roleColor['text'] }}; font-weight:600; font-size:11px; padding: 4px 10px; border-radius: 999px; display: inline-block; line-height:1.3;">
                  {{ $roleName }}
                </span>
              </td>
              <td class="text-secondary">{{ $user->bagian->nama_bagian ?? '-' }}</td>
              <td>
                @if($user->status === 'active')
                  <span style="background:#dcfce7; color:#166534; font-weight:600; font-size:11px; padding: 4px 10px; border-radius: 999px; display:inline-block; line-height:1.3;">
                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                  </span>
                @else
                  <span style="background:#f1f5f9; color:#64748b; font-weight:600; font-size:11px; padding: 4px 10px; border-radius: 999px; display:inline-block; line-height:1.3;">
                    Nonaktif
                  </span>
                @endif
              </td>
              <td class="text-end pe-3">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ url('/pengguna/'.$user->id.'/edit') }}" class="btn btn-sm btn-outline-blue rounded-2 px-2 py-1" title="Edit User">
                    <i class="bi bi-pencil"></i>
                  </a>
                  @if($user->id !== auth()->id())
                    <form method="POST" action="{{ url('/pengguna/'.$user->id) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1" title="Hapus User">
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