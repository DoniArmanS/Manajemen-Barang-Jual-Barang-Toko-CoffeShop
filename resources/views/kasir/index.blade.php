@extends('layouts.softui')
@php($title = 'Cashier')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/kasir/kasir.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="container-fluid py-4 kasir-body">
  <div class="row">
    {{-- PRODUCT LIST --}}
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header pb-0 d-flex align-items-center gap-2" style="position:relative; overflow:visible">
          <h6 class="mb-3">Daftar Produk</h6>

          <div class="dropdown ms-1">
            <button class="btn btn-outline-secondary btn-sm custom-dropdown" data-bs-toggle="dropdown" type="button">
            PILIH KATEGORI
                </button>

            <ul class="dropdown-menu shadow" id="categoryMenu"><!-- diisi JS --></ul>
          </div>

          <div class="mb-3 ms-auto w-50">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
              <input id="searchInput" type="text" class="form-control" placeholder="Cari produk…">
            </div>
          </div>
        </div>

        <div class="card-body pt-3">
          <div id="productGrid" class="row g-3"><!-- diisi JS --></div>
        </div>
      </div>
    </div>

    {{-- CART --}}
    <div class="col-lg-4">
      <div class="card sticky-top" style="top:12px">
        <div class="card-header pb-0 d-flex align-items-center">
          <h6 class="mb-0">Keranjang Belanja</h6>
          <span class="badge bg-gradient-dark ms-2" id="cartCount">0 ITEM</span>
        </div>
        <div class="card-body">
          <div id="cartEmpty" class="empty-state text-center text-secondary small py-4">
            Keranjang kosong
          </div>

          <div id="cartList" class="list-group list-group-flush d-none"></div>

          <hr class="my-3">

          <div class="d-flex justify-content-between text-sm">
            <span>Subtotal</span>
            <strong id="subtotalText">Rp 0</strong>
          </div>
          <div class="d-flex justify-content-between text-sm">
            <span>Pajak (10%)</span>
            <strong id="taxText">Rp 0</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-md">Total</span>
            <strong class="text-md" id="totalText">Rp 0</strong>
          </div>

          <div class="mt-3">
            <label class="text-xs text-secondary mb-1">Metode Pembayaran</label>
            <div class="d-flex flex-column gap-1">
              <label class="form-check">
                <input class="form-check-input" type="radio" name="pay" value="Tunai" checked>
                <span class="form-check-label">Tunai</span>
              </label>
              <label class="form-check">
                <input class="form-check-input" type="radio" name="pay" value="Kartu Debit">
                <span class="form-check-label">Kartu Debit</span>
              </label>
              <label class="form-check">
                <input class="form-check-input" type="radio" name="pay" value="QRIS">
                <span class="form-check-label">QRIS</span>
              </label>
            </div>
          </div>

          <div class="d-grid gap-2 mt-3">
            <button id="btnCheckout" class="btn btn-dark" disabled>PROSES TRANSAKSI</button>
            <button id="btnClear" class="btn btn-outline-secondary" disabled>KOSONGKAN KERANJANG</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Modal STRUK (muncul setelah sukses) --}}
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">  {{-- kecil biar mirip thermal --}}
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Struk Transaksi</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="receiptContent">
        <!-- diisi JS: <div id="receiptPaper">...</div> -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button id="btnPrint" class="btn btn-dark">Print</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/kasir/cashier.js') }}?v={{ time() }}"></script>
@endpush
