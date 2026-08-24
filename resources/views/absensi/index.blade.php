@extends('layouts.app')

@section('content')
<style>
  .stat-card-widget {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    color: inherit;
    display: block;
    overflow: hidden;
    position: relative;
  }
  
  .stat-card-widget:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
  }

  .stat-card-widget.active-card {
    ring: 2px solid #2563eb;
    border: 2px solid #2563eb;
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
  }

  .icon-shape {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
  }

  .custom-card {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
  }

  .photo-thumbnail {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.2s ease;
    border: 2px solid #e2e8f0;
  }

  .photo-thumbnail:hover {
    transform: scale(1.1);
    border-color: #2563eb;
  }

  .table-custom th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
  }

  .table-custom td {
    padding: 14px 16px;
    vertical-align: middle;
    font-size: 14px;
  }
</style>

<div class="container-fluid p-0">
  <!-- Header Page -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-calendar-check text-primary me-2"></i>Manajemen Absensi</h4>
      <p class="text-muted small mb-0">Kelola dan pantau riwayat presensi harian seluruh karyawan.</p>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex flex-wrap align-items-center gap-2">
      <a href="{{ route('absensi.selfie') }}" class="btn btn-success rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-camera-fill"></i> Absen Selfie (Radius 500m)
      </a>
      <a href="{{ route('scan-qr.index') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-qr-code-scan"></i> Scan QR Absensi
      </a>
    </div>
  </div>

  @if(empty($linked) || empty($linked->karyawan))
    <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3">
      <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
      <div>
        <h6 class="fw-bold mb-0">Akun Belum Terkait Karyawan</h6>
        <small>Akun Anda belum dihubungkan dengan profil karyawan. Silakan hubungi administrator untuk mengaitkan akun.</small>
      </div>
    </div>
  @endif

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    @php
      $baseQuery = request()->except(['status', 'page']);
      $cards = [
        [
          'label' => 'Total Absensi',
          'count' => $totalAbsensi,
          'status' => null,
          'bgIcon' => 'bg-primary-subtle',
          'textColor' => 'text-primary',
          'icon' => 'bi-journal-check'
        ],
        [
          'label' => 'Total Hadir',
          'count' => $countHadir,
          'status' => 'Hadir',
          'bgIcon' => 'bg-success-subtle',
          'textColor' => 'text-success',
          'icon' => 'bi-check-circle-fill'
        ],
        [
          'label' => 'Telat',
          'count' => $countTelat,
          'status' => 'Telat',
          'bgIcon' => 'bg-warning-subtle',
          'textColor' => 'text-warning',
          'icon' => 'bi-alarm-fill'
        ],
        [
          'label' => 'Alpa',
          'count' => $countAlpa,
          'status' => 'Alpa',
          'bgIcon' => 'bg-danger-subtle',
          'textColor' => 'text-danger',
          'icon' => 'bi-x-circle-fill'
        ],
      ];
    @endphp

    @foreach($cards as $card)
      <div class="col-6 col-md-3">
        <a href="{{ route('absensi.index', array_filter(array_merge($baseQuery, ['status' => $card['status']]), fn($value) => $value !== null && $value !== '')) }}" 
           class="stat-card-widget p-3 {{ request('status') === $card['status'] ? 'active-card' : '' }}">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-medium d-block mb-1">{{ $card['label'] }}</span>
              <h3 class="fw-bold mb-0 {{ $card['textColor'] }}">{{ number_format($card['count']) }}</h3>
            </div>
            <div class="icon-shape {{ $card['bgIcon'] }} {{ $card['textColor'] }}">
              <i class="bi {{ $card['icon'] }}"></i>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>

  <!-- Filter Section -->
  <div class="card custom-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="fw-bold mb-0"><i class="bi bi-funnel text-primary me-2"></i>Filter Data Absensi</h6>
      <div>
        <a href="{{ route('absensi.export.excel', request()->all()) }}" class="btn btn-sm btn-success fw-semibold me-2 shadow-sm">
          <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        @if(request()->hasAny(['from', 'to', 'divisi_id', 'karyawan_id', 'status']))
          <a href="{{ route('absensi.index') }}" class="btn btn-sm btn-link text-decoration-none text-danger p-0 fw-semibold">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
          </a>
        @endif
      </div>
    </div>

    <form method="GET" action="{{ route('absensi.index') }}" class="row g-3">
      @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
      @endif
      @php
        $isAdminRole = auth()->user()->hasRole(['Admin', 'Super Admin']);
        $dateColClass = $isAdminRole ? 'col-md-3' : 'col-md-5';
      @endphp

      <div class="{{ $dateColClass }}">
        <label class="form-label small text-muted fw-semibold">Dari Tanggal</label>
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar text-secondary"></i></span>
          <input type="date" class="form-control bg-light border-start-0" name="from" value="{{ request('from', \Carbon\Carbon::today()->toDateString()) }}">
        </div>
      </div>

      <div class="{{ $dateColClass }}">
        <label class="form-label small text-muted fw-semibold">Sampai Tanggal</label>
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-check text-secondary"></i></span>
          <input type="date" class="form-control bg-light border-start-0" name="to" value="{{ request('to', \Carbon\Carbon::today()->toDateString()) }}">
        </div>
      </div>

      @if($isAdminRole)
        <div class="col-md-2">
          <label class="form-label small text-muted fw-semibold">Divisi</label>
          <select class="form-select bg-light" name="divisi_id">
            <option value="">Semua Divisi</option>
            @foreach($divisis as $d)
              <option value="{{ $d->id }}" {{ request('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small text-muted fw-semibold">Karyawan</label>
          <select class="form-select bg-light" name="karyawan_id">
            <option value="">Semua Karyawan</option>
            @foreach($karyawans as $k)
              <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <div class="col-md-2 align-self-end">
        <button class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
          <i class="bi bi-search me-1"></i> Tampilkan
        </button>
      </div>
    </form>
  </div>

  <!-- Data Table Card -->
  <div class="card custom-card overflow-hidden">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i>Riwayat Presensi Terbaru</h6>
      <span class="badge bg-light text-dark px-3 py-2 rounded-pill border fw-normal">
        Menampilkan {{ count($recent) }} data
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-custom table-hover align-middle mb-0" data-no-datatable>
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Foto</th>
            <th>Karyawan</th>
            <th>Divisi</th>
            <th>Jam Absen</th>
            @if(auth()->user()->hasRole(['Admin', 'Super Admin']))
              <th>Jarak GPS</th>
            @endif
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @if(!empty($recent) && count($recent))
            @foreach($recent as $row)
              <tr>
                <td class="fw-semibold text-dark">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event text-secondary"></i>
                    {{ optional($row->tanggal)->format('d M Y') ?? '-' }}
                  </div>
                </td>
                <td>
                  @if($row->photo && str_starts_with($row->photo, 'data:image'))
                    <img src="{{ $row->photo }}" alt="Foto Absensi" class="photo-thumbnail shadow-sm" onclick="showPhotoModal('{{ $row->photo }}', '{{ optional($row->karyawan)->nama }}')" />
                  @elseif($row->photo)
                    <img src="{{ asset($row->photo) }}" alt="Foto Absensi" class="photo-thumbnail shadow-sm" onclick="showPhotoModal('{{ asset($row->photo) }}', '{{ optional($row->karyawan)->nama }}')" />
                  @else
                    <span class="badge bg-light text-secondary border px-2 py-1"><i class="bi bi-camera-off me-1"></i>Tanpa Foto</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle bg-primary-subtle text-primary fw-bold" style="width:34px; height:34px; font-size:12px;">
                      {{ strtoupper(substr(optional($row->karyawan)->nama ?? 'U', 0, 2)) }}
                    </div>
                    <span class="fw-bold text-dark">{{ optional($row->karyawan)->nama ?? '-' }}</span>
                  </div>
                </td>
                <td>
                  <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                    {{ optional($row->karyawan->divisi)->nama_divisi ?? '-' }}
                  </span>
                </td>
                <td>
                  @if($row->masuk)
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill border border-success-subtle fw-semibold">
                      <i class="bi bi-clock me-1"></i>{{ optional($row->masuk)->format('H:i:s') }}
                    </span>
                  @else
                    <span class="text-muted small">-</span>
                  @endif
                </td>
                @if(auth()->user()->hasRole(['Admin', 'Super Admin']))
                  <td>
                    @if($row->distance !== null)
                      <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-geo-alt me-1"></i>{{ $row->distance }}m
                      </span>
                    @else
                      <span class="text-muted small">-</span>
                    @endif
                  </td>
                @endif
                <td>
                  @if($row->status === 'Hadir')
                    <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold">
                      <i class="bi bi-check-circle me-1"></i>Hadir
                    </span>
                  @elseif($row->status === 'Telat')
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
                      <i class="bi bi-alarm me-1"></i>Telat
                    </span>
                  @elseif($row->status === 'Alpa')
                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold">
                      <i class="bi bi-x-circle me-1"></i>Alpa
                    </span>
                  @else
                    <span class="badge bg-light text-dark px-3 py-2 rounded-pill border">{{ $row->status ?? '-' }}</span>
                  @endif
                </td>
              </tr>
            @endforeach
          @else
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                Tidak ada data absensi yang ditemukan.
              </td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
    
    @if(method_exists($recent, 'links'))
      <div class="card-footer bg-white border-top py-3">
        {{ $recent->links('pagination::bootstrap-5') }}
      </div>
    @endif
  </div>
</div>

<!-- Modal Preview Foto Absensi -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-bold" id="photoModalTitle">Foto Presensi</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <img id="modalPhotoSrc" src="" alt="Preview Foto" class="img-fluid rounded-3 shadow" style="max-height: 420px; object-fit: contain;">
      </div>
    </div>
  </div>
</div>

<script>
function showPhotoModal(src, name) {
  document.getElementById('modalPhotoSrc').src = src;
  document.getElementById('photoModalTitle').textContent = 'Foto Presensi: ' + (name || 'Karyawan');
  const modal = new bootstrap.Modal(document.getElementById('photoModal'));
  modal.show();
}
</script>
@endsection
