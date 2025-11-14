/* public/assets/kasir/manage.js
   Cashier Management + Resep (bahan dari Inventory) – wajib ≥1 bahan + list scroll
*/
(function(){
  const KEY_PRODUCTS = 'kasir_products_v1';
  const KEY_INV      = 'inv_items_v1';
  const ALLOWED_CATS = ['Minuman','Makanan','Snack'];

  const $  = (s, c=document)=>c.querySelector(s);
  const $$ = (s, c=document)=>Array.from(c.querySelectorAll(s));
  const fmt = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(+n||0);
  const idGen = () => 'P' + Math.random().toString(36).slice(2,7).toUpperCase();

  // ===== Activity (local + server) =====
  function postActivity(payload){
    const ACT_PREFIX = 'activity_';
    const todayKey = () => { const d=new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; };
    const actKey = () => ACT_PREFIX + todayKey();
    const list = JSON.parse(localStorage.getItem(actKey()) || '[]');
    list.unshift({ ts:Date.now(), source:'cashier', ...payload, meta:payload.meta||{} });
    localStorage.setItem(actKey(), JSON.stringify(list));
    localStorage.setItem('activity_ping', Date.now().toString());
    fetch('/activity',{
      method:'POST',
      headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||'','Content-Type':'application/json'},
      body:JSON.stringify(payload)
    }).catch(()=>{});
  }

  // ===== Storage =====
  function loadProducts(){ try{ return JSON.parse(localStorage.getItem(KEY_PRODUCTS) || '[]'); }catch{return [];} }
  function saveProducts(list){
    localStorage.setItem(KEY_PRODUCTS, JSON.stringify(list));
    LIST = list;
    localStorage.setItem('kasir_products_ping', Date.now().toString()); // ping halaman kasir
  }
  function loadInventory(){ try{ return JSON.parse(localStorage.getItem(KEY_INV) || '[]'); }catch{return [];} }

  // ===== State =====
  let LIST = [];
  let FILTER = { q:'', cat:'ALL', sort:null, dir:1 };
  let INV_CACHE = [];

  // ===== UI helper Bahan =====
  function renderIngredientPicker(){
    const sel = $('#ingSelect'); const inv = INV_CACHE = loadInventory();
    sel.innerHTML = `<option value="">-- pilih bahan --</option>` +
      inv.map(it=>`<option value="${it.id}">${it.name} (${it.unit||'pcs'}; stok ${it.stock|0})</option>`).join('');
  }
  function readFormIngredients(){
    return $$('#ingList .ing-row').map(row => ({
      invId: Number(row.dataset.id),
      name : row.querySelector('.name').textContent,
      use  : Number(row.querySelector('.qty').textContent) || 0,
      unit : row.querySelector('.unit').textContent
    }));
  }
  function setFormIngredients(arr){
    const list = $('#ingList'); list.innerHTML = '';
    for(const ing of (arr||[])) list.appendChild(makeIngRow(ing.invId, ing.name, ing.use, ing.unit));
  }
      function makeIngRow(invId, name, use, unit){
      const row = document.createElement('div');
      row.className = 'ing-row';
      row.dataset.id = invId;

      row.innerHTML = `
        <span class="name">${name}</span>
        <span class="badge-soft qty">${use}</span>
        <span class="badge-soft unit">${unit || 'pcs'}</span>
        <button class="btn btn-sm btn-del" type="button" aria-label="Hapus">&times;</button>
      `;
      row.querySelector('.btn-del').onclick = ()=> row.remove();
      return row;
    }


  // ===== Render daftar produk =====
  function computeMaxMakeable(p){
    if (Array.isArray(p.ingredients) && p.ingredients.length){
      const inv = loadInventory();
      let max = Infinity;
      for (const ing of p.ingredients){
        const it = inv.find(x=>Number(x.id)===Number(ing.invId));
        const avail = it ? Number(it.stock||0) : 0;
        const need  = Math.max(0, Number(ing.use||0));
        if (need>0) max = Math.min(max, Math.floor(avail/need));
      }
      if (!isFinite(max)) max = 0;
      return Math.max(0, max);
    }
    return Number(p.stock||0);
  }
  function render(){
    const grid = $('#grid');
    const q = FILTER.q.toLowerCase();

    let items = LIST.filter(p => (FILTER.cat==='ALL'||p.cat===FILTER.cat) && (q===''||p.name.toLowerCase().includes(q)||(p.id||'').toLowerCase().includes(q)));
    if(FILTER.sort){
      items = [...items].sort((a,b)=>{
        const ka=(a[FILTER.sort]??'').toString().toLowerCase(), kb=(b[FILTER.sort]??'').toString().toLowerCase();
        const na=+ka, nb=+kb; let cmp; cmp = (!isNaN(na)&&!isNaN(nb))?na-nb:(ka>kb?1:ka<kb?-1:0); return cmp*FILTER.dir;
      });
    }

    grid.innerHTML = items.map(p=>{
      const can = computeMaxMakeable(p);
      return `
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
                <span class="text-xs text-secondary">Bisa dibuat: ${can}</span>
              </div>
              <div class="text-xxs text-secondary mt-1">
                ${Array.isArray(p.ingredients)&&p.ingredients.length
                  ? p.ingredients.map(i=>`${i.name}×${i.use}${i.unit||''}`).join(' • ')
                  : '<em>tanpa bahan</em>'}
              </div>
              <div class="d-grid gap-2 mt-2">
                <button class="btn btn-sm btn-dark" data-edit="${p.id}">EDIT</button>
                <button class="btn btn-sm btn-outline-danger" data-del="${p.id}">DELETE</button>
              </div>
            </div>
          </div>
        </div>`;
    }).join('');

    grid.querySelectorAll('[data-edit]').forEach(b=>b.addEventListener('click',()=>openForm(b.dataset.edit)));
    grid.querySelectorAll('[data-del]').forEach(b=>b.addEventListener('click',()=>del(b.dataset.del)));
  }

  // ===== Form add/edit =====
  function openForm(id){
    renderIngredientPicker();

    const m = new bootstrap.Modal('#modalMenu');
    const f = $('#formMenu'); f.reset();

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

    setFormIngredients(it?.ingredients || []);
    f.dataset.editing = it ? '1' : '';
    m.show();
  }

  function fileToDataURL(file){
    return new Promise((resolve,reject)=>{
      const reader=new FileReader(); reader.onload=e=>resolve(e.target.result);
      reader.onerror=reject; reader.readAsDataURL(file);
    });
  }

  function addIngredientFromForm(){
    const sel = $('#ingSelect');
    const invId = Number(sel.value||0);
    const use   = Number($('#ingUse').value||0);
    if (!invId || use<=0) return;

    const it = (INV_CACHE.length?INV_CACHE:loadInventory()).find(x=>Number(x.id)===invId);
    if(!it) return;

    // jika sudah ada → update qty
    const exist = $$('#ingList .ing-row').find(r=>Number(r.dataset.id)===invId);
    if (exist){
      const cur = Number(exist.querySelector('.use').textContent)||0;
      exist.querySelector('.use').textContent = String(cur + use);
    } else {
      $('#ingList').appendChild(makeIngRow(invId, it.name, use, it.unit||'pcs'));
    }

    // auto scroll ke bawah
    const box = $('#ingList'); box.scrollTop = box.scrollHeight;
  }

  // ===== Actions =====
  async function submitForm(e){
    e.preventDefault();
    const f = e.target;
    const editing = !!f.dataset.editing;

    const ingredients = readFormIngredients();
    if (ingredients.length === 0){
      if (window.Swal) Swal.fire({icon:'warning', title:'Bahan wajib', text:'Tambahkan minimal satu bahan untuk menu ini.'});
      else alert('Tambahkan minimal satu bahan untuk menu ini.');
      return; // stop submit
    }

    const payload = {
      id: f.id.value.trim() || idGen(),
      name: f.name.value.trim(),
      price: Number(f.price.value||0),
      cat: ALLOWED_CATS.includes(f.cat.value) ? f.cat.value : 'Minuman',
      stock: Number(f.stock.value||0),
      note: f.note.value.trim(),
      ingredients
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
        saveProducts(LIST); render();
        bootstrap.Modal.getInstance($('#modalMenu')).hide();
        postActivity({action:'menu_update', item_name:payload.name, note:`Harga ${fmt(payload.price)} • ${payload.cat}`, meta:{id:payload.id}});
      }
    }else{
      LIST.push({ ...payload, img });
      saveProducts(LIST); render();
      bootstrap.Modal.getInstance($('#modalMenu')).hide();
      postActivity({action:'menu_create', item_name:payload.name, note:`Harga ${fmt(payload.price)} • ${payload.cat}`, meta:{id:payload.id}});
    }
  }

  function del(id){
    const it = LIST.find(x=>x.id===id);
    if(!it) return;
    if(!confirm(`Hapus menu "${it.name}" ?`)) return;
    LIST = LIST.filter(x=>x.id!==id);
    saveProducts(LIST); render();
    postActivity({action:'menu_delete', item_name:it.name, note:it.cat, meta:{id}});
  }

  // ===== Import/Export =====
  function exportJSON(){
    const a = document.createElement('a');
    const blob = new Blob([JSON.stringify(LIST,null,2)],{type:'application/json'});
    a.href = URL.createObjectURL(blob); a.download = 'kasir_products.json'; a.click();
  }
  function importJSON(){
    const i=document.createElement('input'); i.type='file'; i.accept='.json,application/json';
    i.onchange=async()=>{
      const text=await i.files[0].text();
      try{
        const data=JSON.parse(text); if(!Array.isArray(data)) throw new Error('Format tidak valid');
        const map=new Map(LIST.map(x=>[x.id,x]));
        for(const p of data){
          const id=p.id || idGen();
          const cat=ALLOWED_CATS.includes(p.cat)?p.cat:'Minuman';
          map.set(id,{ id, name:p.name||'Item', price:+(p.price||0), cat,
            stock:+(p.stock||0), img:p.img||'/assets/img/placeholder.jpg', note:p.note||'',
            ingredients:Array.isArray(p.ingredients)?p.ingredients:[] });
        }
        LIST=[...map.values()]; saveProducts(LIST); render();
      }catch(e){ alert('Import gagal: '+e.message); }
    };
    i.click();
  }

  // ===== Bindings =====
  document.addEventListener('DOMContentLoaded', ()=>{
    LIST = loadProducts(); render();

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
        btn.classList.add('active'); FILTER.cat = btn.dataset.cat; render();
      });
    });
    $$('.btn-group [data-sort]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const key = btn.dataset.sort; FILTER.dir = (FILTER.sort===key) ? -FILTER.dir : 1; FILTER.sort = key; render();
      });
    });

    $('#btnAddIng')?.addEventListener('click', addIngredientFromForm);

    $('#btnExportJSON')?.addEventListener('click', exportJSON);
    $('#btnImportJSON')?.addEventListener('click', importJSON);
  });
})();