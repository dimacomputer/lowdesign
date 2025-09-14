(function(){
  function svgUse(id, cls){
    if(!id||id==='none'||id==='__custom__') return '';
    return '<span class="'+cls+'"><svg aria-hidden="true"><use href="#'+id+'"></use></svg></span>';
  }
  function tplResult(s){
    if(!s.id||s.id==='__custom__'||s.id==='none') return s.text;
    return svgUse(s.id,'ld-icon-opt')+'<span>'+s.text+'</span>';
  }
  function tplSelection(s){
    if(!s.id||s.id==='__custom__'||s.id==='none') return s.text||'';
    return svgUse(s.id,'ld-icon-sel')+'<span>'+s.text+'</span>';
  }

  function findUploadField($root){
    let $img=$root.closest('.acf-fields, .acf-row, body').find('.acf-field[data-name="content_icon_media"]');
    if($img.length) return $img;
    $img=$root.closest('.acf-fields, .acf-row, body').find('.acf-field[data-name="term_icon_media"]');
    return $img.length?$img:null;
  }

  function buildGroupsFromOptions($sel){
    const glyph=[], brand=[];
    $sel.find('option').each(function(){
      const id=this.value||''; const text=this.textContent||id; if(!id) return;
      if(id.startsWith('glyph/')) glyph.push({id,text});
      else if(id.startsWith('brand/')) brand.push({id,text});
    });
    return {glyph,brand};
  }

  function enhance(raw){
    if(typeof jQuery==='undefined'||!jQuery.fn.select2) return;
    const $sel=jQuery(raw), $upload=findUploadField($sel);
    const data=[{id:'none',text:'— No icon —'}];
    if($upload && $sel.closest('.acf-field').data('name')!=='menu_icon'){
      data.push({id:'__custom__',text:'Custom Icon (upload)'});
    }
    const g=buildGroupsFromOptions($sel);
    if(g.glyph.length) data.push({text:'Glyph',children:g.glyph});
    if(g.brand.length) data.push({text:'Brand',children:g.brand});
    $sel.empty();
    $sel.select2({
      width:'100%',data,allowClear:true,placeholder:'— No icon —',
      templateResult:tplResult,templateSelection:tplSelection,escapeMarkup:m=>m
    });
    if(!$sel.val()) $sel.val('none').trigger('change');
    $sel.on('select2:select',e=>{
      const val=e.params&&e.params.data&&e.params.data.id;
      if(val==='__custom__' && $upload){
        const btn=$upload.find('button, .acf-button').get(0);
        $upload.get(0).scrollIntoView({behavior:'smooth',block:'center'});
        if(btn) btn.focus();
      }
    });
  }

  function init(){
    const q=[
      '.acf-field[data-name="menu_icon"] select',
      '.acf-field[data-name="post_icon_name"] select',
      '.acf-field[data-name="term_icon_name"] select'
    ].join(',');
    if(window.acf && typeof acf.add_action==='function'){
      acf.add_action('ready',()=>{ jQuery(q).each(function(){ enhance(this); }); });
      acf.add_action('append',($el)=>{ jQuery($el).find(q).each(function(){ enhance(this); }); });
    }else if(typeof jQuery!=='undefined'){
      jQuery(()=>{ jQuery(q).each(function(){ enhance(this); }); });
    }
  }
  if(document.readyState!=='loading') init(); else document.addEventListener('DOMContentLoaded', init);
})();
