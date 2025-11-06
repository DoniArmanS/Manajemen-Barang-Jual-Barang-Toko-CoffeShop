@extends('layouts.softui')
@php($title = 'Cashier Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/kasir/kasir.css') }}?v={{ time() }}">
<style>
  .mgmt-card{border:1px solid rgba(2,6,23,.08);border-radius:14px;overflow:hidden;background:#fff}
  .mgmt-img{width:100%;aspect-ratio:1/1;object-fit:cover}
  .price{font-weight:700}

  /* ===== Ingredient toolbar ===== */
  .ing-toolbar .form-select,.ing-toolbar .form-control{min-width:0}
  .ing-toolbar .form-select{border-radius:10px}
  .ing-toolbar .form-control{border-radius:10px}
  .ing-toolbar .btn{border-radius:12px}

  /* ===== Ingredient list (scrollable) ===== */
  .ing-box{
    max-height: 180px;                /* tinggi list sebelum scroll */
    overflow: auto;
    padding: .4rem;
    background: linear-gradient(180deg,#fafafa, #f7f7f7);
    border: 1px solid #eef0f2;
    border-radius: 14px;
  }

  /* cantikkan scrollbar (webkit) */
  .ing-box::-webkit-scrollbar{height:8px;width:8px}
  .ing-box::-webkit-scrollbar-thumb{
    background: #d7dbe0; border-radius: 999px;
  }
  .ing-box::-webkit-scrollbar-track{background: transparent}

  /* ===== Ingredient chip-card ===== */
  #ingList{display:flex; flex-direction:column; gap:.5rem}
  #ingList .ing-row{
    display:grid;
    grid-template-columns: 1fr auto auto auto; /* name | qty | unit | del */
    gap:.5rem;
    align-items:center;
    padding:.55rem .65rem;
    border:1px solid #e7eaee;
    border-radius:12px;
    background:#fff;
    box-shadow: 0 1px 1.5px rgba(16,24,40,.06);
    transition: box-shadow .15s ease, transform .15s ease;
  }


  #ingList .name{
    font-weight:600; color:#111827;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }

  /* badge style */
  #ingList .badge-soft{
    display:inline-flex; align-items:center; justify-content:center;
    min-width: 2.25rem;
    padding:.25rem .55rem;
    border-radius:999px;
    font-size:.78rem; line-height:1rem;
    border:1px solid;
    user-select:none;
  }
  #ingList .qty{ background:#eef8f3; border-color:#d2efe0; color:#166534; }     /* hijau lembut */
  #ingList .unit{background:#f2f4f7; border-color:#e5e7eb; color:#475467;}    /* abu lembut */

  /* tombol hapus bulat */
  #ingList .btn-del{
    --bs-btn-padding-y:.2rem; --bs-btn-padding-x:.5rem;
    --bs-btn-border-color:#ffccd5;
    --bs-btn-color:#b42318; --bs-btn-hover-color:#7a271a;
    border-radius:999px;
    background:#fff5f6; border:1px solid #ffd1d8;
      position: relative;
      top: 3px;
      display: flex;
      align-items: center;
      justify-content: center;
  }
  #ingList .btn-del:hover{background:#ffe4e8}

  /* kecilkan tulisan helper */
  .text-helper{font-size:.76rem; color:#6b7280}
</style>
@endpush

@section('content')
<div class="container-fluid py-4 kasir-body">
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
          <div>
            <h5 class="mb-0">Cashier Management</h5>
            <p class="text-sm text-secondary mb-0">Kelola menu/produk untuk halaman Kasir — <strong>tanpa database</strong> (localStorage)</p>
          </div>
          <div class="d-flex gap-2">
            <button id="btnImportJSON" class="btn btn-sm btn-outline-dark">IMPORT JSON</button>
            <button id="btnExportJSON" class="btn btn-sm btn-outline-dark">EXPORT JSON</button>
            <button id="btnAdd" class="btn btn-sm btn-dark">+ ADD MENU</button>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-center">
            <div class="col-md-4">
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                <input id="q" type="text" class="form-control" placeholder="Cari nama / kategori …">
              </div>
            </div>
            <div class="col-md-8 text-md-end">
              <div class="btn-group btn-group-sm me-2" role="group">
                <button class="btn btn-outline-secondary active" data-cat="ALL">ALL</button>
                <button class="btn btn-outline-secondary" data-cat="Minuman">Minuman</button>
                <button class="btn btn-outline-secondary" data-cat="Makanan">Makanan</button>
                <button class="btn btn-outline-secondary" data-cat="Snack">Snack</button>
              </div>
              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-secondary" data-sort="name">Sort: Name</button>
                <button class="btn btn-outline-secondary" data-sort="price">Sort: Price</button>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-3" id="grid"><!-- diisi JS --></div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Add/Edit Menu --}}
<div class="modal fade" id="modalMenu" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="formMenu">
      <div class="modal-header">
        <h5 class="modal-title">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          {{-- KIRI: diperlebar untuk bahan --}}
          <div class="col-md-7">
            <img id="imgPreview" class="w-100 rounded border" style="aspect-ratio:1/1;object-fit:cover" src="/assets/img/placeholder.jpg" alt="Preview">
            <input id="imgFile" type="file" class="form-control form-control-sm mt-2" accept="image/*">
            <small class="text-secondary">Gambar akan disimpan sebagai DataURL di localStorage.</small>

            <hr class="my-3">
            <h6 class="mb-2">Bahan yang digunakan</h6>

            <div class="ing-toolbar d-flex gap-2">
              <select id="ingSelect" class="form-select form-select-sm" title="pilih bahan"></select>
              <input id="ingUse" type="number" min="1" step="1" value="1" class="form-control form-control-sm" style="max-width:120px" placeholder="Qty">
              <button id="btnAddIng" type="button" class="btn btn-sm btn-dark">TAMBAH</button>
            </div>

            <div class="ing-box mt-2" id="ingList"><!-- diisi JS --></div>
            <small class="text-secondary d-block mt-1">Qty mengikuti satuan item inventory (gram, ml, pcs, dll).</small>
          </div>

          {{-- KANAN: form menu --}}
          <div class="col-md-5">
            <div class="mb-2">
              <label class="form-label">Nama</label>
              <input name="name" type="text" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Harga (IDR)</label>
              <input name="price" type="number" class="form-control" min="0" value="0" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Kategori</label>
              <select name="cat" class="form-select">
                <option>Minuman</option>
                <option>Makanan</option>
                <option>Snack</option>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <label class="form-label">Stok awal (opsional)</label>
                <input name="stock" type="number" class="form-control" min="0" value="0">
              </div>
              <div class="col-md-6">
                <label class="form-label">ID (opsional)</label>
                <input name="id" type="text" class="form-control" placeholder="otomatis jika dikosongkan">
              </div>
            </div>
            <div class="mt-2">
              <label class="form-label">Catatan</label>
              <input name="note" type="text" class="form-control">
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-dark">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
</script>
<script src="{{ asset('assets/kasir/manage.js') }}?v={{ time() }}"></script>
@endpush
