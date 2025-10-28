document.addEventListener('click', async (e)=>{
  if(e.target.matches('form button') && e.target.closest('form[action*="/items/"][method="post"]') && e.target.closest('form').querySelector('input[name="_method"][value="DELETE"]')){
    e.preventDefault();
    const form = e.target.closest('form');
    const res = await fetch(form.action,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:new FormData(form)});
    if(res.ok) location.reload();
  }
});
