// Dashboard Coffeeshop frontend (no DB) – in-memory storage
const KEY_TRANSACTIONS = 'coffeeshop_transactions_v1';
const KEY_INVENTORY = 'coffeeshop_inventory_v1';
const KEY_CASH = 'coffeeshop_cash_v1';

const $  = (sel, ctx=document) => ctx.querySelector(sel);
const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

let CURRENT_PERIOD = 'today';
let chartFinancial = null;
let chartInventory = null;

// ===== STORAGE FUNCTIONS =====
function loadTransactions(){ 
  try {
    return JSON.parse(localStorage.getItem(KEY_TRANSACTIONS) || '[]'); 
  } catch {
    return [];
  }
}
function saveTransactions(list){ localStorage.setItem(KEY_TRANSACTIONS, JSON.stringify(list)); }

function loadInventory(){ 
  try {
    return JSON.parse(localStorage.getItem(KEY_INVENTORY) || '[]'); 
  } catch {
    return [];
  }
}
function saveInventory(list){ localStorage.setItem(KEY_INVENTORY, JSON.stringify(list)); }

function loadCash(){ 
  try {
    return parseFloat(localStorage.getItem(KEY_CASH) || '1000000'); 
  } catch {
    return 1000000;
  }
}
function saveCash(amount){ localStorage.setItem(KEY_CASH, amount.toString()); }

function log(msg){
  const li = document.createElement('li');
  li.className = 'mb-2 pb-2 border-bottom';
  li.innerHTML = `<small class="text-muted">${new Date().toLocaleString('id-ID')}</small><br>${msg}`;
  const ul = document.getElementById('log');
  if (ul) {
    ul.prepend(li);
    if (ul.children.length > 15) ul.removeChild(ul.lastChild);
  }
}

// ===== UTILITY FUNCTIONS =====
function formatRupiah(amount){
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
  }).format(amount);
}

function getToday(){
  const d = new Date();
  return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function isToday(dateStr){
  const date = new Date(dateStr);
  const today = getToday();
  return date >= today && date < new Date(today.getTime() + 86400000);
}

function isThisWeek(dateStr){
  const date = new Date(dateStr);
  const today = new Date();
  const startOfWeek = new Date(today);
  startOfWeek.setDate(today.getDate() - today.getDay());
  startOfWeek.setHours(0, 0, 0, 0);
  return date >= startOfWeek;
}

function isThisMonth(dateStr){
  const date = new Date(dateStr);
  const today = new Date();
  return date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear();
}

function isThisYear(dateStr){
  const date = new Date(dateStr);
  const today = new Date();
  return date.getFullYear() === today.getFullYear();
}

function filterByPeriod(transactions){
  if (CURRENT_PERIOD === 'today') return transactions.filter(t => isToday(t.datetime));
  if (CURRENT_PERIOD === 'week') return transactions.filter(t => isThisWeek(t.datetime));
  if (CURRENT_PERIOD === 'month') return transactions.filter(t => isThisMonth(t.datetime));
  if (CURRENT_PERIOD === 'year') return transactions.filter(t => isThisYear(t.datetime));
  return transactions;
}

function statusOf(item){
  if((item.stock|0) <= 0) return 'out';
  if((item.stock|0) <= (parseInt(item.min)||0)) return 'low';
  return 'ok';
}

// ===== CHART FUNCTIONS =====
function initCharts(){
  // Financial Chart
  const ctxFinancial = document.getElementById('chartFinancial');
  if(ctxFinancial){
    chartFinancial = new Chart(ctxFinancial, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [
          {
            label: 'Pendapatan',
            data: [],
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgba(34, 197, 94, 1)',
            borderWidth: 1
          },
          {
            label: 'Pengeluaran',
            data: [],
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderColor: 'rgba(239, 68, 68, 1)',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + formatRupiah(context.parsed.y);
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return 'Rp ' + (value/1000) + 'k';
              }
            }
          }
        }
      }
    });
  }

  // Inventory Chart
  const ctxInventory = document.getElementById('chartInventory');
  if(ctxInventory){
    chartInventory = new Chart(ctxInventory, {
      type: 'doughnut',
      data: {
        labels: ['Ready Stock', 'Low Stock', 'Out of Stock'],
        datasets: [{
          data: [0, 0, 0],
          backgroundColor: [
            'rgba(34, 197, 94, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(239, 68, 68, 0.8)'
          ],
          borderColor: [
            'rgba(34, 197, 94, 1)',
            'rgba(251, 191, 36, 1)',
            'rgba(239, 68, 68, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  }
}

function updateFinancialChart(){
  if(!chartFinancial) return;
  
  const transactions = filterByPeriod(loadTransactions());
  
  // Group by date
  const grouped = {};
  transactions.forEach(t => {
    const date = new Date(t.datetime).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    if(!grouped[date]) grouped[date] = { income: 0, expense: 0 };
    if(t.type === 'income') grouped[date].income += parseFloat(t.amount || 0);
    if(t.type === 'expense' || t.type === 'inventory') grouped[date].expense += parseFloat(t.amount || 0);
  });
  
  const labels = Object.keys(grouped).slice(-7); // Last 7 days
  const incomeData = labels.map(l => grouped[l]?.income || 0);
  const expenseData = labels.map(l => grouped[l]?.expense || 0);
  
  chartFinancial.data.labels = labels;
  chartFinancial.data.datasets[0].data = incomeData;
  chartFinancial.data.datasets[1].data = expenseData;
  chartFinancial.update();
}

function updateInventoryChart(){
  if(!chartInventory) return;
  
  const items = loadInventory();
  let ready = 0, low = 0, out = 0;
  
  items.forEach(it => {
    const st = statusOf(it);
    if(st === 'ok') ready++;
    else if(st === 'low') low++;
    else if(st === 'out') out++;
  });
  
  chartInventory.data.datasets[0].data = [ready, low, out];
  chartInventory.update();
}

// ===== RENDER FUNCTION =====
function render(){
  updateStats();
  updateInventoryStats();
  updateFinancialChart();
  updateInventoryChart();
}

function updateStats(){
  const transactions = filterByPeriod(loadTransactions());
  
  const income = transactions.filter(t => t.type === 'income').reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
  const expense = transactions.filter(t => t.type === 'expense' || t.type === 'inventory').reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
  const incomeCount = transactions.filter(t => t.type === 'income').length;
  const expenseCount = transactions.filter(t => t.type === 'expense' || t.type === 'inventory').length;
  const cash = loadCash();
  const profit = income - expense;
  
  const set = (id, val) => {
    const el = document.getElementById(id);
    if(el) el.textContent = val;
  };
  
  set('statIncome', formatRupiah(income));
  set('statIncomeCount', incomeCount);
  set('statExpense', formatRupiah(expense));
  set('statExpenseCount', expenseCount);
  set('statCash', formatRupiah(cash));
  set('statProfit', formatRupiah(profit));
}

function updateInventoryStats(){
  const items = loadInventory();
  let low = 0, out = 0;
  
  items.forEach(it => {
    const st = statusOf(it);
    if(st === 'low') low++;
    if(st === 'out') out++;
  });
  
  const set = (id, val) => {
    const el = document.getElementById(id);
    if(el) el.textContent = val;
  };
  
  set('statInventoryTotal', items.length);
  set('statInventoryLow', low);
  set('statInventoryOut', out);
}

// ===== EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', () => {
  // Initialize charts
  initCharts();
  
  // Period filter
  $('#periodFilter')?.addEventListener('change', e => {
    CURRENT_PERIOD = e.target.value;
    render();
    log(`📊 Filter periode diubah ke: ${e.target.options[e.target.selectedIndex].text}`);
  });
  
  // Navigation buttons
  $('#btnGotoIncome')?.addEventListener('click', e => {
    e.preventDefault();
    alert('Navigasi ke halaman Pendapatan\n(Implementasi route: /pendapatan)');
    log('🔗 Navigasi ke halaman Pendapatan');
  });
  
  $('#btnGotoExpense')?.addEventListener('click', e => {
    e.preventDefault();
    alert('Navigasi ke halaman Pengeluaran\n(Implementasi route: /pengeluaran)');
    log('🔗 Navigasi ke halaman Pengeluaran');
  });
  
  $('#btnGotoInventory')?.addEventListener('click', e => {
    e.preventDefault();
    alert('Navigasi ke halaman Inventory\n(Implementasi route: /inventory)');
    log('🔗 Navigasi ke halaman Inventory');
  });
  
  $('#btnGotoCash')?.addEventListener('click', e => {
    e.preventDefault();
    alert('Navigasi ke halaman Uang Kas\n(Implementasi route: /uang-kas)');
    log('🔗 Navigasi ke halaman Uang Kas');
  });
  
  $('#btnGotoInventoryDetail')?.addEventListener('click', e => {
    e.preventDefault();
    alert('Navigasi ke halaman Detail Inventory\n(Implementasi route: /inventory)');
    log('🔗 Navigasi ke halaman Detail Inventory');
  });
  
  // Seed data pertama kali
  if (!window.USE_DB && loadTransactions().length === 0) {
  if(loadTransactions().length === 0){
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 10, 0).toISOString();
    const yesterday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1, 14, 0).toISOString();
    const twoDaysAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 2, 11, 0).toISOString();
    
    saveTransactions([
      {id: 1, type: 'income', datetime: today, description: 'Penjualan Americano', category: 'Penjualan', amount: 25000, note: '2 cup'},
      {id: 2, type: 'income', datetime: today, description: 'Penjualan Cappuccino', category: 'Penjualan', amount: 35000, note: '1 cup'},
      {id: 3, type: 'income', datetime: today, description: 'Penjualan Latte', category: 'Penjualan', amount: 30000, note: '1 cup'},
      {id: 4, type: 'expense', datetime: today, description: 'Pembelian Susu', category: 'Bahan Baku', amount: 150000, note: '5 liter'},
      {id: 5, type: 'expense', datetime: today, description: 'Biaya Listrik', category: 'Operasional', amount: 200000, note: 'Bulan ini'},
      {id: 6, type: 'income', datetime: yesterday, description: 'Penjualan Es Kopi Susu', category: 'Penjualan', amount: 28000, note: '1 cup'},
      {id: 7, type: 'income', datetime: yesterday, description: 'Penjualan Cappuccino', category: 'Penjualan', amount: 70000, note: '2 cup'},
      {id: 8, type: 'expense', datetime: yesterday, description: 'Pembelian Gula', category: 'Bahan Baku', amount: 50000, note: '5 kg'},
      {id: 9, type: 'income', datetime: twoDaysAgo, description: 'Penjualan Americano', category: 'Penjualan', amount: 75000, note: '3 cup'},
      {id: 10, type: 'inventory', datetime: twoDaysAgo, description: 'Stok Biji Kopi', category: 'Inventory', amount: 300000, note: '2 kg'}
    ]);
    log('🎉 Data demo transaksi berhasil dimuat');
  }
}
  
if (!window.USE_DB && loadInventory().length === 0) {
  if(loadInventory().length === 0){
    saveInventory([
      {id: 1, name: 'Biji Kopi Arabica', sku: 'BEAN-AR', category: 'Bahan', stock: 12, min: 5, unit: 'kg'},
      {id: 2, name: 'Biji Kopi Robusta', sku: 'BEAN-RB', category: 'Bahan', stock: 8, min: 5, unit: 'kg'},
      {id: 3, name: 'Susu Full Cream', sku: 'MILK-FC', category: 'Bahan', stock: 4, min: 6, unit: 'L'},
      {id: 4, name: 'Gula Pasir', sku: 'SUGAR-01', category: 'Bahan', stock: 15, min: 10, unit: 'kg'},
      {id: 5, name: 'Gelas Cup 12oz', sku: 'CUP-12', category: 'Perlengkapan', stock: 0, min: 50, unit: 'pcs'},
      {id: 6, name: 'Sedotan', sku: 'STRAW-01', category: 'Perlengkapan', stock: 200, min: 100, unit: 'pcs'},
      {id: 7, name: 'Sirup Vanilla', sku: 'SYR-VAN', category: 'Bahan', stock: 3, min: 5, unit: 'btl'}
    ]);
    log('📦 Data demo inventory berhasil dimuat');
  }
}
  
  
  
  // Initial render
  render();
  log('✅ Dashboard berhasil dimuat');
});