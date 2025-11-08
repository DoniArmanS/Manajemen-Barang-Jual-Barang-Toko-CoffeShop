// public/assets/kasir/inventory.js
// Inventory (no DB) — localStorage + Activity harian (persist) + ping Dashboard
(function(){
  const KEY_ITEMS  = 'inv_items_v1';   // dipakai dashboard juga
  const ACT_PREFIX = 'activity_';      // activity harian per tanggal
  const $  = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

  // ==== Tambahan: transaksi untuk Dashboard ====
  const KEY_TRANSACTIONS = 'coffeeshop_transactions_v1';
  function loadTransactions(){ try { return JSON.parse(localStorage.getItem(KEY_TRANSACTIONS) || '[]'); } catch { return []; } }
  function saveTransactions(list){ localStorage.setItem(KEY_TRANSACTIONS, JSON.stringify(list)); pingDashboard(); }

  let SORT = { key: null, dir: 1 }; // 1 asc, -1 desc

  // ========= Helpers =========
  function todayKey(){
    const d=new Date();
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
  }
  function actKey(){ return ACT_PREFIX + todayKey(); }

  function loadItems(){ return JSON.parse(localStorage.getItem(KEY_ITEMS) || '[]'); }
  function saveItems(list){ localStorage.setItem(KEY_ITEMS, JSON.stringify(list)); pingDashboard(); }

  function statusOf(it){
    if((it.stock|0)<=0) return 'out';
    if((it.stock|0) <= (parseInt(it.min)||0)) return 'low';
    return 'ok';
  }
  function badge(st){ const m={ok:'Ready',low:'Low',out:'Out'}; return `<span class="inv-badge ${st}">${m[st]}</span>`; }
  function getById(id){ return loadItems().find(x=>x.id===id); }

  // ========= Activity harian (persist di localStorage) =========
  // Ambil SEMUA activity harian (lintas modul)
  function loadActivityAll(){
    try { return JSON.parse(localStorage.getItem(actKey()) || '[]'); } catch { return []; }
  }
  // Filter khusus INVENTORY (yang tampil di panel ini)
  function invActivities(){
    const all = loadActivityAll();
    return all.filter(a => a.source === 'inventory' || a.source === undefined); // entry lama tanpa source dianggap inventory
  }
  function saveActivity(list){
    if (list.length > 1000) list = list.slice(-1000);
    localStorage.setItem(actKey(), JSON.stringify(list));
    pingDashboard();
  }
  function pushActivity({action, item_name, qty_change=null, note='', meta={}}){
    const list = loadActivityAll(); // gabungkan dengan activity modul lain
    list.unshift({
      ts: Date.now(),
      source: 'inventory',
      action, item_name, qty_change, note, meta
    });
    saveActivity(list);
  }

  // sisipkan style kecil untuk scrollbar & toolbar catatan (sekali di-load)
  function injectStylesOnce(){
  if (document.getElementById('inv-log-styles')) return;
  const css = `
    /* host utk posisi absolute toolbar */
    .log-host { position: relative; }

    /* toolbar export: mengambang kanan-atas, tidak mem-push konten */
    .log-toolbar {
      position: absolute;
      top: 17px;
      right: 20px;
      display: flex;
      align-items: center;
      gap: .5rem;
      margin: 0;
      padding: 0;
      background: transparent;
      z-index: 1;
    }
    #btnInventoryActivityExport { padding: .25rem .5rem; }

    /* list catatan: kalau >5 item jadi scroll */
    #log.scroll {
      max-height: 240px;
      overflow-y: auto;
      padding-right: 6px;
    }
    #log.scroll::-webkit-scrollbar { width: 6px; }
    #log.scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,.2); border-radius: 3px; }
  `;
  const style = document.createElement('style');
  style.id = 'inv-log-styles';
  style.textContent = css;
  document.head.appendChild(style);
}

  // buat toolbar export CSV di atas list catatan (kalau belum ada)
  function ensureLogToolbar(){
  const ul = document.getElementById('log');
  if (!ul) return;

  // posisikan di dalam card-body (kanan-atas)
  const host = ul.closest('.card-body') || ul.parentElement;
  if (!host) return;
  host.classList.add('log-host');

  // cari toolbar kalau sudah ada
  let toolbar = host.querySelector('.log-toolbar');
  if (!toolbar){
    toolbar = document.createElement('div');
    toolbar.className = 'log-toolbar';
    host.insertBefore(toolbar, host.firstChild); // paling atas dalam card body
  }

  // tombol export (sekali saja)
  let btn = document.getElementById('btnInventoryActivityExport');
  if (!btn){
    btn = document.createElement('button');
    btn.id = 'btnInventoryActivityExport';
    btn.type = 'button';
    btn.className = 'btn btn-sm btn-outline-secondary';
    btn.textContent = 'EXPORT CSV';
    btn.addEventListener('click', exportActivityCSV);
    toolbar.appendChild(btn);
  }
}


  // tampilan catatan di panel “CATATAN”
  function renderActivityList(){
    injectStylesOnce();
    ensureLogToolbar();

    const ul = document.getElementById('log');
    if (!ul) return;

    const list = invActivities();      // hanya inventory
    ul.innerHTML = '';
    // toggle scrollbar bila > 5
    ul.classList.toggle('scroll', list.length > 5);

    const show = list.slice(0, 200);   // render max 200 item
    for (const a of show){
      const when = new Date(a.ts).toLocaleString('id-ID');
      const qty  = (a.qty_change===0 || a.qty_change)
                   ? ` (${a.qty_change>0?'+':''}${a.qty_change})` : '';
      const li = document.createElement('li');
      li.className = 'mb-2 pb-2 border-bottom';
      li.innerHTML = `<small class="text-muted">${when}</small><br>
        <strong>${a.action.toUpperCase()}</strong> — ${a.item_name||'Item'}${qty}${a.note?` — ${a.note}`:''}`;
      ul.appendChild(li);
    }
  }

  // ========= Export CSV untuk Activity harian dari halaman Inventory =========
  function exportActivityCSV(){
    const list = invActivities();
    const rows = [['datetime','action','item_name','qty_change','amount','note']];
    for (const a of list.slice().reverse()){ // paling lama → paling baru
      rows.push([
        new Date(a.ts).toISOString(),
        a.action,
        a.item_name || '',
        (a.qty_change===0 || a.qty_change) ? a.qty_change : '',
        (a.meta && a.meta.amount) ? a.meta.amount : '',
        (a.note||'').replace(/\r?\n/g,' ')
      ]);
    }
    const csv = rows.map(r=>r.map(x=>`"${String(x).replace(/"/g,'""')}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
    a.download = `activity_${todayKey()}_inventory.csv`;
    a.click();
  }

  // ========= Ping Dashboard (supaya donut & activity ikut update tanpa reload) =========
  function pingDashboard(){
    localStorage.setItem('activity_ping', Date.now().toString());
  }

  // ========= CRUD =========
  function upsert(item){
    const list = loadItems();
    if(!item.id){
      // create
      item.id = Date.now();
      list.push(item);
      pushActivity({ action:'create', item_name:item.name, qty_change:Number(item.stock||0), note:`SKU: ${item.sku||'-'}` });
    } else {
      // update
      const i=list.findIndex(x=>x.id===item.id);
      if(i>=0) list[i]=item;
      pushActivity({ action:'update', item_name:item.name, note:`SKU: ${item.sku||'-'}` });
    }
    saveItems(list);
    render();
    renderActivityList(); // langsung update panel
  }

  function delItem(id){
    const it=getById(id);
    Swal.fire({ title:'Hapus item?', text:it?.name||'', icon:'warning', showCancelButton:true, confirmButtonText:'Hapus' })
      .then(r=>{
        if(r.isConfirmed){
          saveItems(loadItems().filter(x=>x.id!==id));
          pushActivity({ action:'delete', item_name:it?.name || '(unknown)', note:`SKU: ${it?.sku || '-'}` });
          render();
          renderActivityList();
        }
      });
  }

  function openItem(id){
    const f=$('#formItem'); f.reset(); f.querySelector('[name=id]').value=id||'';
    if(id){
      const it=getById(id);
      ['name','sku','category','min','unit','stock','note','default_cost'].forEach(k=>{
        const el=f.querySelector(`[name=${k}]`);
        if (el) el.value=it[k]??'';
      });
    }
    new bootstrap.Modal('#modalItem').show();
  }

  function openAdjust(id){
    const it=getById(id); const f=$('#formAdjust'); f.reset();
    f.querySelector('[name=id]').value=id;
    f.querySelector('[name=name]').value=it?.name || '';
    // Prefill biaya jika ada default_cost dan field cost tersedia
    const costEl = f.querySelector('[name=cost]');
    if (costEl && it && typeof it.default_cost !== 'undefined') {
      costEl.value = Number(it.default_cost) || 0;
    }
    new bootstrap.Modal('#modalAdjust').show();
  }

  // ========= Sorting & Rendering =========
  function sortItems(items){
    if(!SORT.key) return items;
    return [...items].sort((a,b)=>{
      const va = (a[SORT.key]??'').toString().toLowerCase();
      const vb = (b[SORT.key]??'').toString().toLowerCase();
      if(!isNaN(+va) && !isNaN(+vb)) return (+va - +vb) * SORT.dir;
      return (va>vb?1:va<vb?-1:0) * SORT.dir;
    });
  }

  function render(){
    const q = ($('#q')?.value || '').toLowerCase();
    const active = document.querySelector('.btn-group [data-filter].active')?.dataset.filter || 'all';
    const body = $('#tbl tbody'); if (!body) return;
    body.innerHTML = '';
    let items = loadItems();
    let low=0, out=0;

    // stats & filter
    items.forEach(it => { const st=statusOf(it); if(st==='low') low++; if(st==='out') out++; });
    items = items.filter(it=>{
      const st=statusOf(it);
      const passF = (active==='all' || st===active);
      const hay=(it.name+it.sku+it.category).toLowerCase();
      const passQ = !q || hay.includes(q);
      return passF && passQ;
    });

    // sorting
    items = sortItems(items);

    // rows
    for(const it of items){
      const st = statusOf(it);
      const pct = Math.max(0, Math.min(100, it.min>0 ? Math.round((it.stock/it.min)*100) : 100));
      const color = st==='ok'?'bg-success':st==='low'?'bg-warning':'bg-danger';

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="fw-semibold">${it.name}</td>
        <td><code class="text-primary text-uppercase">${it.sku||'-'}</code></td>
        <td>${it.category||'-'}</td>
        <td class="text-center">
          <div class="d-flex flex-column align-items-center">
            <span class="fw-bold">${it.stock|0}</span>
            <div class="stock-pct w-100 mt-1"><div class="bar ${color}" style="width:${pct}%"></div></div>
          </div>
        </td>
        <td class="text-center">${it.min|0}</td>
        <td class="text-center">${it.unit||'pcs'}</td>
        <td>${badge(st)}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-dark me-2 btnAdjust">ADJUST</button>
          <button class="btn btn-sm btn-outline-secondary me-2 btnEdit">EDIT</button>
          <button class="btn btn-sm btn-outline-danger btnDel">HAPUS</button>
        </td>
      `;
      tr.querySelector('.btnEdit').onclick   = () => openItem(it.id);
      tr.querySelector('.btnDel').onclick    = () => delItem(it.id);
      tr.querySelector('.btnAdjust').onclick = () => openAdjust(it.id);
      body.appendChild(tr);
    }

    // ringkasan
    const set = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = val; };
    set('statTotal', loadItems().length);
    set('statLow', low);
    set('statOut', out);
  }

  // ========= Events =========
  document.addEventListener('DOMContentLoaded', () => {
    injectStylesOnce();

    // tambah/edit
    $('#formItem')?.addEventListener('submit', e=>{
      e.preventDefault();
      const fd=new FormData(e.target); const item=Object.fromEntries(fd.entries());
      item.id = item.id? Number(item.id): undefined;
      item.min = Number(item.min||0); item.stock = Number(item.stock||0);
      // simpan default_cost kalau ada field-nya
      if (typeof item.default_cost !== 'undefined' && item.default_cost !== '') {
        item.default_cost = Number(item.default_cost);
      } else {
        delete item.default_cost;
      }
      upsert(item);
      bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
      e.target.reset();
    });

    // adjust
    $('#formAdjust')?.addEventListener('submit', e=>{
      e.preventDefault();
      const fd=new FormData(e.target);
      const id=Number(fd.get('id')); const delta=Number(fd.get('delta')||0);
      const reason = fd.get('reason') || 'adjust';
      const cost   = Number(fd.get('cost')||0); // OPSIONAL biaya restock
      const it=getById(id);
      it.stock=Math.max(0, Number(it.stock||0)+delta);
      // simpan perubahan + activity adjust stok
      pushActivity({ action:'adjust', item_name:it.name, qty_change: delta, note:reason });

      // ==== Tambahan: catat pengeluaran agar Dashboard naik ====
      // Hanya saat restock (delta > 0) dan ada biaya (cost > 0)
      if (delta > 0 && cost > 0){
        // 1) Activity khusus expense (tampil juga di CATATAN)
        pushActivity({
          action:'expense',
          item_name: it.name,
          qty_change: delta,
          note: `Restock — ${reason}`,
          meta: { amount: cost }
        });

        // 2) Simpan TRANSACTION agar dashboard baca (type 'inventory' dihitung sebagai expense)
        const tx = loadTransactions();
        tx.push({
          id: 'tx_'+Date.now()+'_'+Math.random().toString(36).slice(2,8),
          type: 'inventory',                 // dashboard treat as pengeluaran
          amount: Number(cost)||0,
          datetime: new Date().toISOString(),
          note: `Restock ${it.name} (${delta})`
        });
        saveTransactions(tx);
      }
      // =====================================

      upsert(it);
      bootstrap.Modal.getInstance(document.getElementById('modalAdjust')).hide();
    });

    // search & filter
    $('#q')?.addEventListener('input', render);
    $$('.btn-group [data-filter]').forEach(btn=>{
      btn.onclick=()=>{ $$('.btn-group [data-filter]').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); render(); };
    });

    // sort
    $$('.btn-group [data-sort]').forEach(btn=>{
      btn.onclick=()=>{
        const key=btn.dataset.sort;
        SORT.dir = (SORT.key===key) ? -SORT.dir : 1;
        SORT.key = key; render();
      };
    });

    // export/import CSV items
    $('#btnExport')?.addEventListener('click', ()=>{
      const rows=[['name','sku','category','stock','min','unit','note','default_cost']];
      for(const it of loadItems()){ rows.push([it.name,it.sku||'',it.category||'',it.stock||0,it.min||0,it.unit||'pcs',it.note||'', (typeof it.default_cost!=='undefined'?it.default_cost:'')]); }
      const csv=rows.map(r=>r.map(x=>`"${String(x).replace(/"/g,'""')}"`).join(',')).join('\n');
      const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'})); a.download='inventory.csv'; a.click();
    });

    $('#btnImport')?.addEventListener('click', ()=>{
      const i=document.createElement('input'); i.type='file'; i.accept='.csv';
      i.onchange=async()=>{
        const text=await i.files[0].text(); const lines=text.trim().split(/\r?\n/).slice(1);
        const list=loadItems();
        for(const line of lines){
          const cols=line.match(/("([^"]|"")*"|[^,]+)/g)?.map(c=>c.replace(/^"|"$/g,'').replace(/""/g,'"'))||[];
          const [name,sku,category,stock,min,unit,note,default_cost]=cols;
          list.push({id:Date.now()+Math.random(), name, sku, category, stock:Number(stock||0), min:Number(min||0), unit:unit||'pcs', note, ...(default_cost?{default_cost:Number(default_cost)}:{})});
        }
        saveItems(list); render();
      };
      i.click();
    });

    // tombol export activity (kalau kamu sudah taruh di HTML)
    document.getElementById('btnInventoryActivityExport')?.addEventListener('click', exportActivityCSV);

    // render awal
    render();
    renderActivityList();
  });

  // expose untuk tombol di HTML
  window.invOpenItem   = openItem;
  window.invOpenAdjust = openAdjust;
  window.invDelItem    = delItem;
})();
