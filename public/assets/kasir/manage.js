/* public/assets/kasir/manage.js
   Cashier Management (tanpa DB) — sinkron ke localStorage + Activity log
   - Key storage produk: 'kasir_products_v1'
   - Catatan aktivitas: /activity (menu_create, menu_update, menu_delete)
*/
(function(){
  const KEY = 'kasir_products_v1';                 // dibaca juga oleh cashier.js
  const ALLOWED_CATS = ['Minuman','Makanan','Snack'];

  const $  = (s, c=document)=>c.querySelector(s);
  const $$ = (s, c=document)=>Array.from(c.querySelectorAll(s));
  const fmt = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(+n||0);
  const idGen = () => 'P' + Math.random().toString(36).slice(2,7).toUpperCase();

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


  // ---------- State ----------
  let LIST = [];
  let FILTER = { q:'', cat:'ALL', sort:null, dir:1 }; // dir: 1 asc, -1 desc

  // ---------- Storage ----------
  function load(){
    try{
      const raw = localStorage.getItem(KEY);
      if(raw) return JSON.parse(raw);
    }catch{}
    // Seed default (tanpa kategori Bahan)
    const seed = [
      { id:'P001', name:'Kopi Arabica 250g', price:45000, stock:15, cat:'Minuman', img:'/assets/img/home-decor-2.jpg' },
      { id:'P002', name:'Teh Hijau 100g',    price:32000, stock: 8, cat:'Minuman', img:'/assets/img/home-decor-3.jpg' },
      { id:'P003', name:'Croissant Almond',  price:35000, stock:10, cat:'Snack',   img:'/assets/img/team-2.jpg' },
      { id:'P004', name:'Cappuccino',        price:28000, stock:20, cat:'Minuman', img:'/assets/img/ivana-square.jpg' },
      { id:'P005', name:'Sandwich Tuna',     price:27000, stock:12, cat:'Makanan', img:'/assets/img/team-3.jpg' },
    ];
    localStorage.setItem(KEY, JSON.stringify(seed));
    return seed;
  }
  function save(list){
    localStorage.setItem(KEY, JSON.stringify(list));
    LIST = list;
    // biar halaman kasir ke-refresh katalognya
    localStorage.setItem('kasir_products_ping', Date.now().toString());
  }

  // ---------- Render ----------
  function render(){
    const grid = $('#grid');
    const q = FILTER.q.toLowerCase();

    let items = LIST.filter(p =>
      (FILTER.cat==='ALL' || p.cat===FILTER.cat) &&
      (q==='' || p.name.toLowerCase().includes(q) || (p.id||'').toLowerCase().includes(q))
    );

    if(FILTER.sort){
      items = [...items].sort((a,b)=>{
        const ka = (a[FILTER.sort] ?? '').toString().toLowerCase();
        const kb = (b[FILTER.sort] ?? '').toString().toLowerCase();
        const na = +ka, nb = +kb;
        let cmp;
        if(!isNaN(na) && !isNaN(nb)) cmp = na-nb; else cmp = ka>kb?1:ka<kb?-1:0;
        return cmp*FILTER.dir;
      });
    }

    grid.innerHTML = items.map(p=>`
      <div class="col-6 col-md-4 col-xl-3">
        <div class="mgmt-card h-100">
          <img class="mgmt-img" src="${p.img || '/assets/img/placeholder.jpg'}" alt="${p.name}">
          <div class="p-2">
            <div class="d-flex justify-content-between align-items-start">
              <div class="small fw-bold">${p.name}</div>
              <span class="badge bg-light text-dark">${p.cat}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <span class="price">${fmt(p.price)}</span>
              <span class="text-xs text-secondary">Stok: ${p.stock|0}</span>
            </div>
            <div class="d-grid gap-2 mt-2">
              <button class="btn btn-sm btn-dark" data-edit="${p.id}">EDIT</button>
              <button class="btn btn-sm btn-outline-danger" data-del="${p.id}">DELETE</button>
            </div>
          </div>
        </div>
      </div>
    `).join('');

    grid.querySelectorAll('[data-edit]').forEach(b=>b.addEventListener('click',()=>openForm(b.dataset.edit)));
    grid.querySelectorAll('[data-del]').forEach(b=>b.addEventListener('click',()=>del(b.dataset.del)));
  }

  // ---------- Form add/edit ----------
  function openForm(id){
    const m = new bootstrap.Modal('#modalMenu');
    const f = $('#formMenu');
    f.reset();

    // Isi dropdown kategori: hanya ALLOWED_CATS
    const sel = f.querySelector('select[name="cat"]');
    sel.innerHTML = ALLOWED_CATS.map(c=>`<option>${c}</option>`).join('');

    const it = LIST.find(x=>x.id===id);
    $('#imgPreview').src = it?.img || '/assets/img/placeholder.jpg';
    f.name.value  = it?.name || '';
    f.price.value = it?.price ?? 0;
    f.cat.value   = it?.cat && ALLOWED_CATS.includes(it.cat) ? it.cat : 'Minuman';
    f.stock.value = it?.stock ?? 0;
    f.id.value    = it?.id || '';
    f.note.value  = it?.note || '';
    f.dataset.editing = it ? '1' : '';
    m.show();
  }

  // file → dataURL
  function fileToDataURL(file){
    return new Promise((resolve,reject)=>{
      const reader = new FileReader();
      reader.onload = e => resolve(e.target.result);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }

  // ---------- Actions ----------
  async function submitForm(e){
    e.preventDefault();
    const f = e.target;
    const editing = !!f.dataset.editing;
    const payload = {
      id: f.id.value.trim() || idGen(),
      name: f.name.value.trim(),
      price: Number(f.price.value||0),
      cat: ALLOWED_CATS.includes(f.cat.value) ? f.cat.value : 'Minuman',
      stock: Number(f.stock.value||0),
      note: f.note.value.trim()
    };

    let img = $('#imgPreview').src;
    const fileInput = $('#imgFile');
    if(fileInput.files && fileInput.files[0]){
      try{ img = await fileToDataURL(fileInput.files[0]); }catch{}
    }

    if(editing){
      const i = LIST.findIndex(x=>x.id===payload.id);
      if(i>=0){
        LIST[i] = { ...LIST[i], ...payload, img };
        save(LIST);
        render();
        bootstrap.Modal.getInstance($('#modalMenu')).hide();
        postActivity({action:'menu_update', item_name:payload.name, note:`Harga ${fmt(payload.price)} • ${payload.cat}`, meta:{id:payload.id}});
      }
    }else{
      LIST.push({ ...payload, img });
      save(LIST);
      render();
      bootstrap.Modal.getInstance($('#modalMenu')).hide();
      postActivity({action:'menu_create', item_name:payload.name, note:`Harga ${fmt(payload.price)} • ${payload.cat}`, meta:{id:payload.id}});
    }
  }

  function del(id){
    const it = LIST.find(x=>x.id===id);
    if(!it) return;
    if(!confirm(`Hapus menu "${it.name}" ?`)) return;
    LIST = LIST.filter(x=>x.id!==id);
    save(LIST);
    render();
    postActivity({action:'menu_delete', item_name:it.name, note:it.cat, meta:{id}});
  }

  // ---------- Import/Export ----------
  function exportJSON(){
    const a = document.createElement('a');
    const blob = new Blob([JSON.stringify(LIST,null,2)],{type:'application/json'});
    a.href = URL.createObjectURL(blob);
    a.download = 'kasir_products.json';
    a.click();
  }
  function importJSON(){
    const i = document.createElement('input');
    i.type='file'; i.accept='.json,application/json';
    i.onchange = async ()=>{
      const text = await i.files[0].text();
      try{
        const data = JSON.parse(text);
        if(!Array.isArray(data)) throw new Error('Format tidak valid');
        // merge by id (replace)
        const map = new Map(LIST.map(x=>[x.id,x]));
        for(const p of data){
          const id = p.id || idGen();
          const cat = ALLOWED_CATS.includes(p.cat) ? p.cat : 'Minuman';
          map.set(id, {
            id, name:p.name||'Item', price:+(p.price||0), cat,
            stock:+(p.stock||0), img:p.img||'/assets/img/placeholder.jpg', note:p.note||''
          });
        }
        LIST = [...map.values()];
        save(LIST);
        render();
      }catch(e){ alert('Import gagal: '+e.message); }
    };
    i.click();
  }

  // ---------- Bindings ----------
  document.addEventListener('DOMContentLoaded', ()=>{
    LIST = load();
    render();

    $('#btnAdd')?.addEventListener('click', ()=>openForm(null));
    $('#formMenu')?.addEventListener('submit', submitForm);
    $('#imgFile')?.addEventListener('change', async (e)=>{
      const f = e.target.files?.[0]; if(!f) return;
      try{ $('#imgPreview').src = await fileToDataURL(f); }catch{}
    });

    $('#q')?.addEventListener('input', e=>{ FILTER.q = e.target.value; render(); });

    $$('#content .btn-group [data-cat], .btn-group [data-cat]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        $$('#content .btn-group [data-cat], .btn-group [data-cat]').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        FILTER.cat = btn.dataset.cat;
        render();
      });
    });

    $$('.btn-group [data-sort]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const key = btn.dataset.sort;
        FILTER.dir = (FILTER.sort===key) ? -FILTER.dir : 1;
        FILTER.sort = key; render();
      });
    });

    $('#btnExportJSON')?.addEventListener('click', exportJSON);
    $('#btnImportJSON')?.addEventListener('click', importJSON);
  });
})();
