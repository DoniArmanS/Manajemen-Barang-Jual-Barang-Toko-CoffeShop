// Inventory frontend (no DB) – localStorage
const KEY = 'inv_items_v1';
const $  = (sel, ctx=document) => ctx.querySelector(sel);
const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

let SORT = { key: null, dir: 1 }; // 1 asc, -1 desc

function load(){ return JSON.parse(localStorage.getItem(KEY) || '[]'); }
function save(list){ localStorage.setItem(KEY, JSON.stringify(list)); }
function log(msg){
  const li = document.createElement('li');
  li.textContent = new Date().toLocaleString() + ' — ' + msg;
  const ul = document.getElementById('log');
  if (ul) ul.prepend(li);
}

function statusOf(it){ if((it.stock|0)<=0) return 'out'; if((it.stock|0) <= (parseInt(it.min)||0)) return 'low'; return 'ok'; }
function badge(st){ const m={ok:'Ready',low:'Low',out:'Out'}; return `<span class="inv-badge ${st}">${m[st]}</span>`; }
function getById(id){ return load().find(x=>x.id===id); }

function upsert(item){
  const list = load();
  if(!item.id){ item.id=Date.now(); list.push(item); log(`Tambah item: ${item.name} (+${item.stock})`); }
  else { const i=list.findIndex(x=>x.id===item.id); if(i>=0) list[i]=item; log(`Ubah item: ${item.name}`); }
  save(list); render();
}
function delItem(id){
  const it=getById(id);
  Swal.fire({ title:'Hapus item?', text:it?.name||'', icon:'warning', showCancelButton:true, confirmButtonText:'Hapus' })
  .then(r=>{ if(r.isConfirmed){ save(load().filter(x=>x.id!==id)); log(`Hapus item: ${it?.name}`); render(); }});
}
function openItem(id){
  const f=$('#formItem'); f.reset(); f.querySelector('[name=id]').value=id||'';
  if(id){ const it=getById(id); ['name','sku','category','min','unit','stock','note'].forEach(k=>f.querySelector(`[name=${k}]`).value=it[k]??''); }
  new bootstrap.Modal('#modalItem').show();
}
function openAdjust(id){
  const it=getById(id); const f=$('#formAdjust'); f.reset();
  f.querySelector('[name=id]').value=id; f.querySelector('[name=name]').value=it.name;
  new bootstrap.Modal('#modalAdjust').show();
}

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
  let items = load();
  let low=0, out=0;

  // filter & stats
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

  // render rows
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

  // stats
  const set = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = val; };
  set('statTotal', load().length);
  set('statLow', low);
  set('statOut', out);
}

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
    const it=getById(id); it.stock=Math.max(0, Number(it.stock||0)+delta); upsert(it);
    log(`${delta>=0?'+':''}${delta} ${it.unit||'pcs'} untuk ${it.name} (${fd.get('reason')||'update'})`);
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

  // import/export
  $('#btnExport')?.addEventListener('click', ()=>{
    const rows=[['name','sku','category','stock','min','unit','note']];
    for(const it of load()){ rows.push([it.name,it.sku||'',it.category||'',it.stock||0,it.min||0,it.unit||'pcs',it.note||'']); }
    const csv=rows.map(r=>r.map(x=>`"${String(x).replace(/"/g,'""')}"`).join(',')).join('\n');
    const a=document.createElement('a'); a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'})); a.download='inventory.csv'; a.click();
  });

  $('#btnImport')?.addEventListener('click', ()=>{
    const i=document.createElement('input'); i.type='file'; i.accept='.csv';
    i.onchange=async()=>{
      const text=await i.files[0].text(); const lines=text.trim().split(/\r?\n/).slice(1);
      const list=load();
      for(const line of lines){
        const cols=line.match(/("([^"]|"")*"|[^,]+)/g)?.map(c=>c.replace(/^"|"$/g,'').replace(/""/g,'"'))||[];
        const [name,sku,category,stock,min,unit,note]=cols;
        list.push({id:Date.now()+Math.random(), name, sku, category, stock:Number(stock||0), min:Number(min||0), unit:unit||'pcs', note});
      }
      save(list); render(); log('Import CSV berhasil');
    };
    i.click();
  });

  // seed pertama kali
  if(load().length===0){
    save([
      {id:1,name:'Biji Kopi Arabica',sku:'BEAN-AR',category:'Bahan',stock:12,min:5,unit:'kg',note:'Gudang A'},
      {id:2,name:'Susu Full Cream',sku:'MILK-FC',category:'Bahan',stock:4,min:6,unit:'L',note:'Perlu restock'},
      {id:3,name:'Gelas Cup 12oz',sku:'CUP-12',category:'Perlengkapan',stock:0,min:50,unit:'pcs',note:''},
    ]);
  }

  render();
});
