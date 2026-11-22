(() => {
  'use strict';
  const picks = new WeakMap();
  const find = (r,s) => r.querySelector(s);
  const token = () => document.head.dataset.requesttoken || window.OC?.requestToken || '';

  function render(pad, seq) {
    picks.set(pad,seq);
    find(pad,'output').textContent=`${seq.length} / 6`;
    find(pad,'.xyg-submit').disabled=seq.length!==6;
    find(pad,'[name=challenge]').value=seq.length===6
      ? `${pad.dataset.ticket}:${seq.join(';')}` : '';
  }
  document.addEventListener('click',e=>{
    const b=e.target.closest?.('button'), p=b?.closest('.xyg-pad');
    if (!p) return;
    const a=[...(picks.get(p)||[])];
    if (b.classList.contains('xyg-back')) a.pop();
    else if (b.hasAttribute('data-index') && a.length<6) {
      const n=Number(b.dataset.index);
      a.push(`${n%5},${Math.floor(n/5)}`);
    } else return;
    render(p,a);
    if (a.length===6) find(p,'.xyg-submit').focus();
  });
  document.addEventListener('submit',e=>{
    if (!e.target.matches('.xyg-form')) return;
    const p=find(e.target,'.xyg-pad');
    if (!p || (picks.get(p)||[]).length!==6) {e.preventDefault();return;}
    const t=find(e.target,'[name=requesttoken]');
    if (t && token()) t.value=token();
  },true);

  function init() {
    const root=document.getElementById('xyg-personal');
    if (!root || root.dataset.ready) return;
    root.dataset.ready='1';
    const box=find(root,'.xyg-toggle'), panel=find(root,'.xyg-enrol'),
      secret=find(root,'.xyg-secret'), msg=find(root,'.xyg-message'),
      pad=find(panel,'.xyg-pad'), form=find(panel,'form');
    let active=box.checked, busy=false, timer;
    function clear() {
      clearTimeout(timer); panel.hidden=true; secret.textContent='';
      pad.dataset.ticket=''; find(pad,'.xyg-keys').replaceChildren(); render(pad,[]);
    }
    async function post(op, data={}) {
      if (!token()) throw Error('Session non reconnue. Rechargez la page.');
      const r=await fetch(root.dataset[op],{method:'POST',credentials:'same-origin',
        cache:'no-store',headers:{requesttoken:token(),Accept:'application/json'},
        body:new URLSearchParams(data)});
      if (r.status===403 || r.status===401) {
        throw Error('Session expirée ou confirmation requise. Reconnectez-vous, puis revenez immédiatement dans Sécurité.');
      }
      if (!r.headers.get('content-type')?.includes('application/json')) {
        throw Error('Réponse inattendue. Rechargez la page pour vérifier l’état du compte.');
      }
      const j=await r.json();
      if (!r.ok) throw Error(j.error || 'Opération refusée. Rechargez la page pour vérifier l’état.');
      return j;
    }
    async function run(fn) {
      if (busy) return;
      busy=true; box.disabled=true; msg.textContent='';
      try { await fn(); }
      catch (e) {
        clear(); box.checked=active;
        msg.textContent=e.message+' En cas de doute, rechargez cette page.';
      } finally { busy=false; box.disabled=false; }
    }
    box.addEventListener('change',()=>run(async()=>{
      if (box.checked && !active) {
        const j=await post('begin');
        clear();
        pad.dataset.ticket=j.id; secret.textContent=j.code;
        for (const [i,d] of j.digits.entries()) {
          const b=document.createElement('button');
          b.type='button'; b.dataset.index=String(i); b.textContent=d;
          find(pad,'.xyg-keys').append(b);
        }
        panel.hidden=false;
        msg.textContent='Activation en attente de votre confirmation.';
        timer=setTimeout(()=>{clear();box.checked=active;msg.textContent='Délai écoulé. Recommencez.';},300000);
      } else if (active) {
        if (!window.confirm('Désactiver votre grille personnelle ?')) {box.checked=true;return;}
        await post('disable'); active=false; clear(); msg.textContent='Grille désactivée.';
      } else {
        clear(); msg.textContent='Activation abandonnée.';
      }
    }));
    find(panel,'.xyg-hide').addEventListener('click',()=>{secret.textContent='••••••';});
    form.addEventListener('submit',e=>{
      e.preventDefault();
      if ((picks.get(pad)||[]).length!==6) return;
      run(async()=>{
        await post('confirm',{challenge:find(pad,'[name=challenge]').value});
        active=true; box.checked=true; clear(); msg.textContent='Grille activée.';
      });
    });
    window.addEventListener('pagehide',()=>{clear();box.checked=active;});
  }
  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded',init);
  else init();
  // Les pages de réglages peuvent insérer des sections après DOMContentLoaded.
  document.addEventListener('change',e=>{
    if (e.target.matches?.('#xyg-personal .xyg-toggle') &&
        !e.target.closest('section').dataset.ready) {
      const wanted=e.target.checked;
      e.target.checked=e.target.defaultChecked;
      init(); e.target.checked=wanted;
      e.target.dispatchEvent(new Event('change',{bubbles:true}));
    }
  });
})();
