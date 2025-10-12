@php($title = $title ?? 'CoffeShop')

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }}</title>

  {{-- Soft UI core CSS (wajib sudah ada di public/assets dari template) --}}
  <link rel="stylesheet" href="{{ asset('assets/css/soft-ui-dashboard.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/nucleo-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/nucleo-svg.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/topbar.css') }}">
  @stack('styles')
</head>
<body class="g-sidenav-show bg-gray-100">

  {{-- SIDEBAR ala Soft UI (copy style dari template) --}}
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
    <div class="sidenav-header">
      <a class="navbar-brand m-0" href="{{ url('/') }}">
        <span class="ms-1 font-weight-bold">CoffeShop Admin</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">

    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">

        {{-- Dashboard --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('dashboard') }}">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>

        {{-- Section: Laravel Templates (seperti template aslinya) --}}
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Laravel Templates</h6>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('user-profile') ? 'active' : '' }}" href="#!">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">User Profile</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('user-management') ? 'active' : '' }}" href="#!">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-badge text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">User Management</span>
          </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->is('kasir') ? 'active' : '' }}" href="{{ route('cashier') }}">
              <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="ni ni-badge text-dark text-sm opacity-10"></i>
              </div>
              <span class="nav-link-text ms-1">Cashier</span>
            </a>
          </li>

        {{-- Section: Example Pages --}}
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Example Pages</h6>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('tables') ? 'active' : '' }}" href="#!">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-align-left-2 text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Tables</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('billing') ? 'active' : '' }}" href="#!">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-credit-card text-warning text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Billing</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('rtl') ? 'active' : '' }}" href="#!">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-world text-info text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">RTL</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('virtual-reality') ? 'active' : '' }}" href="#!">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-app text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Virtual Reality</span>
          </a>
        </li>

        {{-- INVENTORY (kita tambahkan di sidebar) --}}
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">CoffeShop</h6>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->is('inventory') ? 'active' : '' }}" href="{{ route('inventory') }}">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-archive-2 text-success text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Inventory</span>
          </a>
        </li>

      </ul>
    </div>
  </aside>

  {{-- MAIN --}}
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    {{-- Top navbar tipis (biar rapih, opsional) --}}
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
  <div class="container-fluid py-2 px-3">

    {{-- Brand chip kiri --}}
    <div class="brand-chip">
      <div class="chip-icon bg-gradient-warning shadow">
        <i class="ni ni-shop text-white"></i>
      </div>
      <div class="chip-title">
        <h5 class="mb-0">CoffeShop</h5>
        <div class="chip-sub">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item text-sm"><a class="opacity-8" href="{{ url('/') }}">Dashboard</a></li>
              <li class="breadcrumb-item text-sm active" aria-current="page">{{ $title }}</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>

    {{-- Kanan: badge kecil + tanggal --}}
    <div class="ms-auto d-flex align-items-center gap-2">
      <span class="badge bg-gradient-dark text-xxs">v0.1</span>
      <span class="text-sm text-secondary d-none d-md-inline">{{ now()->format('D, d M Y') }}</span>
    </div>

  </div>
</nav>

    @yield('content')
  </main>

  {{-- JS global --}}
  <script src="{{ asset('assets/js/core/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>
  <script src="{{ asset('assets/js/soft-ui-dashboard.min.js') }}"></script>
  @stack('scripts')
</body>
</html>
