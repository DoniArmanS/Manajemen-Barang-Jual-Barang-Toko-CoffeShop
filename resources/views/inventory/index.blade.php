@extends('layouts.softui')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/inventory/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">

  {{-- TOOLBAR ATAS --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
          <div class="mb-2 mb-md-0">
            <h5 class="mb-0">Inventory</h5>
            <p class="text-sm mb-0">Kelola stok barang & bahan — <span class="text-danger fw-bold">tanpa database (demo)</span></p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-dark" id="btnImport">IMPORT CSV</button>
            <button class="btn btn-sm btn-outline-dark" id="btnExport">EXPORT CSV</button>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalItem">+ ITEM</button>
          </div>
        </div>
        <div class="card-body pt-0">
          <div class="row g-3 align-items-center mt-2">
            <div class="col-lg-5">
              <div class="input-group">
                <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                <input id="q" type="text" class="form-control" placeholder="Cari nama / SKU / kategori">
              </div>
            </div>
            <div class="col-lg-7 text-lg-end">
              <div class="btn-group btn-group-sm me-2" role="group" aria-label="Filters">
                <button class="btn btn-outline-secondary active" data-filter="all">ALL</button>
                <button class="btn btn-outline-secondary" data-filter="ok">READY</button>
                <button class="btn btn-outline-secondary" data-filter="low">LOW</button>
                <button class="btn btn-outline-secondary" data-filter="out">OUT</button>
              </div>
              <div class="btn-group btn-group-sm" role="group" aria-label="Sort">
                <button class="btn btn-outline-secondary" data-sort="name">Sort: Name</button>
                <button class="btn btn-outline-secondary" data-sort="stock">Sort: Stock</button>
              </div>
            </div>
          </div>

          <div class="table-responsive mt-4">
            <table class="table table-hover align-items-center mb-0" id="tbl">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>SKU</th>
                  <th>Kategori</th>
                  <th class="text-center">Stock</th>
                  <th class="text-center">Min</th>
                  <th class="text-center">Satuan</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- RINGKASAN & CATATAN --}}
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h6 class="text-uppercase text-secondary text-xxs font-weight-bolder">RINGKASAN</h6>
          <div class="d-flex justify-content-between">
            <span>Total Item</span><span id="statTotal" class="fw-bold">0</span>
          </div>
          <div class="d-flex justify-content-between">
            <span>Low Stock</span><span id="statLow" class="text-warning fw-bold">0</span>
          </div>
          <div class="d-flex justify-content-between">
            <span>Out of Stock</span><span id="statOut" class="text-danger fw-bold">0</span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h6 class="text-uppercase text-secondary text-xxs font-weight-bolder">CATATAN</h6>
          <ul id="log" class="mb-0 small"></ul>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Tambah/Edit --}}
<div class="modal fade" id="modalItem" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formItem">
      <div class="modal-header">
        <h5 class="modal-title">Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">SKU</label>
            <input type="text" class="form-control" name="sku" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <input type="text" class="form-control" name="category" placeholder="Biji, Susu, Sirup, etc">
          </div>
          <div class="col-md-3">
            <label class="form-label">Min</label>
            <input type="number" class="form-control" name="min" value="5" min="0">
          </div>
          <div class="col-md-3">
            <label class="form-label">Satuan</label>
            <input type="text" class="form-control" name="unit" value="pcs">
          </div>
          <div class="col-md-6">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="stock" value="0" min="0">
          </div>
          <div class="col-md-6">
            <label class="form-label">Catatan</label>
            <input type="text" class="form-control" name="note">
          </div>
          <input type="hidden" name="id">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Adjust --}}
<div class="modal fade" id="modalAdjust" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formAdjust">
      <div class="modal-header">
        <h5 class="modal-title">Adjust Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Item</label>
            <input type="text" class="form-control" name="name" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">Qty (+/-)</label>
            <input type="number" class="form-control" name="delta" value="1">
          </div>
          <div class="col-12">
            <label class="form-label">Keterangan</label>
            <input type="text" class="form-control" name="reason" placeholder="Masuk stok / Terpakai / Koreksi">
          </div>
          <input type="hidden" name="id">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-dark">Update</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/inventory/js/inventory.js') }}"></script>
@endpush
