@extends('layouts.softui')

@section('content')
<div class="container-fluid px-2 px-xl-2 py-3" style="background: linear-gradient(to bottom, #FBF7F4, #F5EFE6); min-height: 100vh;">
  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">

      <div class="text-center mb-4">
        <h2 class="display-7 fw-bold text-dark mb-1" style="text-shadow: 1px 1px 1px rgba(0,0,0,0.05);">Riwayat Transaksi</h2>
        <p class="lead text-muted">Setiap pembayaran adalah sebuah cerita dan rasa</p>

        <div class="row justify-content-center mt-4 g-2">
          <div class="col-md-8">
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-white border-end-0"><i class="ni ni-zoom-split-in text-muted"></i></span>
              <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Cari tanggal / metode / item…" style="background-color: rgba(255,255,255,0.85);">
            </div>
          </div>

          {{-- Ganti “Semua Status” => tombol Export CSV pill (tanpa caret) --}}
          <div class="col-md-4">
            <div class="dropdown w-100">
              <button class="btn btn-pill-export w-100 dropdown-toggle no-caret" data-bs-toggle="dropdown" aria-expanded="false">
                Export CSV
              </button>
              <ul class="dropdown-menu dropdown-menu-end w-100">
                <li><a class="dropdown-item export-range" data-range="today" href="#">Hari ini</a></li>
                <li><a class="dropdown-item export-range" data-range="week" href="#">Minggu ini</a></li>
                <li><a class="dropdown-item export-range" data-range="month" href="#">Bulan ini</a></li>
                <li><a class="dropdown-item export-range" data-range="year" href="#">Tahun ini</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item export-range" data-range="all" href="#">Semua data</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- LIST RIWAYAT -->
      <div id="transactionList" class="row g-4"></div>

      <!-- Pesan jika tidak ada hasil -->
      <div id="noResults" class="text-center mt-5 d-none">
        <i class="ni ni-fat-remove text-muted" style="font-size: 48px"></i>
        <h5 class="text-muted mt-3">Tidak ada transaksi yang cocok.</h5>
      </div>

    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* Tombol Export berbentuk pill, tinggi & tampilan selaras input besar */
  .btn-pill-export{
    background: rgba(255,255,255,0.85);
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: .8rem 1rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: #111827;
    text-align: left;
  }
  .btn-pill-export:hover{ background:#fff; border-color:#d1d5db; }
  .btn-pill-export:focus{ box-shadow: 0 0 0 .2rem rgba(139,90,60,.15); }
  /* Hilangkan caret default bootstrap */
  .no-caret::after{ display:none !important; }

  /* Card & hover */
  .trx-card{border-radius:18px;transition:.25s ease;overflow:hidden;border:1px solid rgba(2,6,23,.06);background:#fff}
  .trx-card:hover{transform:translateY(-4px);box-shadow:0 18px 36px rgba(0,0,0,.12)}
  .trx-leftbar{width:8px;background:linear-gradient(195deg,#8B5A3C,#6F4E37)}
  .trx-row{padding:18px 20px}
  .badge-pill{border-radius:999px;padding:.4rem .7rem;font-weight:600;font-size:.72rem;letter-spacing:.3px}
  .text-xxs{font-size:.78rem}
  .muted{color:#6b7280}
  .icon-pay{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:#f3f4f6;margin-right:8px}
  .icon-pay i{font-size:14px}
  .pointer{cursor:pointer}
  /* Collapse detail */
  .trx-detail{background:#fafaf9;border-top:1px dashed rgba(2,6,23,.08)}
  .table-sm td,.table-sm th{padding:.45rem .6rem;font-size:.9rem}

  /* Warna badge subtle */
  .bg-success-subtle{background:#EAF7EE!important;color:#2CA46D}
  .bg-warning-subtle{background:#FFF6E5!important;color:#F29F05}
  .bg-danger-subtle{background:#FDECEC!important;color:#D33F49}
</style>
@endpush

@push('scripts')
<script>
// ========= helpers =========
function fmtIDR(n){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(+n||0); }
function fmtDT(ts){
  const d = new Date(ts);
  const t = d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  const date = d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
  return {date, time:t};
}
function payIcon(pay){
  if(pay==='Tunai') return '<span class="icon-pay"><i class="ni ni-money-coins text-success"></i></span>Tunai';
  if(pay==='Kartu Debit' || pay==='Kartu') return '<span class="icon-pay"><i class="ni ni-credit-card text-info"></i></span>Kartu Debit';
  if(pay==='QRIS') return '<span class="icon-pay"><i class="ni ni-mobile-button text-primary"></i></span>QRIS';
  return `<span class="icon-pay"><i class="ni ni-delivery-fast text-secondary"></i></span>${pay||'-'}`;
}
function loadOrders(){ try{ return JSON.parse(localStorage.getItem('kasir_orders_v1')||'[]'); }catch{return [];} }

// Rentang waktu
function startOfToday(){ const d=new Date(); d.setHours(0,0,0,0); return d; }
function startOfWeek(){ const d=startOfToday(); const day=d.getDay(); const diff=(day===0?6:day-1); d.setDate(d.getDate()-diff); return d; }
function startOfMonth(){ const d=startOfToday(); d.setDate(1); return d; }
function startOfYear(){ const d=startOfToday(); d.setMonth(0,1); return d; }

// ========= render list =========
function renderList(){
  const wrap = document.getElementById('transactionList');
  const q = (document.getElementById('searchInput').value||'').toLowerCase();

  let data = loadOrders();
  data.sort((a,b)=>b.ts-a.ts);

  const html = [];
  let visible = 0;

  for(const trx of data){
    const {date,time} = fmtDT(trx.ts||Date.now());
    const itemsCount = Array.isArray(trx.items)? trx.items.reduce((n,i)=>n+(+i.qty||0), 0) : 0;

    // search text (tanggal/metode/nomor/item)
    const hay = [
      trx.no, trx.pay, date, time,
      ...(trx.items||[]).map(i=>i.name)
    ].join(' ').toLowerCase();
    const matchSearch = !q || hay.includes(q);

    if(!matchSearch) continue;
    visible++;

    const detailId = `detail-${trx.no}`;
    const detailRows = (trx.items||[]).map(i=>`
      <tr>
        <td>${i.name}</td>
        <td class="text-center">${i.qty}</td>
        <td class="text-end">${fmtIDR(i.price)}</td>
        <td class="text-end">${fmtIDR((+i.price||0) * (+i.qty||0))}</td>
      </tr>`).join('');

    const status = trx.status || 'Selesai';
    const statusBadge =
      status==='Selesai' ? '<span class="badge badge-pill bg-success-subtle">Selesai</span>' :
      status==='Diproses' ? '<span class="badge badge-pill bg-warning-subtle">Diproses</span>' :
      '<span class="badge badge-pill bg-danger-subtle">Dibatalkan</span>';

    html.push(`
      <div class="col-12">
        <div class="trx-card">
          <div class="d-flex">
            <div class="trx-leftbar"></div>
            <div class="flex-grow-1">
              <div class="trx-row d-flex align-items-center justify-content-between pointer" data-bs-toggle="collapse" data-bs-target="#${detailId}" aria-expanded="false">
                <div class="me-3">
                  <div class="fw-bold">Transaksi pada ${date}, ${time}</div>
                  <div class="text-xxs muted">
                    Metode: ${payIcon(trx.pay)} • ${itemsCount} item • Total <strong>${fmtIDR(trx.total)}</strong>
                  </div>
                </div>
                <div class="text-end">${statusBadge}</div>
              </div>

              <div id="${detailId}" class="collapse trx-detail">
                <div class="p-3">
                  <div class="table-responsive">
                    <table class="table table-sm align-middle mb-3">
                      <thead>
                        <tr>
                          <th>Item</th>
                          <th class="text-center" style="width:90px">Qty</th>
                          <th class="text-end" style="width:160px">Harga</th>
                          <th class="text-end" style="width:180px">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>${detailRows || '<tr><td colspan="4" class="text-center text-muted">Tidak ada item</td></tr>'}</tbody>
                    </table>
                  </div>
                  <div class="d-flex justify-content-end">
                    <div class="text-end" style="min-width:260px">
                      <div>Subtotal: <strong>${fmtIDR(trx.subtotal)}</strong></div>
                      <div>Pajak (10%): <strong>${fmtIDR(trx.tax)}</strong></div>
                      <div class="mt-1">Total: <span class="h6 fw-bold">${fmtIDR(trx.total)}</span></div>
                      <div class="text-xxs muted mt-1">No. Order: ${trx.no}</div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    `);
  }

  wrap.innerHTML = html.join('');
  document.getElementById('noResults').classList.toggle('d-none', visible>0);
}

// ========= EXPORT CSV =========
function exportCSV(range){
  let from=null, to=new Date();
  const now=new Date();
  switch(range){
    case 'today': from=startOfToday(); break;
    case 'week':  from=startOfWeek();  break;
    case 'month': from=startOfMonth(); break;
    case 'year':  from=startOfYear();  break;
    case 'all': default: from=null;
  }

  const orders = loadOrders()
    .filter(o => !from || (new Date(o.ts)>=from && new Date(o.ts)<=to))
    .sort((a,b)=>a.ts-b.ts); // kronologis

  const rows=[['datetime','order_no','payment_method','items_count','subtotal','tax','total','items_detail']];
  for(const o of orders){
    const itemsDetail = (o.items||[])
      .map(x=>`${(x.name||'').replace(/,/g,' ')} x${x.qty||0}`)
      .join(' | ');
    rows.push([
      new Date(o.ts).toISOString(),
      o.no||'',
      o.pay||'',
      (Array.isArray(o.items)?o.items.reduce((n,i)=>n+(+i.qty||0),0):0),
      o.subtotal||0,
      o.tax||0,
      o.total||0,
      itemsDetail
    ]);
  }

  const csv = rows.map(r=>r.map(x=>`"${String(x).replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob = new Blob([csv],{type:'text/csv'});
  const a=document.createElement('a');
  const pad2=n=>String(n).padStart(2,'0');
  const fname=`sales_${range}_${now.getFullYear()}${pad2(now.getMonth()+1)}${pad2(now.getDate())}.csv`;
  a.href=URL.createObjectURL(blob);
  a.download=fname;
  a.click();
  URL.revokeObjectURL(a.href);
}

// ========= bindings =========
document.addEventListener('DOMContentLoaded', () => {
  renderList();
  document.getElementById('searchInput').addEventListener('input', renderList);

  document.querySelectorAll('.export-range').forEach(el=>{
    el.addEventListener('click', e=>{
      e.preventDefault();
      exportCSV(el.dataset.range);
    });
  });

  // auto refresh jika ada transaksi baru
  window.addEventListener('storage', (ev)=>{
    if(ev.key === 'kasir_orders_v1' || ev.key === 'kasir_orders_ping'){
      renderList();
    }
  });
});
</script>
@endpush
