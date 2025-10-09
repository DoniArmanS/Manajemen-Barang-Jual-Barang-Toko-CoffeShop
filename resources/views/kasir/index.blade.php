@extends('layouts.softui')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/kasir/kasir.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
    <div class="container-fluid kasir-body">
        <div class="row">
            <!-- Navbar akan di-extend dari Laravel -->

            <!-- Konten Utama -->
            <main class="col-md-12 ms-sm-auto px-md-4 main-content">


                <div class="row">
                    <!-- Daftar Produk -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Daftar Produk</span>
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control form-control-sm" placeholder="Cari produk...">
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Kategori -->
                                <div class="filter-section mb-3">
                                    <h6 class="mb-2">Filter Kategori:</h6>

                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Pilih Kategori
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                            <li><a class="dropdown-item filter-btn active" href="#" data-filter="all">Semua</a></li>
                                            <li><a class="dropdown-item filter-btn" href="#" data-filter="makanan">
                                                <i class="fas fa-utensils me-1"></i> Makanan
                                            </a></li>
                                            <li><a class="dropdown-item filter-btn" href="#" data-filter="minuman">
                                                <i class="fas fa-coffee me-1"></i> Minuman
                                            </a></li>
                                            <li><a class="dropdown-item filter-btn" href="#" data-filter="snack">
                                                <i class="fas fa-cookie me-1"></i> Snack
                                            </a></li>
                                            <li><a class="dropdown-item filter-btn" href="#" data-filter="perlengkapan-rumah">
                                                <i class="fas fa-home me-1"></i> Perlengkapan Rumah
                                            </a></li>
                                            <li><a class="dropdown-item filter-btn" href="#" data-filter="kesehatan">
                                                <i class="fas fa-heart me-1"></i> Kesehatan
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="row product-list">
                                    <!-- Produk 1 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="minuman">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Kopi Arabica">
                                                <span class="badge bg-primary category-badge">Minuman</span>
                                            </div>
                                            <h6 class="mb-1">Kopi Arabica 250g</h6>
                                            <p class="text-muted small mb-1">Stok: 15</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 45.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 2 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="minuman">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Teh Hijau">
                                                <span class="badge bg-primary category-badge">Minuman</span>
                                            </div>
                                            <h6 class="mb-1">Teh Hijau 100g</h6>
                                            <p class="text-muted small mb-1">Stok: 8</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 32.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 3 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="makanan">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Gula Pasir">
                                                <span class="badge bg-success category-badge">Makanan</span>
                                            </div>
                                            <h6 class="mb-1">Gula Pasir 1kg</h6>
                                            <p class="text-muted small mb-1">Stok: 22</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 15.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 4 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="minuman">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Susu UHT">
                                                <span class="badge bg-primary category-badge">Minuman</span>
                                            </div>
                                            <h6 class="mb-1">Susu UHT 1L</h6>
                                            <p class="text-muted small mb-1">Stok: 12</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 24.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 5 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="snack">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Biskuit Coklat">
                                                <span class="badge bg-warning category-badge">Snack</span>
                                            </div>
                                            <h6 class="mb-1">Biskuit Coklat</h6>
                                            <p class="text-muted small mb-1">Stok: 18</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 12.500</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 6 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="makanan">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Minyak Goreng">
                                                <span class="badge bg-success category-badge">Makanan</span>
                                            </div>
                                            <h6 class="mb-1">Minyak Goreng 2L</h6>
                                            <p class="text-muted small mb-1">Stok: 10</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 35.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 7 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="perlengkapan-rumah">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Sabun Mandi">
                                                <span class="badge bg-info category-badge">Rumah</span>
                                            </div>
                                            <h6 class="mb-1">Sabun Mandi</h6>
                                            <p class="text-muted small mb-1">Stok: 25</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 8.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 8 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="kesehatan">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Pasta Gigi">
                                                <span class="badge bg-danger category-badge">Kesehatan</span>
                                            </div>
                                            <h6 class="mb-1">Pasta Gigi</h6>
                                            <p class="text-muted small mb-1">Stok: 14</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 11.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 9 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="snack">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Keripik Kentang">
                                                <span class="badge bg-warning category-badge">Snack</span>
                                            </div>
                                            <h6 class="mb-1">Keripik Kentang</h6>
                                            <p class="text-muted small mb-1">Stok: 20</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 9.500</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 10 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="makanan">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Beras Premium">
                                                <span class="badge bg-success category-badge">Makanan</span>
                                            </div>
                                            <h6 class="mb-1">Beras Premium 5kg</h6>
                                            <p class="text-muted small mb-1">Stok: 7</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 75.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 11 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="minuman">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Jus Jeruk">
                                                <span class="badge bg-primary category-badge">Minuman</span>
                                            </div>
                                            <h6 class="mb-1">Jus Jeruk Kotak</h6>
                                            <p class="text-muted small mb-1">Stok: 16</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 8.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Produk 12 -->
                                    <div class="col-md-4 mb-3 product-card" data-category="kesehatan">
                                        <div class="product-item">
                                            <div class="text-center mb-2 position-relative">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/45/A_small_cup_of_coffee.JPG" class="img-fluid rounded" alt="Shampoo">
                                                <span class="badge bg-danger category-badge">Kesehatan</span>
                                            </div>
                                            <h6 class="mb-1">Shampoo Anti Ketombe</h6>
                                            <p class="text-muted small mb-1">Stok: 11</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">Rp 28.000</span>
                                                <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keranjang Belanja -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Keranjang Belanja</span>
                                <span class="badge bg-primary">3 Item</span>
                            </div>
                            <div class="card-body">
                                <!-- Item Keranjang 1 -->
                                <div class="cart-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">Kopi Arabica 250g</h6>
                                            <small class="text-muted">Rp 45.000</small>
                                        </div>
                                        <div class="quantity-control">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-minus"></i></button>
                                            <input type="text" class="form-control form-control-sm" value="1">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Subtotal</span>
                                        <span class="fw-bold">Rp 45.000</span>
                                    </div>
                                    <div class="text-end mt-1">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>

                                <!-- Item Keranjang 2 -->
                                <div class="cart-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">Teh Hijau 100g</h6>
                                            <small class="text-muted">Rp 32.000</small>
                                        </div>
                                        <div class="quantity-control">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-minus"></i></button>
                                            <input type="text" class="form-control form-control-sm" value="2">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Subtotal</span>
                                        <span class="fw-bold">Rp 64.000</span>
                                    </div>
                                    <div class="text-end mt-1">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>

                                <!-- Item Keranjang 3 -->
                                <div class="cart-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">Gula Pasir 1kg</h6>
                                            <small class="text-muted">Rp 15.000</small>
                                        </div>
                                        <div class="quantity-control">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-minus"></i></button>
                                            <input type="text" class="form-control form-control-sm" value="1">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Subtotal</span>
                                        <span class="fw-bold">Rp 15.000</span>
                                    </div>
                                    <div class="text-end mt-1">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>

                                <!-- Ringkasan Pembayaran -->
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal</span>
                                        <span>Rp 124.000</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Pajak (10%)</span>
                                        <span>Rp 12.400</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 fw-bold">
                                        <span>Total</span>
                                        <span>Rp 136.400</span>
                                    </div>

                                    <!-- Metode Pembayaran -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Metode Pembayaran</label>
                                        <div class="payment-methods">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="paymentMethod" id="cash" checked>
                                                <label class="form-check-label" for="cash">
                                                    Tunai
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="paymentMethod" id="debit">
                                                <label class="form-check-label" for="debit">
                                                    Kartu Debit
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="paymentMethod" id="credit">
                                                <label class="form-check-label" for="credit">
                                                    Kartu Kredit
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="paymentMethod" id="qris">
                                                <label class="form-check-label" for="qris">
                                                    QRIS
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input Pembayaran -->
                                    <div class="mb-3">
                                        <label for="paymentAmount" class="form-label">Jumlah Bayar</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="paymentAmount" placeholder="0">
                                        </div>
                                    </div>

                                    <!-- Kembalian -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Kembalian</span>
                                            <span class="fw-bold text-success">Rp 63.600</span>
                                        </div>
                                    </div>

                                    <!-- Tombol Aksi -->
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-success btn-lg">
                                            <i class="fas fa-check-circle"></i> Proses Transaksi
                                        </button>
                                        <button class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Batalkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Struk Sementara -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <span>Struk Transaksi</span>
                            </div>
                            <div class="card-body">
                                <div class="receipt">
                                    <div class="receipt-header">
                                        <h5>TOKO MAKMUR SEJAHTERA</h5>
                                        <p>Jl. Merdeka No. 123, Jakarta</p>
                                        <p>Telp: (021) 1234567</p>
                                    </div>
                                    <div class="receipt-item">
                                        <span>No. Transaksi:</span>
                                        <span>TRX-20230520-001</span>
                                    </div>
                                    <div class="receipt-item">
                                        <span>Tanggal:</span>
                                        <span>20/05/2023 14:30</span>
                                    </div>
                                    <div class="receipt-item">
                                        <span>Kasir:</span>
                                        <span>Ahmad</span>
                                    </div>
                                    <hr>
                                    <div class="receipt-item">
                                        <span>Kopi Arabica 250g</span>
                                        <span>Rp 45.000</span>
                                    </div>
                                    <div class="receipt-item">
                                        <span>Teh Hijau 100g x2</span>
                                        <span>Rp 64.000</span>
                                    </div>
                                    <div class="receipt-item">
                                        <span>Gula Pasir 1kg</span>
                                        <span>Rp 15.000</span>
                                    </div>
                                    <hr>
                                    <div class="receipt-item">
                                        <span>Subtotal:</span>
                                        <span>Rp 124.000</span>
                                    </div>
                                    <div class="receipt-item">
                                        <span>Pajak (10%):</span>
                                        <span>Rp 12.400</span>
                                    </div>
                                    <div class="receipt-item receipt-total">
                                        <span>TOTAL:</span>
                                        <span>Rp 136.400</span>
                                    </div>
                                    <div class="receipt-item">
                                        <span>Tunai:</span>
                                        <span>Rp 200.000</span>
                                    </div>
                                    <div class="receipt-item">
                                        <span>Kembalian:</span>
                                        <span>Rp 63.600</span>
                                    </div>
                                    <hr>
                                    <div class="text-center mt-3">
                                        <p>Terima kasih atas kunjungan Anda</p>
                                        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    @endsection



