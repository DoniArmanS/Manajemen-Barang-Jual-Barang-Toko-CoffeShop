@extends('layouts.softui')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/dashboard.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">

  {{-- HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="mb-2 mb-md-0">
              <h5 class="mb-0">Dashboard Admin Coffeeshop</h5>
              <p class="text-sm mb-0">Selamat datang di sistem manajemen coffeeshop — <span class="text-danger fw-bold">Demo Version</span></p>
            </div>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" id="periodFilter" style="width:auto">
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="year">Tahun Ini</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOP CARDS (3 kolom, tanpa Uang Kas) --}}
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body d-flex align-items-center">
          <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md me-3">
            <i class="ni ni-money-coins text-lg opacity-10"></i>
          </div>
          <div>
            <p class="text-xs mb-0 text-uppercase font-weight-bold">Pendapatan</p>
            <h5 class="mb-0" id="statIncome">Rp 0</h5>
            <small class="text-muted"><span id="statIncomeCount">0</span> transaksi</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body d-flex align-items-center">
          <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md me-3">
            <i class="ni ni-cart text-lg opacity-10"></i>
          </div>
          <div>
            <p class="text-xs mb-0 text-uppercase font-weight-bold">Pengeluaran</p>
            <h5 class="mb-0" id="statExpense">Rp 0</h5>
            <small class="text-muted"><span id="statExpenseCount">0</span> transaksi</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body d-flex align-items-center">
          <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md me-3">
            <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
          </div>
          <div>
            <p class="text-xs mb-0 text-uppercase font-weight-bold">Profit</p>
            <h5 class="mb-0" id="statProfit">Rp 0</h5>
            <small class="text-muted">Pendapatan - Pengeluaran</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- GRAFIK --}}
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header pb-0">
          <h6>Statistik Keuangan</h6>
          <p class="text-sm mb-0">Grafik pendapatan vs pengeluaran</p>
        </div>
        <div class="card-body p-3">
          <canvas id="chartFinancial" height="300"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header pb-0">
          <h6>Status Inventory</h6>
          <p class="text-sm mb-0">Kondisi stok barang</p>
        </div>
        <div class="card-body">
          <canvas id="chartInventory" height="300"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- RINGKASAN & LOG --}}
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header pb-0">
          <h6 class="text-uppercase text-secondary text-xxs font-weight-bolder">RINGKASAN INVENTORY</h6>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <p class="text-xs mb-0">Total Item</p>
              <h5 class="mb-0" id="statInventoryTotal">0</h5>
            </div>
            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
              <i class="ni ni-box-2 text-lg opacity-10"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <p class="text-xs mb-0 text-warning">Low Stock</p>
              <h5 class="mb-0 text-warning" id="statInventoryLow">0</h5>
            </div>
            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
              <i class="ni ni-bell-55 text-lg opacity-10"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="text-xs mb-0 text-danger">Out of Stock</p>
              <h5 class="mb-0 text-danger" id="statInventoryOut">0</h5>
            </div>
            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
              <i class="ni ni-fat-remove text-lg opacity-10"></i>
            </div>
          </div>
          <div class="mt-4">
            <a href="{{ route('inventory') }}" class="btn btn-sm btn-outline-info w-100">Lihat Detail Inventory →</a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
          <h6 class="text-uppercase text-secondary text-xxs font-weight-bolder mb-0">AKTIVITAS TERAKHIR</h6>
          <div class="d-flex gap-2">
            <button id="btnExportActivity" class="btn btn-sm btn-outline-secondary">Export CSV</button>
          </div>
        </div>
        <div class="card-body">
          <ul id="log" class="mb-0 small" style="max-height: 300px; overflow-y:auto;"></ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
  // Mode LS (tanpa DB). Kalau nanti mau DB, properti ini bisa kamu pakai:
  window.USE_DB = false;
</script>
<script src="{{ asset('assets/dashboard/js/dashboard.js') }}"></script>
@endpush
