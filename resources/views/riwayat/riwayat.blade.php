@extends('layouts.softui')

@section('content')
<div class="container-fluid px-2 px-xl-2 py-3" style="background: linear-gradient(to bottom, #FBF7F4, #F5EFE6); min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            
            <div class="text-center mb-4">
                <h2 class="display-7 fw-bold text-dark mb-1" style="text-shadow: 1px 1px 1px rgba(0,0,0,0.05);">Riwayat Transaksi Pembelian</h2>
                <p class="lead text-muted">Setiap pembelian adalah sebuah cerita dan rasa</p>
                
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Cari produk atau tanggal..." style="background-color: rgba(255,255,255,0.8);">
                        </div>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <select id="statusFilter" class="form-select form-select-lg" style="background-color: rgba(255,255,255,0.8);">
                            <option value="">Semua Status</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Diproses">Diproses</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="transactionList" class="row g-4">
                {{-- Kartu Transaksi 1 --}}
                <div class="transaction-item" data-status="Selesai">
                    <div class="card border-0 shadow-lg h-100 overflow-hidden" style="border-radius: 20px;">
                        <div class="row g-0 h-100">
                            <div class="col-auto" style="background: linear-gradient(195deg, #8B5A3C 0%, #6F4E37 100%); width: 8px;"></div>
                            <div class="col">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center">
                                            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=100&h=100&fit=crop&crop=center" class="rounded-circle me-4" alt="Product" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                            <div>
                                                <h5 class="mb-1 fw-bold text-dark">Es Kopi Susu</h5>
                                                <p class="text-muted mb-0"><i class="far fa-clock text-muted me-1"></i>28 Okt 2023, 08:15 &middot; 2 items</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                            <h4 class="mb-2 fw-bold text-dark">Rp 36.000</h4>
                                            <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Transaksi 2 --}}
                <div class="transaction-item" data-status="Selesai">
                    <div class="card border-0 shadow-lg h-100 overflow-hidden" style="border-radius: 20px;">
                        <div class="row g-0 h-100">
                            <div class="col-auto" style="background: linear-gradient(195deg, #8B5A3C 0%, #6F4E37 100%); width: 8px;"></div>
                            <div class="col">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center">
                                            <img src="https://images.unsplash.com/photo-1511920180055-a7e3e46811c3?w=100&h=100&fit=crop&crop=center" class="rounded-circle me-4" alt="Product" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                            <div>
                                                <h5 class="mb-1 fw-bold text-dark">Arabica Gayo 250gr</h5>
                                                <p class="text-muted mb-0"><i class="far fa-clock text-muted me-1"></i>27 Okt 2023, 19:30 &middot; 1 item</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                            <h4 class="mb-2 fw-bold text-dark">Rp 95.000</h4>
                                            <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Transaksi 3 --}}
                <div class="transaction-item" data-status="Diproses">
                    <div class="card border-0 shadow-lg h-100 overflow-hidden" style="border-radius: 20px;">
                        <div class="row g-0 h-100">
                            <div class="col-auto" style="background: linear-gradient(195deg, #f093fb 0%, #f5576c 100%); width: 8px;"></div>
                            <div class="col">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center">
                                            <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=100&h=100&fit=crop&crop=center" class="rounded-circle me-4" alt="Product" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                            <div>
                                                <h5 class="mb-1 fw-bold text-dark">Croissant Almond</h5>
                                                <p class="text-muted mb-0"><i class="far fa-clock text-muted me-1"></i>27 Okt 2023, 19:30 &middot; 1 item</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                            <h4 class="mb-2 fw-bold text-dark">Rp 35.000</h4>
                                            <span class="badge bg-warning-subtle text-warning fw-semibold px-3 py-2 rounded-pill"><i class="fas fa-truck me-1"></i>Diproses</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Transaksi 4 --}}
                <div class="transaction-item" data-status="Dibatalkan">
                    <div class="card border-0 shadow-lg h-100 overflow-hidden" style="border-radius: 20px;">
                        <div class="row g-0 h-100">
                            <div class="col-auto" style="background: linear-gradient(195deg, #FC466B 0%, #3F5EFB 100%); width: 8px;"></div>
                            <div class="col">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center">
                                            <img src="https://images.unsplash.com/photo-1534778101976-628808918e88?w=100&h=100&fit=crop&crop=center" class="rounded-circle me-4" alt="Product" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                            <div>
                                                <h5 class="mb-1 fw-bold text-dark">Cappuccino</h5>
                                                <p class="text-muted mb-0"><i class="far fa-clock text-muted me-1"></i>25 Okt 2023, 14:00 &middot; 1 item</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                            <h4 class="mb-2 fw-bold text-dark">Rp 28.000</h4>
                                            <span class="badge bg-danger-subtle text-danger fw-semibold px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i>Dibatalkan</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Transaksi 5 --}}
                <div class="transaction-item" data-status="Diproses">
                    <div class="card border-0 shadow-lg h-100 overflow-hidden" style="border-radius: 20px;">
                        <div class="row g-0 h-100">
                            <div class="col-auto" style="background: linear-gradient(195deg, #f093fb 0%, #f5576c 100%); width: 8px;"></div>
                            <div class="col">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center">
                                            <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=100&h=100&fit=crop&crop=center" class="rounded-circle me-4" alt="Product" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                            <div>
                                                <h5 class="mb-1 fw-bold text-dark">Croissant Almond</h5>
                                                <p class="text-muted mb-0"><i class="far fa-clock text-muted me-1"></i>27 Okt 2023, 19:30 &middot; 1 item</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                            <h4 class="mb-2 fw-bold text-dark">Rp 35.000</h4>
                                            <span class="badge bg-warning-subtle text-warning fw-semibold px-3 py-2 rounded-pill"><i class="fas fa-truck me-1"></i>Diproses</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Transaksi 6 --}}
                <div class="transaction-item" data-status="Selesai">
                    <div class="card border-0 shadow-lg h-100 overflow-hidden" style="border-radius: 20px;">
                        <div class="row g-0 h-100">
                            <div class="col-auto" style="background: linear-gradient(195deg, #8B5A3C 0%, #6F4E37 100%); width: 8px;"></div>
                            <div class="col">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 d-flex align-items-center">
                                            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=100&h=100&fit=crop&crop=center" class="rounded-circle me-4" alt="Product" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                            <div>
                                                <h5 class="mb-1 fw-bold text-dark">Es Kopi Susu</h5>
                                                <p class="text-muted mb-0"><i class="far fa-clock text-muted me-1"></i>28 Okt 2023, 08:15 &middot; 2 items</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                            <h4 class="mb-2 fw-bold text-dark">Rp 36.000</h4>
                                            <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Pesan jika tidak ada hasil -->
            <div id="noResults" class="text-center mt-5" style="display: none;">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada transaksi yang ditemukan.</h5>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Styling Kartu Transaksi */
    .card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card:hover {
        transform: translateY(-10px) scale(1.01);
        box-shadow: 0 25px 50px rgba(0,0,0,0.15) !important;
    }

    /* Styling Badge */
    .badge {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Styling Input dan Select */
    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        transition: all 0.2s ease-in-out;
    }
    .form-control:focus, .form-select:focus {
        border-color: #8B5A3C;
        box-shadow: 0 0 0 0.2rem rgba(139, 90, 60, 0.15);
    }
    .input-group-text {
        border-radius: 12px 0 0 12px;
        border: 1px solid #e0e0e0;
        background-color: white;
    }
    .form-control.border-start-0 {
        border-radius: 0 12px 12px 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
 $(document).ready(function() {
    function filterTransactions() {
        var searchTerm = $('#searchInput').val().toLowerCase();
        var statusFilter = $('#statusFilter').val();
        var anyVisible = false;

        $('.transaction-item').each(function() {
            var card = $(this);
            var title = card.find('h5').text().toLowerCase();
            var details = card.find('p').text().toLowerCase();
            var status = card.data('status');

            var matchesSearch = title.includes(searchTerm) || details.includes(searchTerm);
            var matchesStatus = (statusFilter === '' || status === statusFilter);

            if (matchesSearch && matchesStatus) {
                card.fadeIn(300); // Tambahkan efek fade in
                anyVisible = true;
            } else {
                card.fadeOut(300); // Tambahkan efek fade out
            }
        });

        $('#noResults').toggle(!anyVisible);
    }

    $('#searchInput').on('keyup', filterTransactions);
    $('#statusFilter').on('change', filterTransactions);
});
</script>
@endpush