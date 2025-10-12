(function(){
  // ===== Dummy data (tanpa DB) =====
  const PRODUCTS = [
    { id:'P001', name:'Kopi Arabica 250g', price:45000, stock:15, cat:'Minuman', img:'/assets/img/home-decor-2.jpg' },
    { id:'P002', name:'Teh Hijau 100g',    price:32000, stock: 8, cat:'Minuman', img:'/assets/img/home-decor-3.jpg' },
    { id:'P003', name:'Gula Pasir 1kg',    price:15000, stock:22, cat:'Bahan',   img:'/assets/img/ivana-square.jpg' },
    { id:'P004', name:'Susu UHT 1L',       price:24000, stock:12, cat:'Bahan',   img:'/assets/img/team-1.jpg' },
    { id:'P005', name:'Biskuit Coklat',    price:12500, stock:18, cat:'Snack',   img:'/assets/img/team-3.jpg' },
    { id:'P006', name:'Minyak Goreng 2L',  price:35000, stock:10, cat:'Bahan',   img:'/assets/img/team-2.jpg' },
  ];
  const TAX = 0.10;
  const fmt = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n);

  const state = { items:new Map(), products:PRODUCTS, q:'', cat:'ALL' };

  // ===== helpers =====
  function calc(){
    let subtotal = 0;
    for(const {product,qty} of state.items.values()) subtotal += product.price * qty;
    const tax = Math.round(subtotal * TAX);
    return {subtotal,tax,total:subtotal+tax};
  }
  function orderNo(){
    const now = new Date();
    return `TRX-${now.getFullYear().toString().slice(-2)}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}-${String(now.getHours()).padStart(2,'0')}${String(now.getMinutes()).padStart(2,'0')}${String(now.getSeconds()).padStart(2,'0')}`;
  }

  // ===== products UI =====
  function renderProducts(){
    const grid = document.getElementById('productGrid');
    const q = state.q.toLowerCase(), cat = state.cat;
    const list = state.products.filter(p => (cat==='ALL'||p.cat===cat) && (q==='' || p.name.toLowerCase().includes(q) || p.id.toLowerCase().includes(q)));
    grid.innerHTML = list.map(p=>`
      <div class="col-6 col-md-4">
        <div class="p-2 product-card h-100">
          <img class="product-img" src="${p.img}" alt="${p.name}">
          <div class="mt-2">
            <span class="badge badge-chip ${p.cat==='Minuman'?'bg-gradient-info':p.cat==='Bahan'?'bg-gradient-success':'bg-gradient-warning'}">${p.cat}</span>
            <div class="fw-bold small mt-1">${p.name}</div>
            <div class="text-secondary text-xs">Stok: ${p.stock}</div>
            <div class="fw-bold mt-1">${fmt(p.price)}</div>
            <button class="btn btn-sm btn-dark w-100 mt-2" data-add="${p.id}">TAMBAH</button>
          </div>
        </div>
      </div>
    `).join('');
    grid.querySelectorAll('[data-add]').forEach(b=>b.addEventListener('click',()=>addToCart(b.dataset.add,1)));
  }

  function renderCategories(){
    const cats = ['ALL',...new Set(PRODUCTS.map(p=>p.cat))];
    const menu = document.getElementById('categoryMenu');
    menu.innerHTML = cats.map(c=>`<li><a class="dropdown-item" data-cat="${c}">${c}</a></li>`).join('');
    menu.querySelectorAll('[data-cat]').forEach(a=>a.addEventListener('click',()=>{
      state.cat = a.dataset.cat;
      renderProducts();
    }));
  }

  // ===== cart =====
  function addToCart(id, delta){
    const p = PRODUCTS.find(x=>x.id===id); if(!p) return;
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

    // toggle empty state
    const hasItems = items.length > 0;
    list.classList.toggle('d-none', !hasItems);
    empty.classList.toggle('d-none', hasItems);
    btnCheckout.disabled = !hasItems;
    btnClear.disabled = !hasItems;

    list.innerHTML = items.map(({product,qty})=>`
      <div class="list-group-item d-flex align-items-center justify-content-between">
        <div class="me-2">
          <div class="small fw-bold">${product.name}</div>
          <div class="text-xs text-secondary">${fmt(product.price)}</div>
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

  // ===== receipt =====
  function buildReceiptHtml(order) {
    const rows = order.items.map(({product, qty}) => `
      <tr>
        <td>${product.name}</td>
        <td style="text-align:center">${qty}</td>
        <td style="text-align:right">${fmt(product.price)}</td>
        <td style="text-align:right">${fmt(product.price * qty)}</td>
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
    if(state.items.size===0) return; // tombol sudah disabled saat kosong

    const pay = document.querySelector('input[name="pay"]:checked')?.value || 'Tunai';
    const order = { no: orderNo(), items:[...state.items.values()], pay, ...calc() };

    document.getElementById('receiptContent').innerHTML = buildReceiptHtml(order);
    new bootstrap.Modal(document.getElementById('receiptModal')).show();

    // kosongkan cart setelah sukses
    state.items.clear();
    renderCart();
  }

  // ===== init =====
  document.getElementById('searchInput').addEventListener('input',e=>{ state.q = e.target.value; renderProducts(); });
  document.getElementById('btnCheckout').addEventListener('click', checkout);
  document.getElementById('btnClear').addEventListener('click', ()=>{ state.items.clear(); renderCart(); });

  // === PRINT HANDLER: cetak hanya struk ===
  // pastikan anchor printRoot ada; jika tidak, buat
  let printRoot = document.getElementById('printRoot');
  if(!printRoot){
    printRoot = document.createElement('div');
    printRoot.id = 'printRoot';
    printRoot.style.display = 'none';
    document.body.appendChild(printRoot);
  }
  document.getElementById('btnPrint').addEventListener('click', () => {
    const html = document.getElementById('receiptContent').innerHTML; // sudah berisi <div id="receiptPaper">...</div>
    printRoot.innerHTML = html;
    printRoot.style.display = 'block';
    // beri sedikit jeda agar layout print stabil
    setTimeout(() => {
      window.print();
      printRoot.style.display = 'none';
      printRoot.innerHTML = '';
    }, 50);
  });

  renderCategories(); renderProducts(); renderCart();
})();
