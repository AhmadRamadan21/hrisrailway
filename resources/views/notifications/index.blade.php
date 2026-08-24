@extends('layouts.app')

@section('content')
<style>
  .notification-card {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
  }

  .notification-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.2s ease;
  }

  .notification-item:hover {
    background-color: #f8fafc;
  }

  .notification-item:last-child {
    border-bottom: none;
  }

  .icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
  }
</style>

<div class="container-fluid p-0">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-bell text-primary me-2"></i>Notifikasi System</h4>
      <p class="text-muted small mb-0">Daftar seluruh notifikasi dan pemberitahuan aktivitas HRIS.</p>
    </div>
  </div>

  <div class="card notification-card overflow-hidden">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0">Semua Notifikasi</h6>
      <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">
        {{ $notifications->total() }} Notifikasi
      </span>
    </div>

    <div class="card-body p-0">
      @forelse($notifications as $n)
        <div class="notification-item d-flex align-items-start gap-3">
          <div class="icon-circle bg-primary-subtle text-primary">
            <i class="bi bi-bell-fill"></i>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <h6 class="fw-bold text-dark mb-0" style="font-size: 14.5px;">{{ $n->title }}</h6>
              <small class="text-muted" style="font-size: 12px;">
                <i class="bi bi-clock me-1"></i>{{ optional($n->created_at)->diffForHumans() ?? '-' }}
              </small>
            </div>
            <p class="text-secondary small mb-1">{{ $n->message }}</p>
            @if($n->user)
              <small class="text-muted d-block" style="font-size: 11.5px;">
                <i class="bi bi-person me-1"></i>Oleh: {{ $n->user->name }}
              </small>
            @endif
          </div>
          @if($n->link)
            <a href="{{ $n->link }}" class="btn btn-sm btn-outline-primary rounded-3 px-3">Lihat</a>
          @endif
        </div>
      @empty
        <div class="text-center text-muted py-5">
          <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary"></i>
          <p class="mb-0 small">Belum ada notifikasi.</p>
        </div>
      @endforelse
    </div>
  </div>

  @if($notifications->hasPages())
    <div class="mt-4 d-flex justify-content-center">
      {{ $notifications->links() }}
    </div>
  @endif
</div>
@endsection