<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $pageTitle ?? 'HRIS IBP' }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/style.min.css" rel="stylesheet">
  <style>
    * { font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; box-sizing: border-box; }
    body { margin: 0; background: linear-gradient(180deg, #eef4ff 0%, #f8fafc 100%); color: #111827; }

    .app-shell { display: flex; min-height: 100vh; }

    /* --- SIDEBAR RESPONSIVE FIXED --- */
    .sidebar {
      width: 250px; flex-shrink: 0; background: #ffffff;
      border-right: 1px solid #e5e7eb; display: flex; flex-direction: column;
      position: sticky; top: 0; height: 100vh; overflow-y: auto; z-index: 1000;
    }

    /* Di layar HP (di bawah 768px), ubah Sidebar menjadi Drawer Slide (Offcanvas) */
    @media (max-width: 767.98px) {
      .sidebar {
        position: fixed;
        left: -100%;
        top: 0;
        bottom: 0;
        height: 100%;
        transition: left 0.3s ease-in-out;
        box-shadow: 0 0 20px rgba(0,0,0,0.15);
      }
      .sidebar.show {
        left: 0;
      }
    }

    .sidebar-logo {
      padding: 20px 18px; font-weight: 800; font-size: 17px; color: #111827;
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 1px solid #f1f5f9;
      background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
    }
    .sidebar-logo-brand {
      display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
    }
    .sidebar-logo i { color: #1E3A8A; font-size: 26px; }
    .sidebar-nav { padding: 16px 12px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
    .sidebar-section {
      font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;
      letter-spacing: .05em; padding: 14px 14px 6px;
    }
    
    .sidebar .nav-item {
      display: flex; align-items: center; gap: 12px; padding: 11px 14px;
      border-radius: 12px; color: #475569; text-decoration: none; font-size: 13.75px;
      font-weight: 500; border: none; background: transparent; width: 100%;
      text-align: left; cursor: pointer; transition: background .2s ease, color .2s ease;
    }
    .sidebar .nav-item i { font-size: 16px; width: 18px; text-align: center; }
    .sidebar .nav-item:hover { background: #eef3ff; color: #1d4ed8; }
    .sidebar .nav-item.active { background: #1e40af; color: #ffffff; font-weight: 600; }
    .sidebar .nav-item-danger { color: #dc2626; }
    .sidebar .nav-item-danger:hover { background: #fef2f2; }
    .sidebar .nav-item-toggle { justify-content: space-between; }
    .sidebar .nav-item-toggle .chevron { transition: transform .2s ease; font-size: 12px; }
    .sidebar .nav-item-toggle[aria-expanded="true"] .chevron { transform: rotate(180deg); }
    .nav-submenu { display: flex; flex-direction: column; gap: 4px; padding: 8px 0 8px 26px; }
    .nav-subitem {
      padding: 8px 12px; border-radius: 8px; color: #64748b; text-decoration: none;
      font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 10px;
    }
    .nav-subitem:hover { background: #f1f5f9; color: #334155; }
    .nav-subitem.active { color: #172554; font-weight: 700; }
    .sidebar-nav-bottom { margin-top: auto; padding: 12px; border-top: 1px solid #f1f5f9; }

    /* Topbar */
    .main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .topbar {
      background: rgba(255,255,255,0.95); border-bottom: 1px solid #e5e7eb; padding: 18px 28px;
      display: flex; justify-content: space-between; align-items: center;
      box-shadow: 0 12px 24px rgba(15,23,42,.06);
      position: sticky; top: 0; z-index: 10;
      backdrop-filter: blur(12px);
    }
    .topbar-left {
      display: flex; align-items: center; gap: 12px;
    }
    .page-title {
      margin: 0; font-size: 18px; font-weight: 700; color: #0f172a;
    }
    .page-subtitle {
      margin: 0; font-size: 13px; color: #6b7280;
    }
    .topbar-user { display: flex; align-items: center; gap: 12px; }
    .notification-bell {
      position: relative; margin-right: 4px;
    }
    .notification-bell .btn {
      border-radius: 50%; width: 40px; height: 40px; display: inline-flex;
      align-items: center; justify-content: center; padding: 0; color: #475569;
      border: 1px solid #d1d5db; background: #f8fafc;
    }
    .notification-bell .btn:hover {
      background: #f8fafc;
    }
    .notification-badge {
      position: absolute; top: 0; right: 0; transform: translate(25%, -25%);
      min-width: 18px; height: 18px; border-radius: 50%; font-size: 11px;
      line-height: 18px; background: #dc2626; color: #fff; text-align: center;
      padding: 0 5px;
    }

    .avatar-circle {
      width: 38px; height: 38px; border-radius: 50%; background: #2563eb; color: #ffffff;
      display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
    }
    .user-name { font-weight: 700; font-size: 13px; color: #0f172a; letter-spacing: .02em; }

    .content-area { padding: 24px; padding-bottom: 90px; }
    .content-inner { max-width: 1240px; margin: 0 auto; }

    /* Overlay latar belakang saat sidebar HP dibuka */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.4);
      backdrop-filter: blur(2px);
      z-index: 999;
    }
    .sidebar-overlay.show {
      display: block;
    }

    @media (max-width: 576px) {
      .topbar { padding: 14px 16px; }
      .content-area { padding: 16px; padding-bottom: 80px; }
      .user-details { display: none; } /* Menyembunyikan teks nama user di HP agar tidak padat */
    }
  </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-shell">
  <!-- Sidebar -->
  <aside class="sidebar" id="mobileSidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-brand">
        <img src="{{ asset('assets/logo-ibp-icon.svg') }}" alt="Logo" style="height: 35px; width: 35px;">
        <span>HRIS IBP</span>
      </div>
      <!-- Tombol Tutup khusus di HP -->
      <button type="button" class="btn-close d-md-none" id="closeSidebarBtn" aria-label="Close"></button>
    </div>
    
    <nav class="sidebar-nav">
      <!-- User Info Card khusus Mobile -->
      <div class="d-md-none p-3 mb-2 rounded-3 bg-light border">
        <div class="d-flex align-items-center gap-2">
          @if(auth()->user()->foto)
            <img src="{{ asset('storage/' . auth()->user()->foto) }}" class="rounded-circle object-fit-cover" style="width: 36px; height: 36px;">
          @else
            <div class="avatar-circle" style="width: 36px; height: 36px; font-size: 13px;">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
          @endif
          <div class="overflow-hidden">
            <div class="user-name text-truncate" style="font-size:13px;">{{ auth()->user()->name }}</div>
            <div class="text-muted text-truncate" style="font-size: 11px;">{{ auth()->user()->role->name ?? '-' }}</div>
          </div>
        </div>
      </div>

      <a href="{{ url('/dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Beranda
      </a>

      @if(auth()->user()->hasRole(['Admin', 'Super Admin']))
        <button class="nav-item nav-item-toggle" type="button" data-bs-toggle="collapse"
                data-bs-target="#dataKaryawanMenu"
                aria-expanded="{{ request()->is('list-karyawan*','divisi-jabatan*','pengguna*') ? 'true' : 'false' }}">
          <span class="d-flex align-items-center gap-2">
            <i class="bi bi-people"></i> Data Karyawan
          </span>
          <i class="bi bi-chevron-down chevron"></i>
        </button>
        <div class="collapse {{ request()->is('list-karyawan*','divisi-jabatan*','pengguna*') ? 'show' : '' }}" id="dataKaryawanMenu">
          <div class="nav-submenu">
            <a href="{{ route('karyawan.index') }}" class="nav-subitem {{ request()->is('list-karyawan*') ? 'active' : '' }}">
              <i class="bi bi-person-lines-fill"></i> List Karyawan
            </a>
            <a href="{{ route('divisi-jabatan.index') }}" class="nav-subitem {{ request()->is('divisi-jabatan*') ? 'active' : '' }}">
              <i class="bi bi-diagram-3"></i> Divisi & Jabatan
            </a>
            <a href="{{ route('pengguna.index') }}" class="nav-subitem {{ request()->is('pengguna*') ? 'active' : '' }}">
              <i class="bi bi-person-badge"></i> Pengguna
            </a>
            <a href="{{ route('ai-knowledge.index') }}" class="nav-subitem {{ request()->is('ai-knowledge*') ? 'active' : '' }}">
              <i class="bi bi-robot text-primary"></i> Kelola AI Knowledge
            </a>
          </div>
        </div>
      @endif

      <button class="nav-item nav-item-toggle {{ request()->is('absensi*','scan-qr*') ? 'active' : '' }}" type="button" data-bs-toggle="collapse"
              data-bs-target="#absensiMenu"
              aria-expanded="{{ request()->is('absensi*','scan-qr*') ? 'true' : 'false' }}">
        <span class="d-flex align-items-center gap-2">
          <i class="bi bi-calendar-check"></i> Absensi
        </span>
        <i class="bi bi-chevron-down chevron"></i>
      </button>
      <div class="collapse {{ request()->is('absensi*','scan-qr*') ? 'show' : '' }}" id="absensiMenu">
        <div class="nav-submenu">
          <a href="{{ route('absensi.selfie') }}" class="nav-subitem {{ request()->routeIs('absensi.selfie') ? 'active' : '' }}">
            <i class="bi bi-camera"></i> Absen Selfie (500m)
          </a>
          <a href="{{ route('scan-qr.index') }}" class="nav-subitem {{ request()->routeIs('scan-qr.index') ? 'active' : '' }}">
            <i class="bi bi-qr-code-scan"></i> Scan QR Code
          </a>
          <a href="{{ route('absensi.index') }}" class="nav-subitem {{ request()->routeIs('absensi.index') ? 'active' : '' }}">
            <i class="bi bi-journal-check"></i> Riwayat Absensi
          </a>
        </div>
      </div>

      <a href="{{ route('kegiatan-harian.index') }}" class="nav-item {{ request()->is('kegiatan-harian*') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i> Kegiatan Harian
      </a>

      <a href="{{ route('cuti.index') }}" class="nav-item {{ request()->is('cuti*') ? 'active' : '' }}">
        <i class="bi bi-calendar-week"></i> Cuti
      </a>

      <a href="{{ route('penggajian.index') }}" class="nav-item {{ request()->is('penggajian*') ? 'active' : '' }}">
        <i class="bi bi-cash-coin"></i> Penggajian
      </a>

      <a href="{{ route('ai-chat.index') }}" class="nav-item {{ request()->routeIs('ai-chat.index') ? 'active' : '' }}">
        <i class="bi bi-chat-square-dots-fill text-primary"></i> AI HRIS
      </a>

      <!-- Tombol Sign Out khusus di menu mobile -->
      <div class="d-md-none mt-3 pt-2 border-top">
        <form method="POST" action="{{ url('/logout') }}">
          @csrf
          <button type="submit" class="nav-item nav-item-danger fw-bold">
            <i class="bi bi-box-arrow-right fs-5"></i> Sign Out / Keluar
          </button>
        </form>
      </div>
    </nav>

    <div class="sidebar-nav-bottom d-none d-md-block">
      <form method="POST" action="{{ url('/logout') }}">
        @csrf
        <button type="submit" class="nav-item nav-item-danger">
          <i class="bi bi-box-arrow-right"></i> Keluar
        </button>
      </form>
    </div>
  </aside>

  <!-- Main Area -->
  <div class="main-area">
    <nav class="topbar">
      <div class="topbar-left">
        <!-- Tombol Hamburger (Hanya muncul di Layar Kecil/HP) -->
        <button class="btn btn-light border d-md-none me-1" id="toggleSidebarBtn" type="button">
          <i class="bi bi-list fs-5"></i>
        </button>
        
        <div>
          <h1 class="page-title">{{ $pageTitle ?? 'Dashboard' }}</h1>
          <p class="page-subtitle d-none d-sm-block">Sistem HRIS internal perusahaan</p>
        </div>
      </div>

      <div class="topbar-user">
        <div class="notification-bell dropdown">
          <a class="btn" href="{{ route('notifications.index') }}" aria-label="Notifikasi">
            <i class="bi bi-bell"></i>
            @if(isset($unreadNotifications) && $unreadNotifications > 0)
              <span class="notification-badge">{{ $unreadNotifications }}</span>
            @endif
          </a>
        </div>
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-reset dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            @if(auth()->user()->foto)
              <img src="{{ asset('storage/' . auth()->user()->foto) }}" class="rounded-circle object-fit-cover" style="width: 36px; height: 36px;">
            @else
              <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            @endif
            <div class="user-details">
              <div class="user-name">{{ auth()->user()->name }}</div>
              <div class="text-muted" style="font-size: 12px; letter-spacing: .02em;">{{ auth()->user()->role->name ?? '-' }}</div>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 mt-2">
            <li>
              <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="{{ route('profile') }}">
                <i class="bi bi-person text-primary"></i> Profil Saya
              </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
              <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger py-2 px-3 d-flex align-items-center gap-2 fw-semibold">
                  <i class="bi bi-box-arrow-right"></i> Sign Out
                </button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="content-area">
      <div class="content-inner">
        @yield('content')
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3/dist/umd/simple-datatables.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Logic untuk Buka / Tutup Sidebar khusus Mobile
  const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
  const closeSidebarBtn = document.getElementById('closeSidebarBtn');
  const mobileSidebar = document.getElementById('mobileSidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  function openSidebar() {
    mobileSidebar.classList.add('show');
    sidebarOverlay.classList.add('show');
  }

  function closeSidebar() {
    mobileSidebar.classList.remove('show');
    sidebarOverlay.classList.remove('show');
  }

  if (toggleSidebarBtn) toggleSidebarBtn.addEventListener('click', openSidebar);
  if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeSidebar);
  if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

  // SweetAlert & Datatables
  @if(session('success'))
  Swal.fire({ title: 'Berhasil!', text: @json(session('success')), icon: 'success', confirmButtonColor: '#1E3A8A', timer: 2500, timerProgressBar: true, customClass: { popup: 'rounded-4 shadow-lg border-0' } });
  @endif
  @if(session('error'))
  Swal.fire({ title: 'Gagal!', text: @json(session('error')), icon: 'error', confirmButtonColor: '#dc3545', customClass: { popup: 'rounded-4 shadow-lg border-0' } });
  @endif

  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      if (this.action.includes('/logout') || this.id === 'logout-form' || this.id === 'aiChatForm' || this.method.toUpperCase() === 'GET') return;
      e.preventDefault();
      const isDelete = this.querySelector('input[name="_method"][value="DELETE"]') !== null;
      const cfg = isDelete
        ? { title:'Konfirmasi Hapus', text:'Data yang dihapus tidak dapat dikembalikan!', icon:'warning', confirmButtonColor:'#dc3545', cancelButtonColor:'#6c757d', confirmButtonText:'Ya, Hapus!', cancelButtonText:'Batal' }
        : { title:'Konfirmasi Simpan', text:'Apakah Anda yakin ingin menyimpan data ini?', icon:'question', confirmButtonColor:'#1E3A8A', cancelButtonColor:'#dc3545', confirmButtonText:'Ya, Simpan!', cancelButtonText:'Batal' };
      cfg.showCancelButton = true;
      cfg.customClass = { popup: 'rounded-4 shadow-lg border-0' };
      Swal.fire(cfg).then(r => {
        if (r.isConfirmed) { Swal.fire({ title: isDelete ? 'Menghapus...' : 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() }); this.submit(); }
      });
    });
  });

  document.querySelectorAll('.table:not([data-no-datatable])').forEach(table => {
    new simpleDatatables.DataTable(table, {
      searchable: true, perPage: 10,
      labels: { placeholder: "Cari data...", perPage: "Data per halaman", noRows: "Tidak ada data ditemukan", info: "Menampilkan {start} sampai {end} dari {rows} data" }
    });
  });
});
</script>
@yield('scripts')

{{-- AI Chat Assistant Widget (Melayang di Seluruh Halaman) --}}
@include('partials.ai_chat_widget')
</body>
</html>