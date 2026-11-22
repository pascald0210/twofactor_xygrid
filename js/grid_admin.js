(() => {
  'use strict';
  function init(root) {
    if (root.dataset.ready) return;
    root.dataset.ready = '1';
    const labels = JSON.parse(root.dataset.labels);
    const form = root.querySelector('form'), input = root.querySelector('input');
    const check = form.querySelector('button'), unlock = root.querySelector('.xyg-admin-unlock');
    const msg = root.querySelector('.xyg-admin-status');
    let selected = null, busy = false;
    const reset = () => { selected = null; unlock.hidden = true; unlock.disabled = true; };
    input.addEventListener('input', () => { reset(); msg.textContent = ''; });
    async function post(op, data) {
      const token = document.head.dataset.requesttoken || window.OC?.requestToken;
      if (!token) throw Error(labels.denied);
      const r = await fetch(root.dataset[op], {method:'POST', credentials:'same-origin',
        cache:'no-store', headers:{requesttoken:token, Accept:'application/json'},
        body:new URLSearchParams(data)});
      if ([401,403,412].includes(r.status)) throw Error(labels.denied);
      if (r.status === 409) throw Error(labels.changed);
      if (!r.ok || !r.headers.get('content-type')?.includes('application/json'))
        throw Error(labels.error);
      return r.json();
    }
    async function run(fn) {
      if (busy) return;
      busy = true; input.disabled = check.disabled = true; reset(); msg.textContent = '';
      try { await fn(); }
      catch (e) {
        reset();
        msg.textContent = [labels.denied,labels.changed].includes(e.message) ? e.message : labels.error;
      } finally { busy = false; input.disabled = check.disabled = false; }
    }
    form.addEventListener('submit', e => {
      e.preventDefault();
      const uid = input.value;
      run(async () => {
        const j = await post('lookup',{uid});
        if (j.state === 'locked') {
          selected = {uid:j.uid, revision:j.revision};
          msg.textContent = `${j.uid} — ${labels.minutes} ${j.minutes}`;
          unlock.hidden = false; unlock.disabled = false;
        } else if (['open','inactive','unknown'].includes(j.state)) {
          msg.textContent = `${j.uid || uid} — ${labels[j.state]}`;
        } else throw Error(labels.error);
      });
    });
    unlock.addEventListener('click', () => {
      const target = selected;
      if (!target || busy || !window.confirm(`${labels.confirm}\n${target.uid}`)) return;
      run(async () => {
        const j = await post('unlock',target);
        if (j.state !== 'unlocked') throw Error(labels.error);
        msg.textContent = `${target.uid} — ${labels.unlocked}`;
      });
    });
  }
  const scan = () => { const r=document.getElementById('xyg-admin'); if (r) init(r); };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',scan);
  else scan();
  document.addEventListener('focusin', e => { const r=e.target.closest?.('#xyg-admin'); if(r) init(r); });
})();
