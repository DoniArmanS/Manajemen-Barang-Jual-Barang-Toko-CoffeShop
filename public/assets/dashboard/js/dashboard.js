// Dashboard (DB-aware / localStorage-aware) + Activity feed harian
const KEY_TRANSACTIONS = 'coffeeshop_transactions_v1';
const KEY_INVENTORY_ALIASES = ['inv_items_v1', 'coffeeshop_inventory_v1'];
const KEY_CASH = 'coffeeshop_cash_v1';
const ACT_PREFIX = 'activity_'; // sama dengan inventory.js

// === Tambahan: sumber order kasir (untuk auto-income) ===
const KEY_KASIR_ORDERS = 'kasir_orders_v1';

const $  = (sel, ctx=document) => ctx.querySelector(sel);

let CURRENT_PERIOD = 'today';
let chartFinancial = null;
let chartInventory = null;
let INVENTORY_EMPTY_CHART = true;
let LAST_DB_SUMMARY = { total: 0, ready: 0, low: 0, out: 0 };

// -------- helpers
function loadTransactions(){ try { return JSON.parse(localStorage.getItem(KEY_TRANSACTIONS) || '[]'); } catch { return []; } }
function saveTransactions(list){ localStorage.setItem(KEY_TRANSACTIONS, JSON.stringify(list)); }
function loadInventoryLS(){
  for (const k of KEY_INVENTORY_ALIASES) {
    const raw = localStorage.getItem(k);
    if (raw) { try { return JSON.parse(raw); } catch {} }
  }
  return [];
}
function loadCash(){ try { return parseFloat(localStorage.getItem(KEY_CASH) || '1000000'); } catch { return 1000000; } }

function todayKey(){
  const d=new Date();
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}
function actKey(){ return ACT_PREFIX + todayKey(); }
function loadActivityLS(){
  try { return JSON.parse(localStorage.getItem(actKey()) || '[]'); } catch { return []; }
}

function formatRupiah(amount){
  return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', minimumFractionDigits:0 }).format(amount);
}
function getToday(){ const d=new Date(); return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
function isToday(dateStr){ const date=new Date(dateStr); const t=getToday(); return date>=t && date<new Date(t.getTime()+86400000); }
function isThisWeek(dateStr){
  const date=new Date(dateStr);
  const today=new Date();
  const sow=new Date(today);
  // Set ke awal minggu (Minggu)
  sow.setDate(today.getDate()-today.getDay());
  sow.setHours(0,0,0,0);
  const eow=new Date(sow); eow.setDate(sow.getDate()+7);
  return date>=sow && date<eow;
}
function isThisMonth(dateStr){ const date=new Date(dateStr); const today=new Date(); return date.getMonth()===today.getMonth() && date.getFullYear()===today.getFullYear(); }
function isThisYear(dateStr){ const date=new Date(dateStr); const today=new Date(); return date.getFullYear()===today.getFullYear(); }
function filterByPeriod(transactions){
  if (CURRENT_PERIOD === 'today') return transactions.filter(t => isToday(t.datetime));
  if (CURRENT_PERIOD === 'week') return transactions.filter(t => isThisWeek(t.datetime));
  if (CURRENT_PERIOD === 'month') return transactions.filter(t => isThisMonth(t.datetime));
  if (CURRENT_PERIOD === 'year') return transactions.filter(t => isThisYear(t.datetime));
  return transactions;
}
function statusOf(it){ if((it.stock|0) <= 0) return 'out'; if((it.stock|0) <= (parseInt(it.min)||0)) return 'low'; return 'ok'; }

// === Tambahan: ambil orders kasir & sinkron ke transaksi income ===
function loadKasirOrders(){ try { return JSON.parse(localStorage.getItem(KEY_KASIR_ORDERS) || '[]'); } catch { return []; } }
/** Buat income dari order kasir status "Selesai" (anti-duplikat via meta.kasir_no) */
function syncIncomeFromKasir(){
  const orders = loadKasirOrders() || [];
  if (!Array.isArray(orders) || orders.length === 0) return;

  const tx = loadTransactions();
  let changed = false;

  for (const o of orders){
    // Struktur order di riwayatmu: { no, ts, total, status, ... }
    if (!o || o.status !== 'Selesai') continue;
    const already = tx.some(t => t.meta && t.meta.kasir_no === o.no);
    if (already) continue;

    tx.push({
      id: 'tx_kasir_'+(o.no || (Date.now()+Math.random())).toString(),
      type: 'income',
      amount: Number(o.total) || 0,
      datetime: o.ts ? new Date(o.ts).toISOString() : new Date().toISOString(),
      note: `SALE — ${o.no || 'kasir'}`,
      meta: { source: 'kasir', kasir_no: o.no || null }
    });
    changed = true;
  }

  if (changed){
    saveTransactions(tx);
  }
}

// -------- charts init
function initCharts(){
  const ctxFinancial = document.getElementById('chartFinancial');
  if(ctxFinancial){
    chartFinancial = new Chart(ctxFinancial, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [
          { label:'Pendapatan', data:[], backgroundColor:'rgba(34, 197, 94, 0.8)', borderColor:'rgba(34, 197, 94, 1)', borderWidth:1 },
          { label:'Pengeluaran', data:[], backgroundColor:'rgba(239, 68, 68, 0.8)', borderColor:'rgba(239, 68, 68, 1)', borderWidth:1 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display:true, position:'top' },
          tooltip: { callbacks:{ label: ctx => ctx.dataset.label+': '+formatRupiah(ctx.parsed.y) } }
        },
        scales: { y: { beginAtZero:true, ticks:{ callback: v => 'Rp '+(v/1000)+'k' } } }
      }
    });
  }

  const canvas = document.getElementById('chartInventory');
  if (canvas) {
    try { const ex = Chart.getChart(canvas); if (ex) ex.destroy(); } catch {}
    chartInventory = new Chart(canvas, {
      type: 'doughnut',
      data: { labels:['No Data'], datasets:[{ data:[1], backgroundColor:['#e5e7eb'], borderColor:['#e5e7eb'], borderWidth:2 }] },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } } }
    });
    INVENTORY_EMPTY_CHART = true;
  }
}

// -------- charts update (period-aware + sorted)
function updateFinancialChart(){
  if(!chartFinancial) return;

  const tx = filterByPeriod(loadTransactions());

  // Grouping key & label sesuai periode
  const grouped = {}; // key => { income, expense, ts }
  function pushRow(key, ts, type, amt, label){
    if(!grouped[key]) grouped[key] = { income:0, expense:0, ts, label };
    if(type==='income') grouped[key].income += amt;
    if(type==='expense' || type==='inventory') grouped[key].expense += amt;
  }

  for (const t of tx){
    const d = new Date(t.datetime);
    let key, label, ts;

    if (CURRENT_PERIOD === 'year'){
      key   = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
      label = d.toLocaleString('id-ID', { month:'short' }); // Jan, Feb, ...
      ts    = new Date(d.getFullYear(), d.getMonth(), 1).getTime();
    } else if (CURRENT_PERIOD === 'month' || CURRENT_PERIOD === 'week'){
      key   = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
      label = d.toLocaleDateString('id-ID',{ day:'2-digit', month:'short' });
      ts    = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    } else { // today
      const hour = d.getHours();
      key   = `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}-${hour}`;
      label = `${String(hour).padStart(2,'0')}:00`;
      ts    = new Date(d.getFullYear(), d.getMonth(), d.getDate(), hour).getTime();
    }

    pushRow(key, ts, t.type, (+t.amount||0), label);
  }

  // Sort kronologis
  const rows = Object.values(grouped).sort((a,b)=>a.ts-b.ts);

  // Batasi poin untuk grafik supaya rapi
  let limit = 7;
  if (CURRENT_PERIOD === 'month') limit = 31;
  if (CURRENT_PERIOD === 'year')  limit = 12;
  if (CURRENT_PERIOD === 'week')  limit = 7;
  if (CURRENT_PERIOD === 'today') limit = 24;

  const sliced = rows.slice(-limit);

  chartFinancial.data.labels = sliced.map(r=>r.label);
  chartFinancial.data.datasets[0].data = sliced.map(r=>r.income);
  chartFinancial.data.datasets[1].data = sliced.map(r=>r.expense);
  chartFinancial.update();
}

function renderInventoryDonut(sum){
  if(!chartInventory) return;
  const canvas = document.getElementById('chartInventory');
  const isEmpty = !sum.total;

  if (INVENTORY_EMPTY_CHART !== isEmpty) {
    try { const ex = Chart.getChart(canvas); if (ex) ex.destroy(); } catch {}
    chartInventory = new Chart(canvas, {
      type: 'doughnut',
      data: isEmpty
        ? { labels:['No Data'], datasets:[{ data:[1], backgroundColor:['#e5e7eb'], borderColor:['#e5e7eb'], borderWidth:2 }] }
        : { labels:['Ready Stock','Low Stock','Out of Stock'],
            datasets:[{ data:[sum.ready||0,sum.low||0,sum.out||0],
              backgroundColor:['rgba(34,197,94,0.8)','rgba(251,191,36,0.8)','rgba(239,68,68,0.8)'],
              borderColor:['rgba(34,197,94,1)','rgba(251,191,36,1)','rgba(239,68,68,1)'], borderWidth:2 }] },
      options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display: !isEmpty, position:'bottom' } } }
    });
    INVENTORY_EMPTY_CHART = isEmpty;
    return;
  }

  if (isEmpty) {
    chartInventory.data.labels = ['No Data'];
    chartInventory.data.datasets[0].data = [1];
    chartInventory.data.datasets[0].backgroundColor = ['#e5e7eb'];
    chartInventory.data.datasets[0].borderColor = ['#e5e7eb'];
  } else {
    chartInventory.data.labels = ['Ready Stock','Low Stock','Out of Stock'];
    chartInventory.data.datasets[0].data = [sum.ready||0, sum.low||0, sum.out||0];
    chartInventory.data.datasets[0].backgroundColor = ['rgba(34,197,94,0.8)','rgba(251,191,36,0.8)','rgba(239,68,68,0.8)'];
    chartInventory.data.datasets[0].borderColor = ['rgba(34,197,94,1)','rgba(251,191,36,1)','rgba(239,68,68,1)'];
  }
  chartInventory.update();
}

// -------- top stats
function updateTopStats(){
  const tx = filterByPeriod(loadTransactions());
  const income = tx.filter(t=>t.type==='income').reduce((s,t)=>s+(+t.amount||0),0);
  const expense= tx.filter(t=>t.type==='expense'||t.type==='inventory').reduce((s,t)=>s+(+t.amount||0),0);
  const incomeCount = tx.filter(t=>t.type==='income').length;
  const expenseCount= tx.filter(t=>t.type==='expense'||t.type==='inventory').length;
  const cash = loadCash();
  const profit = income - expense;

  const set = (id, val) => { const el=document.getElementById(id); if (el) el.textContent = val; };
  set('statIncome', formatRupiah(income));
  set('statIncomeCount', incomeCount);
  set('statExpense', formatRupiah(expense));
  set('statExpenseCount', expenseCount);
  set('statCash', formatRupiah(cash));
  set('statProfit', formatRupiah(profit)); // bisa negatif, formatRupiah handle minus
}

function writeInventoryCounters(sum){
  const set = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = val; };
  set('statInventoryTotal', sum.total ?? 0);
  set('statInventoryLow',   sum.low   ?? 0);
  set('statInventoryOut',   sum.out   ?? 0);
}

// -------- DB sync (optional)
async function fetchInventorySummaryFromDB(){
  if (!window.DASHBOARD_SUMMARY_URL) throw new Error('DASHBOARD_SUMMARY_URL not set');
  const res = await fetch(window.DASHBOARD_SUMMARY_URL, { cache:'no-store' });
  if (!res.ok) throw new Error('HTTP '+res.status);
  return await res.json(); // { total, ready, low, out }
}

// -------- Activity feed
async function fetchActivities(){
  const ul = document.getElementById('log');
  if (!ul) return;

  if (window.USE_DB) {
    // versi server
    try {
      const res = await fetch("/dashboard/activity?limit=50", { cache:'no-store' });
      const data = await res.json();
      ul.innerHTML = '';
      for (const a of data) {
        const li = document.createElement('li');
        li.className = 'mb-2 pb-2 border-bottom';
        const when = new Date(a.created_at).toLocaleString('id-ID');
        const qty  = (a.qty_change !== null && a.qty_change !== undefined) ? ` (${a.qty_change>0?'+':''}${a.qty_change})` : '';
        li.innerHTML = `<small class="text-muted">${when}</small><br>
          <strong>${a.action.toUpperCase()}</strong> — ${a.item_name || a.source}${qty}${a.note ? ` — ${a.note}` : ''}`;
        ul.appendChild(li);
      }
    } catch (e) {
      console.error('fetchActivities failed', e);
    }
    return;
  }

  // versi localStorage (reset otomatis per hari via key activity_YYYY-MM-DD)
  const list = loadActivityLS(); // newest first
  ul.innerHTML = '';
  for (const a of list) {
    const when = new Date(a.ts).toLocaleString('id-ID');
    const qty  = (a.qty_change===0 || a.qty_change) ? ` (${a.qty_change>0?'+':''}${a.qty_change})` : '';
    const li = document.createElement('li');
    li.className = 'mb-2 pb-2 border-bottom';
    li.innerHTML = `<small class="text-muted">${when}</small><br>
      <strong>${a.action.toUpperCase()}</strong> — ${a.item_name || 'Item'}${qty}${a.note ? ` — ${a.note}` : ''}`;
    ul.appendChild(li);
  }
}

// Export CSV activity (DB -> hit endpoint, LS -> generate CSV)
function exportActivity(){
  if (window.USE_DB) {
    window.open('/dashboard/activity/export', '_blank');
    return;
  }
  const list = loadActivityLS().slice().reverse(); // lama -> baru
  const rows = [['datetime','action','item_name','qty_change','note']];
  for (const a of list){
    rows.push([
      new Date(a.ts).toISOString(),
      a.action,
      a.item_name || '',
      (a.qty_change===0 || a.qty_change) ? a.qty_change : '',
      (a.note||'').replace(/\r?\n/g,' ')
    ]);
  }
  const csv = rows.map(r=>r.map(x=>`"${String(x).replace(/"/g,'""')}"`).join(',')).join('\n');
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
  a.download = `activity_${todayKey()}.csv`;
  a.click();
}

// -------- render orchestrator
function renderLocalStorage(){
  const items = loadInventoryLS();
  let ready=0, low=0, out=0;
  items.forEach(it => {
    const st = statusOf(it);
    if (st==='ok') ready++;
    else if (st==='low') low++;
    else out++;
  });
  const sum = { total: items.length, ready, low, out };
  writeInventoryCounters(sum);
  renderInventoryDonut(sum);
}

function renderDB(){
  writeInventoryCounters(LAST_DB_SUMMARY);
  renderInventoryDonut(LAST_DB_SUMMARY);
}

function renderAll(){
  updateTopStats();
  updateFinancialChart();
  if (window.USE_DB) renderDB(); else renderLocalStorage();
}

// -------- events
document.addEventListener('DOMContentLoaded', () => {
  initCharts();

  // Sinkron pendapatan dari kasir (sekali saat load)
  syncIncomeFromKasir();

  $('#periodFilter')?.addEventListener('change', e => {
    CURRENT_PERIOD = e.target.value;
    renderAll();
  });

  // Render awal
  renderAll();
  fetchActivities();

  // tarik ringkasan DB berkala (kalau USE_DB)
  if (window.USE_DB) {
    const pull = async () => {
      try {
        LAST_DB_SUMMARY = await fetchInventorySummaryFromDB();
      } catch (e) {
        console.error('Gagal ambil summary inventory:', e);
        LAST_DB_SUMMARY = { total:0, ready:0, low:0, out:0 };
      }
      renderDB();
    };
    pull();
    setInterval(pull, 15000);
  } else {
    // listen perubahan dari halaman Inventory & Kasir (ping via localStorage)
    window.addEventListener('storage', (ev) => {
      if (
        KEY_INVENTORY_ALIASES.includes(ev.key) ||
        ev.key === 'activity_ping' ||
        ev.key === actKey() ||
        ev.key === KEY_KASIR_ORDERS ||        // <== kasir orders berubah
        ev.key === 'kasir_orders_ping' ||     // <== ping dari kasir
        ev.key === KEY_TRANSACTIONS           // <== transaksi berubah
      ) {
        if (ev.key === KEY_KASIR_ORDERS || ev.key === 'kasir_orders_ping') {
          syncIncomeFromKasir();              // generate income baru dari kasir
        }
        renderLocalStorage();
        fetchActivities();
        updateTopStats();
        updateFinancialChart();
      }
    });
  }

  // Export CSV activity (otomatis pilih DB/LS)
  document.getElementById('btnExportActivity')?.addEventListener('click', exportActivity);

  // Poll activity tiap 5 detik supaya live
  setInterval(() => {
    syncIncomeFromKasir(); // jaga-jaga bila tab kasir lain update
    fetchActivities();
    renderAll();
  }, 5000);
});
