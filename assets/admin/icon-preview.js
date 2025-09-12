(function(){
  function init(){
    var names=['menu_icon','post_icon_name','term_icon_name'];
    names.forEach(function(name){
      document.querySelectorAll('.acf-field[data-name="'+name+'"]').forEach(function(field){
        var select=field.querySelector('select');
        if(!select)return;
        var preview=document.createElement('span');
        preview.className='ld-icon-preview';
        select.insertAdjacentElement('afterend',preview);
        function render(){
          var val=select.value;
          preview.innerHTML=val?'<svg class="icon"><use href="#'+val+'"></use></svg>':'';
        }
        select.addEventListener('change',render);
        render();
      });
    });
  }
  if(document.readyState!=='loading')init();
  else document.addEventListener('DOMContentLoaded',init);
})();
