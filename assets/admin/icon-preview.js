(function(){
  function mount(select){
    if(!select) return;
    const wrapper = select.closest('.acf-input');
    if(!wrapper) return;
    let holder = wrapper.querySelector('.icon-preview');
    if(!holder){
      holder = document.createElement('span');
      holder.className = 'icon-preview';
      wrapper.prepend(holder);
    }
    const val = select.value === 'none' ? '' : select.value;
    holder.innerHTML = val ? `<svg aria-hidden="true"><use href="#${val}"></use></svg>` : '';
    holder.title = val ? '' : 'No icon';
  }

  function init(){
    const query = [
      '.acf-field[data-name="menu_icon"] select',
      '.acf-field[data-name="post_icon_name"] select',
      '.acf-field[data-name="term_icon_name"] select'
    ].join(',');
    document.querySelectorAll(query).forEach(sel => {
      mount(sel);
      sel.addEventListener('change', () => mount(sel));
    });
  }

  if(document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);
})();

