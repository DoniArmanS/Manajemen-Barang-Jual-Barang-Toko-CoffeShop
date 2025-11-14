@php($title = $title ?? 'CoffeShop')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }}</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Soft UI core CSS --}}
  <link rel="stylesheet" href="{{ asset('assets/css/soft-ui-dashboard.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/nucleo-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/nucleo-svg.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/topbar.css') }}">
  {{-- kecilkan sentuhan dropdown agar tampak sama persis seperti brand link --}}
  <style>
    .sidenav-header .dropdown-toggle::after { display:none; }          /* hilangkan caret */
    .sidenav-header .navbar-brand { padding:0; line-height:1; }        /* samakan tinggi */
    .sidenav-header .dropdown-menu { min-width:180px; }                /* lebar menu */
  </style>
  @stack('styles')
</head>
<body class="g-sidenav-show bg-gray-100">

  {{-- SIDEBAR --}}
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
    <div class="sidenav-header d-flex align-items-center">
      {{-- BRAND menjadi dropdown, tampilan tetap sejajar dengan sidebar --}}
      <div class="dropdown">
        <a href="#" class="navbar-brand m-0 d-inline-flex align-items-center dropdown-toggle ms-4 my-1"
           id="sidebarBrandDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="ms-1 font-weight-bold">CoffeShop Admin</span>
        </a>
        <ul class="dropdown-menu shadow" aria-labelledby="sidebarBrandDropdown">
          {{-- Tambah item lain di sini jika perlu (Profile/Settings) --}}
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i class="ni ni-user-run me-2"></i> Logout
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
    <hr class="horizontal dark mt-0">

    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        
    {{-- Section title --}}
    <li class="nav-item mt-3">
      <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">CoffeShop</h6>
    </li>

    {{-- Dashboard (Admin only) --}}
    @if(auth()->user()->role === 'admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
          <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
        </div>
        <span class="nav-link-text ms-1">Dashboard</span>
      </a>
    </li>
    @endif

    {{-- Cashier (Available for both Admin and Cashier) --}}
    <li class="nav-item">
      <a class="nav-link {{ request()->is('kasir') ? 'active' : '' }}" href="{{ route('cashier') }}">
        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
          <i class="ni ni-badge text-dark text-sm opacity-10"></i>
        </div>
        <span class="nav-link-text ms-1">Cashier</span>
      </a>
    </li>

    {{-- Cashier Management (Admin only) --}}
    @if(auth()->user()->role === 'admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('cashier.manage') ? 'active' : '' }}" href="{{ route('cashier.manage') }}">
        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
          <i class="ni ni-settings-gear-65 text-warning text-sm opacity-10"></i>
        </div>
        <span class="nav-link-text ms-1">Cashier Management</span>
      </a>
    </li>
    @endif

    {{-- Inventory (Admin only) --}}
    @if(auth()->user()->role === 'admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->is('inventory') ? 'active' : '' }}" href="{{ route('inventory') }}">
        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
          <i class="ni ni-archive-2 text-success text-sm opacity-10"></i>
        </div>
        <span class="nav-link-text ms-1">Inventory</span>
      </a>
    </li>
    @endif

    {{-- Riwayat (Admin only) --}}
    @if(auth()->user()->role === 'admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->is('riwayat') ? 'active' : '' }}" href="{{ url('riwayat') }}">
        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
          <i class="ni ni-time-alarm text-info text-sm opacity-10"></i>
        </div>
        <span class="nav-link-text ms-1">Riwayat</span>
      </a>
    </li>
    @endif

</ul>

    </div>
  </aside>

  {{-- MAIN --}}
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    {{-- Top navbar --}}
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-2 px-3">
        <div class="brand-chip">
          <div class="chip-icon bg-gradient-warning shadow">
            <i class="ni ni-shop text-white"></i>
          </div>
          <div class="chip-title">
            <h5 class="mb-0">CoffeShop</h5>
            <div class="chip-sub">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item text-sm"><a class="opacity-8" href="{{ route('dashboard') }}">Dashboard</a></li>
                  <li class="breadcrumb-item text-sm active" aria-current="page">{{ $title }}</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>

        {{-- JAM — TIDAK DIUBAH --}}
        <div class="ms-auto d-flex align-items-center">
          <div class="clock-chip">
            <i class="ni ni-time-alarm clock-icon"></i>
            <span id="clock" class="clock-text"></span>
          </div>
        </div>

        @push('scripts')
        <script>
          function updateClock() {
            const d = new Date();
            const formatted = d.toLocaleString('id-ID', {
              weekday: 'short', day: '2-digit', month: 'short', year: 'numeric',
              hour: '2-digit', minute: '2-digit'
            });
            const el = document.getElementById('clock');
            if (el) el.textContent = formatted;
          }
          updateClock();
          setInterval(updateClock, 60 * 1000);
        </script>
        @endpush

      </div>
    </nav>

    @yield('content')
  </main>

  {{-- JS --}}
  <script src="{{ asset('assets/js/core/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>
  <script src="{{ asset('assets/js/soft-ui-dashboard.min.js') }}"></script>
  @stack('scripts')
</body>
<!-- anchor untuk print-only -->
<div id="printRoot" style="display:none"></div>
</html>
