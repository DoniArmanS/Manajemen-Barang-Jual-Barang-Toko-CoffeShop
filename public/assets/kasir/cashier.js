/* public/assets/kasir/cashier.js
   Cashier (POS) — membaca katalog dari localStorage + Activity log SALE
   - Katalog key: 'kasir_products_v1' (dibuat/diubah oleh manage.js)
   - Saat checkout, log 'sale' ke /activity dan ping dashboard
*/
(function(){
  const KEY_PRODUCTS = 'kasir_products_v1';
  const ALLOWED_CATS = ['Minuman','Makanan','Snack'];
  const TAX = 0.10;

  const fmt = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n);
  const state = { items:new Map(), products:[], q:'', cat:'ALL' };

  // ---------- Activity helpers ----------
  function pingDashboard(){ localStorage.setItem('activity_ping', Date.now().toString()); }
  function postActivity(payload){
  // simpan ke localStorage juga
  const ACT_PREFIX = 'activity_';
  const todayKey = () => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
  };
  const actKey = () => ACT_PREFIX + todayKey();
  const list = JSON.parse(localStorage.getItem(actKey()) || '[]');
  list.unshift({
    ts: Date.now(),
    source: payload.source || 'cashier',
    action: payload.action,
    item_name: payload.item_name ?? null,
    qty_change: payload.qty_change ?? null,
    note: payload.note ?? null,
    meta: payload.meta ?? {}
  });
  localStorage.setItem(actKey(), JSON.stringify(list));
  localStorage.setItem('activity_ping', Date.now().toString());

  // kirim ke server juga (opsional)
  fetch('/activity', {
    method:'POST',
    headers:{
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'Content-Type':'application/json'
    },
    body: JSON.stringify(payload)
  }).catch(()=>{});
}


  // ---------- Products ----------
  function loadProducts(){
    try{
      const raw = localStorage.getItem(KEY_PRODUCTS);
      const arr = raw ? JSON.parse(raw) : [];
      return Array.isArray(arr) ? arr.filter(p=>ALLOWED_CATS.includes(p.cat)) : [];
    }catch{ return []; }
  }

  function renderCategories(){
    const cats = ['ALL',...new Set(state.products.map(p=>p.cat))].filter(c=>c==='ALL' || ALLOWED_CATS.includes(c));
    const menu = document.getElementById('categoryMenu');
    menu.innerHTML = cats.map(c=>`<li><a class="dropdown-item" data-cat="${c}">${c}</a></li>`).join('');
    menu.querySelectorAll('[data-cat]').forEach(a=>a.addEventListener('click',()=>{
      state.cat = a.dataset.cat;
      renderProducts();
    }));
  }

  function renderProducts(){
    const grid = document.getElementById('productGrid');
    const q = state.q.toLowerCase(), cat = state.cat;
    const list = state.products.filter(p => (cat==='ALL'||p.cat===cat) &&
      (q==='' || p.name.toLowerCase().includes(q) || (p.id||'').toLowerCase().includes(q)));
    grid.innerHTML = list.map(p=>`
      <div class="col-6 col-md-4">
        <div class="p-2 product-card h-100">
          <img class="product-img" src="${p.img || '/assets/img/placeholder.jpg'}" alt="${p.name}">
          <div class="mt-2">
            <span class="badge badge-chip ${
              p.cat==='Minuman' ? 'bg-gradient-info'
                : p.cat==='Makanan' ? 'bg-gradient-danger'
                : 'bg-gradient-warning'
            }">${p.cat}</span>
            <div class="fw-bold small mt-1">${p.name}</div>
            <div class="text-secondary text-xs">Stok: ${p.stock ?? '-'}</div>
            <div class="fw-bold mt-1">${fmt(p.price||0)}</div>
            <button class="btn btn-sm btn-dark w-100 mt-2" data-add="${p.id}">TAMBAH</button>
          </div>
        </div>
      </div>
    `).join('');
    grid.querySelectorAll('[data-add]').forEach(b=>b.addEventListener('click',()=>addToCart(b.dataset.add,1)));
  }

  // ---------- Cart ----------
  function calc(){
    let subtotal = 0;
    for(const {product,qty} of state.items.values()) subtotal += (product.price||0) * qty;
    const tax = Math.round(subtotal * TAX);
    return {subtotal,tax,total:subtotal+tax};
  }
  function orderNo(){
    const now = new Date();
    return `TRX-${now.getFullYear().toString().slice(-2)}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}-${String(now.getHours()).padStart(2,'0')}${String(now.getMinutes()).padStart(2,'0')}${String(now.getSeconds()).padStart(2,'0')}`;
  }

  function addToCart(id, delta){
    const p = state.products.find(x=>x.id===id); if(!p) return;
    const cur = state.items.get(id) || {product:p, qty:0};
    cur.qty = Math.max(0, cur.qty + delta);
    if(cur.qty===0) state.items.delete(id); else state.items.set(id,cur);
    renderCart();
  }
  function removeFromCart(id){ state.items.delete(id); renderCart(); }

  function renderCart(){
    const list = document.getElementById('cartList');
    const empty = document.getElementById('cartEmpty');
    const btnCheckout = document.getElementById('btnCheckout');
    const btnClear = document.getElementById('btnClear');

    const items = [...state.items.values()];
    document.getElementById('cartCount').textContent = `${items.length} ITEM`;

    const hasItems = items.length > 0;
    list.classList.toggle('d-none', !hasItems);
    empty.classList.toggle('d-none', hasItems);
    btnCheckout.disabled = !hasItems;
    btnClear.disabled = !hasItems;

    list.innerHTML = items.map(({product,qty})=>`
      <div class="list-group-item d-flex align-items-center justify-content-between">
        <div class="me-2">
          <div class="small fw-bold">${product.name}</div>
          <div class="text-xs text-secondary">${fmt(product.price||0)}</div>
        </div>
        <div class="qty-wrap">
          <button class="btn btn-outline-secondary qty-btn" data-dec="${product.id}">–</button>
          <div class="qty-value mb-3" id="q-${product.id}">${qty}</div>
          <button class="btn btn-outline-secondary qty-btn" data-inc="${product.id}">+</button>
          <button class="btn btn-outline-danger btn-delete-item" data-del="${product.id}">&times;</button>
        </div>
      </div>
    `).join('');

    list.querySelectorAll('[data-inc]').forEach(b=>b.addEventListener('click',()=>addToCart(b.dataset.inc,1)));
    list.querySelectorAll('[data-dec]').forEach(b=>b.addEventListener('click',()=>addToCart(b.dataset.dec,-1)));
    list.querySelectorAll('[data-del]').forEach(b=>b.addEventListener('click',()=>removeFromCart(b.dataset.del)));

    const {subtotal,tax,total} = calc();
    document.getElementById('subtotalText').textContent = fmt(subtotal);
    document.getElementById('taxText').textContent      = fmt(tax);
    document.getElementById('totalText').textContent    = fmt(total);
  }

  // ---------- Receipt ----------
  function buildReceiptHtml(order) {
    const rows = order.items.map(({product, qty}) => `
      <tr>
        <td>${product.name}</td>
        <td style="text-align:center">${qty}</td>
        <td style="text-align:right">${fmt(product.price||0)}</td>
        <td style="text-align:right">${fmt((product.price||0) * qty)}</td>
      </tr>
    `).join('');

    return `
      <div id="receiptPaper">
        <div class="r-head">
          <div class="r-title">CoffeShop</div>
          <div class="r-meta">${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}</div>
          <div class="r-meta">${order.no}</div>
        </div>
        <div class="r-hr"></div>
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th style="text-align:center">Qty</th>
              <th style="text-align:right">Harga</th>
              <th style="text-align:right">Subtotal</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
        <div class="r-hr"></div>
        <table>
          <tr><td>Subtotal</td><td style="text-align:right">${fmt(order.subtotal)}</td></tr>
          <tr><td>Pajak (10%)</td><td style="text-align:right">${fmt(order.tax)}</td></tr>
          <tr><td><strong>Total</strong></td><td style="text-align:right"><strong>${fmt(order.total)}</strong></td></tr>
          <tr><td>Bayar (${order.pay})</td><td style="text-align:right">${fmt(order.total)}</td></tr>
        </table>
        <div class="r-thanks">—— Terima kasih ——<br>Atas kunjungan Anda 🙌</div>
      </div>
    `;
  }

  function checkout(){
    if(state.items.size===0) return;

    const pay = document.querySelector('input[name="pay"]:checked')?.value || 'Tunai';
    const order = { no: orderNo(), items:[...state.items.values()], pay, ...calc() };

    // show receipt
    document.getElementById('receiptContent').innerHTML = buildReceiptHtml(order);
    new bootstrap.Modal(document.getElementById('receiptModal')).show();

    // log aktivitas SALE
    const itemCount = order.items.reduce((n,{qty})=>n+qty,0);
    postActivity({
      action: 'sale',
      item_name: `${itemCount} item`,
      note: `Total ${fmt(order.total)} — ${pay}`,
      meta: {
        order_no: order.no,
        subtotal: order.subtotal,
        tax: order.tax,
        total: order.total,
        items: order.items.map(({product,qty})=>({id:product.id,name:product.name,qty,price:product.price||0}))
      }
    });

    // clear cart
    state.items.clear();
    renderCart();
  }

  // ---------- Init ----------
  function refreshCatalog(){
    state.products = loadProducts();
    renderCategories();
    renderProducts();
  }

  document.getElementById('searchInput')?.addEventListener('input',e=>{ state.q = e.target.value; renderProducts(); });
  document.getElementById('btnCheckout')?.addEventListener('click', checkout);
  document.getElementById('btnClear')?.addEventListener('click', ()=>{ state.items.clear(); renderCart(); });

  // print-only struk
  let printRoot = document.getElementById('printRoot');
  if(!printRoot){ printRoot = document.createElement('div'); printRoot.id = 'printRoot'; printRoot.style.display = 'none'; document.body.appendChild(printRoot); }
  document.getElementById('btnPrint')?.addEventListener('click', () => {
    const html = document.getElementById('receiptContent').innerHTML;
    printRoot.innerHTML = html;
    printRoot.style.display = 'block';
    setTimeout(() => { window.print(); printRoot.style.display = 'none'; printRoot.innerHTML = ''; }, 50);
  });

  // sinkron kalau katalog berubah dari halaman management
  window.addEventListener('storage', (ev) => {
    if (ev.key === KEY_PRODUCTS || ev.key === 'kasir_products_ping') {
      refreshCatalog();
    }
  });

  // start
  refreshCatalog();
  renderCart();
})();
