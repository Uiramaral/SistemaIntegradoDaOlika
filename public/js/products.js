(function(){
  const q = document.querySelector('[data-live-search]');
  if(!q) return;

  let t = null;
  q.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => {
      const url = new URL(window.location.href);
      const v = (q.value||'').trim();
      if(v) url.searchParams.set('q', v); else url.searchParams.delete('q');
      window.location.href = url.toString();
    }, 300);
  });
})();
