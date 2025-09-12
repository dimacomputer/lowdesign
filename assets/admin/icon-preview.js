(function(){
  function mount(select){
    if(!select) return;
    const wrap = select.closest('.acf-input'); if(!wrap) return;
    let holder = wrap.querySelector('.icon-preview');
    if(!holder){ holder = document.createElement('span'); holder.className='icon-preview'; wrap.prepend(holder); }
    const val = select.value; // full id, e.g. "icon-ui-menu"
    holder.innerHTML = val ? `<svg aria-hidden="true"><use href="#${val}"></use></svg>` : '';
  }
  function init(){
    const qs = [
      '.acf-field select[name$="[menu_icon]"]',
      '.acf-field select[name$="[post_icon_name]"]',
      '.acf-field select[name$="[term_icon_name]"]'
    ].join(',');
    document.querySelectorAll(qs).forEach(s => { mount(s); s.addEventListener('change', ()=>mount(s)); });
  }
  if (document.readyState!=='loading') init(); else document.addEventListener('DOMContentLoaded', init);
})();
