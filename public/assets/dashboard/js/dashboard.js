// ===================
// Dashboard Coffeeshop (DB-aware / localStorage-aware)
// ===================

const KEY_TRANSACTIONS = 'coffeeshop_transactions_v1';
const KEY_INVENTORY_ALIASES = ['inv_items_v1', 'coffeeshop_inventory_v1']; // <-- sinkron dengan halaman Inventory
const KEY_CASH = 'coffeeshop_cash_v1';

const $  = (sel, ctx=document) => ctx.querySelector(sel);

let CURRENT_PERIOD = 'today';
let chartFinancial = null;
let chartInventory = null;
let INVENTORY_EMPTY_CHART = true;
let LAST_DB_SUMMARY = { total: 0, ready: 0, low: 0, out: 0 };

// ----- Storage helpers -----
function loadTransactions(){ try { return JSON.parse(localStorage.getItem(KEY_TRANSACTIONS) || '[]'); } catch { return []; } }
function loadInventoryLS(){
  // baca dari key yang dipakai halaman Inventory
  for (const k of KEY_INVENTORY_ALIASES) {
    const raw = localStorage.getItem(k);
    if (raw) { try { return JSON.parse(raw); } catch {} }
  }
  return [];
}
function loadCash(){ try { return parseFloat(localStorage.getItem(KEY_CASH) || '1000000'); } catch { return 1000000; } }

function log(msg){
  const li = document.createElement('li');
  li.className = 'mb-2 pb-2 border-bottom';
  li.innerHTML = `<small class="text-muted">${new Date().toLocaleString('id-ID')}</small><br>${msg}`;
  const ul = document.getElementById('log');
  if (ul) { ul.prepend(li); if (ul.children.length > 15) ul.removeChild(ul.lastChild); }
}

// ----- Utility -----
function formatRupiah(amount){
  return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', minimumFractionDigits:0 }).format(amount);
}
function getToday(){ const d=new Date(); return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
function isToday(dateStr){ const date=new Date(dateStr); const t=getToday(); return date>=t && date<new Date(t.getTime()+86400000); }
function isThisWeek(dateStr){ const date=new Date(dateStr); const today=new Date(); const sow=new Date(today); sow.setDate(today.getDate()-today.getDay()); sow.setHours(0,0,0,0); return date>=sow; }
function isThisMonth(dateStr){ const date=new Date(dateStr); const today=new Date(); return date.getMonth()===today.getMonth() && date.getFullYear()===today.getFullYear(); }
function isThisYear(dateStr){ const date=new Date(dateStr); const today=new Date(); return date.getFullYear()===today.getFullYear(); }
function filterByPeriod(transactions){
  if (CURRENT_PERIOD === 'today') return transactions.filter(t => isToday(t.datetime));
  if (CURRENT_PERIOD === 'week') return transactions.filter(t => isThisWeek(t.datetime));
  if (CURRENT_PERIOD === 'month') return transactions.filter(t => isThisMonth(t.datetime));
  if (CURRENT_PERIOD === 'year') return transactions.filter(t => isThisYear(t.datetime));
  return transactions;
}
function statusOf(it){
  if((it.stock|0) <= 0) return 'out';
  if((it.stock|0) <= (parseInt(it.min)||0)) return 'low';
  return 'ok';
}

// ----- Charts init -----
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

// ----- Charts update -----
function updateFinancialChart(){
  if(!chartFinancial) return;
  const tx = filterByPeriod(loadTransactions());
  const grouped = {};
  tx.forEach(t=>{
    const d = new Date(t.datetime).toLocaleDateString('id-ID',{day:'2-digit',month:'short'});
    if(!grouped[d]) grouped[d]={income:0, expense:0};
    if(t.type==='income') grouped[d].income += +t.amount||0;
    if(t.type==='expense'||t.type==='inventory') grouped[d].expense += +t.amount||0;
  });
  const labels = Object.keys(grouped).slice(-7);
  chartFinancial.data.labels = labels;
  chartFinancial.data.datasets[0].data = labels.map(l=>grouped[l].income);
  chartFinancial.data.datasets[1].data = labels.map(l=>grouped[l].expense);
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

// ----- Stats -----
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
  set('statProfit', formatRupiah(profit));
}

function writeInventoryCounters(sum){
  const set = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = val; };
  set('statInventoryTotal', sum.total ?? 0);
  set('statInventoryLow',   sum.low   ?? 0);
  set('statInventoryOut',   sum.out   ?? 0);
}

// ----- DB Sync -----
async function fetchInventorySummaryFromDB(){
  if (!window.DASHBOARD_SUMMARY_URL) throw new Error('DASHBOARD_SUMMARY_URL not set');
  const res = await fetch(window.DASHBOARD_SUMMARY_URL, { cache:'no-store' });
  if (!res.ok) throw new Error('HTTP '+res.status);
  return await res.json(); // { total, ready, low, out }
}

// ----- Render orchestrator -----
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

// ----- Events -----
document.addEventListener('DOMContentLoaded', () => {
  initCharts();

  $('#periodFilter')?.addEventListener('change', e => {
    CURRENT_PERIOD = e.target.value;
    renderAll();
    log(`📊 Filter periode diubah ke: ${e.target.options[e.target.selectedIndex].text}`);
  });

  // Render awal
  renderAll();

  // Mode DB: tarik berkala
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
    // Mode localStorage: dengarkan perubahan storage (tab lain)
    window.addEventListener('storage', (ev) => {
      if (KEY_INVENTORY_ALIASES.includes(ev.key)) renderLocalStorage();
    });
  }
});
