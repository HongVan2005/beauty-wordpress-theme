(function(){
  const search=document.getElementById('pricing-search');
  const category=document.getElementById('pricing-category');
  const price=document.getElementById('pricing-price');
  const rows=Array.from(document.querySelectorAll('.pricing-row'));
  if(!rows.length)return;
  function filter(){
    const q=(search?search.value:'').trim().toLocaleLowerCase('vi');
    const c=category?category.value:'all'; const p=price?price.value:'all'; let shown=0;
    rows.forEach(row=>{
      const name=(row.dataset.name||'').toLocaleLowerCase('vi');
      const cats=(row.dataset.category||''); const amount=Number(row.dataset.price||0);
      const matchesName=!q||name.includes(q); const matchesCategory=c==='all'||cats.split(/\s+/).includes(c);
      let matchesPrice=true;
      if(p==='under300')matchesPrice=amount>0&&amount<300000;
      if(p==='300to500')matchesPrice=amount>=300000&&amount<=500000;
      if(p==='over500')matchesPrice=amount>500000;
      if(p==='contact')matchesPrice=amount===0;
      const visible=matchesName&&matchesCategory&&matchesPrice; row.classList.toggle('is-hidden',!visible); if(visible)shown++;
    });
    let empty=document.getElementById('pricing-no-results');
    if(!shown){if(!empty){empty=document.createElement('p');empty.id='pricing-no-results';empty.className='empty-state';empty.textContent='Không tìm thấy dịch vụ phù hợp. Hãy thử thay đổi bộ lọc.';document.querySelector('.pricing-table-wrap')?.after(empty);}}
    else if(empty)empty.remove();
  }
  [search,category,price].forEach(el=>{if(el){el.addEventListener('input',filter);el.addEventListener('change',filter);}});
})();
