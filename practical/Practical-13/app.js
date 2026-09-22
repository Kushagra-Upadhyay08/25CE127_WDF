const api = 'api/events.php';
const list = document.querySelector('#eventList'), loading = document.querySelector('#loading'), notice = document.
querySelector('#notice');
const dialog = document.querySelector('#eventDialog'), form = document.querySelector('#eventForm');
const demoEvents = [{id:1,title:'WebCraft 2026 Hackathon',category:'Technical',description:'A 24-hour build sprint 
for StudentHub makers.',event_date:'2026-11-15',venue:'Central Computer Center',max_participants:80,status:
'upcoming'},{id:2,title:'Cloud & DevOps Masterclass',category:'Workshop',description:'Hands-on containers, CI/CD and 
cloud fundamentals.',event_date:'2026-11-20',venue:'Seminar Hall 3',max_participants:60,status:'upcoming'},{id:3,
title:'Spoural Sports Fest',category:'Sports',description:'Inter-department championship registrations.',event_date:
'2026-12-05',venue:'CHARUSAT Sports Complex',max_participants:200,status:'upcoming'}];
let events = [];
const escapeHtml = value => String(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;',
'"':'&quot;'}[char]));
function message(text, isError=false){notice.textContent=text;notice.className='notice'+(isError?' error':'');}
function render(rows){loading.hidden=true; list.innerHTML=rows.length?rows.map(e=>`<article class="card"><div 
class="card-top"><span class="badge">${escapeHtml(e.category)}</span><span class="badge">${escapeHtml(e.status)}</
span></div><h2>${escapeHtml(e.title)}</h2><p>${escapeHtml(e.description)}</p><div class="meta">■ ${escapeHtml(e.
event_date)} · ■ ${e.max_participants}</div><div class="meta">■ ${escapeHtml(e.venue)}</div><div class="card-
actions"><button class="link-btn" data-edit="${e.id}">Edit</button><button class="link-btn delete" data-delete="${e.
id}">Delete</button></div></article>`).join(''):'<p>No events match this search.</p>';}
async function load(query=''){loading.hidden=false;try{const res=await fetch(`${api}?search=${encodeURIComponent(
query)}`);const json=await res.json();if(!res.ok)throw new Error(json.message);events=json.data;render(events);
message(`${events.length} event${events.length===1?'':'s'} loaded from the API.`);}catch(error){events=demoEvents;
render(events);message('Preview data shown. Start PHP and MySQL to connect the live API.',true);}}
function openForm(event=null){form.reset();document.querySelector('#formTitle').textContent=event?'Update event':
'Create event';document.querySelector('#eventId').value=event?.id||'';['title','category','description','event_date',
'venue','max_participants','status'].forEach(k=>{document.querySelector('#'+k).value=event?.[k]??(
k==='max_participants'?100:'');});document.querySelector('#formError').textContent='';dialog.showModal();}
document.querySelector('#newEvent').onclick=()=>openForm();document.querySelector('#closeDialog').onclick=document.
querySelector('#cancel').onclick=()=>dialog.close();
document.querySelector('#search').addEventListener('input',e=>{clearTimeout(window.searchTimer);window.
searchTimer=setTimeout(()=>load(e.target.value),280);});
list.onclick=async e=>{let id=e.target.dataset.edit||e.target.dataset.delete;if(!id)return;let event=events.find(
x=>String(x.id)===id);if(e.target.dataset.edit)openForm(event);else if(confirm(`Delete “${event.title}”?`)){try{let 
r=await fetch(`${api}?id=${id}`,{method:'DELETE'}),j=await r.json();if(!r.ok)throw Error(j.message);message(j.
message);load();}catch(err){message(err.message,true);}}};
form.onsubmit=async e=>{e.preventDefault();let id=document.querySelector('#eventId').value;let data=Object.
fromEntries(['title','category','description','event_date','venue','max_participants','status'].map(k=>[k,document.
querySelector('#'+k).value]));try{let r=await fetch(`${api}${id?'?id='+id:''}`,{method:id?'PUT':'POST',headers:{
'Content-Type':'application/json'},body:JSON.stringify(data)}),j=await r.json();if(!r.ok)throw Error(j.message);
dialog.close();message(j.message);load();}catch(err){document.querySelector('#formError').textContent=err.message;}};
load();
