(function(){function m(s){if(!s)return;const w=s.closest('.acf-input');if(!w)return;
  let h=w.querySelector('.icon-preview');if(!h){h=document.createElement('span');h.className='icon-preview';w.prepend(h);}
  const v=s.value;h.innerHTML=v?`<svg aria-hidden="true"><use href="#${v}"></use></svg>`:'';}
  function i(){const q=[
  '.acf-field[data-name="menu_icon"] select',
  '.acf-field[data-name="post_icon_name"] select',
  '.acf-field[data-name="term_icon_name"] select'].join(',');
  document.querySelectorAll(q).forEach(s=>{m(s);s.addEventListener('change',()=>m(s));});}
  if(document.readyState!=='loading') i(); else document.addEventListener('DOMContentLoaded', i);})();
