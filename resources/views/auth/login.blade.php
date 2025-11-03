<x-guest-layout>
    {{-- ==== BACKGROUND BLUR ==== --}}
    <style>
        /* Latar belakang full screen dengan blur + gelap tipis agar teks/card kontras */
        .login-hero-bg {
            position: fixed;        /* biar cover seluruh viewport & tidak ikut scroll konten */
            inset: 0;               /* top/right/bottom/left = 0 */
            z-index: -2;            /* di belakang konten */
            background-image: url('{{ asset('images/coffee-bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(10px) brightness(0.7); /* blur + sedikit gelap */
            transform: scale(1.06); /* anti “tepi blur” */
        }
        /* Lapisan gradient halus di atas gambar (opsional, bikin lebih soft) */
        .login-hero-overlay {
            position: fixed;
            inset: 0;
            z-index: -1;
            background: radial-gradient(ellipse at 30% 10%, rgba(255,255,255,.08), transparent 50%),
                        linear-gradient(135deg, rgba(67,97,238,.12), rgba(236,64,122,.08));
        }
        /* Biar card lebih menonjol */
        .login-card {
            border-radius: 1rem;
            box-shadow: 0 10px 35px rgba(0,0,0,.15);
        }
    </style>

    {{-- dua layer background --}}
    <div class="login-hero-bg"></div>
    <div class="login-hero-overlay"></div>

    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container my-auto">
                    <div class="row">
                        <div class="col-xl-5 col-lg-6 col-md-7 mx-auto">
                            <div class="card z-index-0 login-card">
                                <div class="card-header text-center pt-4">
                                    <h5>Welcome back</h5>
                                    <p class="text-sm text-secondary mb-0">
                                        Gunakan akun yang diberikan <strong>Manager</strong>
                                    </p>
                                </div>

                                <div class="card-body pt-2">
                                    {{-- alert error --}}
                                    @if ($errors->any())
                                        <div class="alert alert-danger text-sm">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form role="form" method="POST" action="{{ route('login.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" value="{{ old('email') }}"
                                                   class="form-control" placeholder="you@example.com"
                                                   autocomplete="username" required autofocus>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password"
                                                   class="form-control" placeholder="••••••••"
                                                   autocomplete="current-password" required>
                                        </div>

                                        <div class="form-check form-switch d-flex align-items-center mb-3">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember"
                                                   {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label ms-2 mt-2" for="remember">Remember me</label>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn bg-gradient-primary w-100 ">
                                                Sign in
                                            </button>
                                        </div>

                                             <p class="text-center text-secondary text-xs mt-3">
                                &copy; {{ date('Y') }} CoffeShop
                            </p>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-guest-layout>
