// Inventory (no DB) — localStorage + Activity harian (persist) + ping Dashboard
(function(){
  const KEY_ITEMS = 'inv_items_v1';          // dipakai dashboard juga
  const ACT_PREFIX = 'activity_';            // activity harian per tanggal
  const $  = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

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
  function loadActivity(){
    try { return JSON.parse(localStorage.getItem(actKey()) || '[]'); } catch { return []; }
  }
  function saveActivity(list){
    // batasi max 1000 entries per hari biar nggak bengkak
    if (list.length > 1000) list = list.slice(-1000);
    localStorage.setItem(actKey(), JSON.stringify(list));
    pingDashboard();
  }
  function pushActivity({action, item_name, qty_change=null, note='', meta={}}){
    const list = loadActivity();
    list.unshift({
      ts: Date.now(),
      source: 'inventory',
      action, item_name, qty_change, note, meta
    });
    saveActivity(list);
  }

  // tampilan catatan di panel “CATATAN”
  function renderActivityList(){
    const ul = document.getElementById('log');
    if (!ul) return;
    ul.innerHTML = '';
    const list = loadActivity();              // newest first (karena unshift)
    const show = list.slice(0, 200);          // render sampai 200 item (scroll kebawah sisanya)
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
      ['name','sku','category','min','unit','stock','note'].forEach(k=>{
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

  // ========= Export CSV untuk Activity harian dari halaman Inventory (opsional tombol) =========
  function exportActivityCSV(){
    const list = loadActivity();
    const rows = [['datetime','action','item_name','qty_change','note']];
    for (const a of list.slice().reverse()){ // paling lama ke paling baru
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

  // ========= Events =========
  document.addEventListener('DOMContentLoaded', () => {
    // tambah/edit
    $('#formItem')?.addEventListener('submit', e=>{
      e.preventDefault();
      const fd=new FormData(e.target); const item=Object.fromEntries(fd.entries());
      item.id = item.id? Number(item.id): undefined;
      item.min = Number(item.min||0); item.stock = Number(item.stock||0);
      upsert(item);
      bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
      e.target.reset();
    });

    // adjust
    $('#formAdjust')?.addEventListener('submit', e=>{
      e.preventDefault();
      const fd=new FormData(e.target);
      const id=Number(fd.get('id')); const delta=Number(fd.get('delta')||0);
      const it=getById(id);
      it.stock=Math.max(0, Number(it.stock||0)+delta);
      // simpan perubahan + activity
      pushActivity({ action:'adjust', item_name:it.name, qty_change: delta, note:(fd.get('reason')||'adjust') });
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
      const rows=[['name','sku','category','stock','min','unit','note']];
      for(const it of loadItems()){ rows.push([it.name,it.sku||'',it.category||'',it.stock||0,it.min||0,it.unit||'pcs',it.note||'']); }
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
          const [name,sku,category,stock,min,unit,note]=cols;
          list.push({id:Date.now()+Math.random(), name, sku, category, stock:Number(stock||0), min:Number(min||0), unit:unit||'pcs', note});
        }
        saveItems(list); render();
      };
      i.click();
    });

    // (opsional) tombol export activity di halaman inventory jika kamu tambahkan btn dengan id ini
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
