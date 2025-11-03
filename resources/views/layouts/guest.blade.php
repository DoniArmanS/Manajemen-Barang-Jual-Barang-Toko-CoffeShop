<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Login' }}</title>

  {{-- Soft UI CSS --}}
  <link rel="stylesheet" href="{{ asset('assets/css/nucleo-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/nucleo-svg.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/soft-ui-dashboard.css') }}">

  <style>
    /* util kecil agar sesuai screenshot */
    .object-fit-cover { object-fit: cover; }
    .login-page { min-height: 75vh; }
    /* panel kanan dengan irisan diagonal putih seperti foto */
    .right-hero {
      position: relative;
      border-radius: 1rem;
      overflow: hidden;
    }
    .right-hero .hero-img {
      position: absolute; inset: 0;
      width: 100%; height: 100%;
      object-fit: cover;
    }
    .right-hero .hero-mask {             /* gradasi ungu–pink */
      position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(67,97,238,.6), rgba(236,64,122,.6));
      mix-blend-mode: multiply;
    }
    .right-hero .hero-wedge {            /* irisan putih di kiri atas */
      position: absolute; inset: 0;
      background: #fff;
      opacity: .9;
      clip-path: polygon(0 0, 65% 0, 55% 40%, 0 40%);
    }
    @media (max-width: 991.98px) {
      .right-hero { display:none; }
    }
  </style>
</head>
<body class="g-sidenav-show bg-gray-100">
  {{ $slot }}

  {{-- Soft UI JS --}}
  <script src="{{ asset('assets/js/soft-ui-dashboard.min.js') }}"></script>
</body>
</html>
