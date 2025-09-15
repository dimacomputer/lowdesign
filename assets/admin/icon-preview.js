(function(){
  function $all(s, r=document){ return Array.from(r.querySelectorAll(s)); }
  function toggleBlock(block, show){
    if(!block) return;
    const inputs=$all('input, select, textarea, button, [tabindex]', block);
    if(show){
      block.classList.remove('ld-hidden');
      block.removeAttribute('aria-hidden');
      inputs.forEach(el=>{
        el.disabled=false;
        if(el.hasAttribute('data-tabindex-prev')){
          el.tabIndex=+el.getAttribute('data-tabindex-prev');
          el.removeAttribute('data-tabindex-prev');
        }
      });
    }else{
      $all('.select2-container--focus', block).forEach(n=>n.classList.remove('select2-container--focus'));
      if(block.contains(document.activeElement)){
        try{ document.activeElement.blur(); }catch(e){}
      }
      block.classList.add('ld-hidden');
      block.setAttribute('aria-hidden','true');
      inputs.forEach(el=>{
        if(!el.hasAttribute('data-tabindex-prev')) el.setAttribute('data-tabindex-prev', el.tabIndex || 0);
        el.tabIndex=-1;
        el.disabled=true;
      });
    }
  }
  window.toggleBlock = toggleBlock;
  function onChange(radio){
    const root=radio.closest('.acf-fields, .acf-row, body');
    if(!root) return;
    const themeWrap=root.querySelector('[data-ld="icon-theme-wrap"]');
    const mediaWrap=root.querySelector('[data-ld="icon-media-wrap"]');
    const val=radio.value;
    if(val==='none'){ toggleBlock(themeWrap,false); toggleBlock(mediaWrap,false); return; }
    if(val==='sprite'){ toggleBlock(themeWrap,true); toggleBlock(mediaWrap,false); return; }
    if(val==='media'){ toggleBlock(themeWrap,false); toggleBlock(mediaWrap,true); return; }
  }
  function svgUse(id, cls){
    if(!id) return '';
    return '<span class="'+cls+'"><svg class="icon icon--24" aria-hidden="true"><use href="#'+id+'"></use></svg></span>';
  }
  function tplResult(s){
    if(!s.id) return s.text;
    return svgUse(s.id,'ld-icon-opt')+'<span>'+s.text+'</span>';
  }
  function tplSelection(s){
    if(!s.id) return s.text||'';
    return svgUse(s.id,'ld-icon-sel')+'<span>'+s.text+'</span>';
  }

  function findSourceRadio($field){
    const $rad=$field.closest('.acf-fields, .acf-row, body')
      .find('.acf-field[data-name="content_icon_source"] input[type="radio"]');
    return $rad.length?$rad:null;
  }

  function buildGroupsFromOptions($sel){
    const glyph=[], brand=[];
    $sel.find('option').each(function(){
      const id=this.value||''; const text=this.textContent||id; if(!id) return;
      if(id.startsWith('glyph-')) glyph.push({id,text});
      else if(id.startsWith('brand-')) brand.push({id,text});
    });
    return {glyph,brand};
  }

  function enhance(raw){
    if(typeof jQuery==='undefined'||!jQuery.fn.select2) return;
    const $sel=jQuery(raw);
    const data=[];
    const g=buildGroupsFromOptions($sel);
    if(g.glyph.length) data.push({text:'Glyph',children:g.glyph});
    if(g.brand.length) data.push({text:'Brand',children:g.brand});
    $sel.empty();
    $sel.select2({
      width:'100%',data,allowClear:true,placeholder:'— Select icon —',
      templateResult:tplResult,templateSelection:tplSelection,escapeMarkup:m=>m
    });
    initPreview($sel);
  }

  function initPreview($sel){
    const $field=$sel.closest('.acf-field');
    const $rad=findSourceRadio($field);
    let $prev=null;
    function render(){
      const src=$rad&&$rad.filter(':checked').val();
      const id=$sel.val();
      if((!$rad||src==='sprite') && id){
        if(!$prev) $prev=jQuery('<span class="icon-preview"></span>').insertAfter($sel);
        $prev.html('<svg class="icon icon--24" aria-hidden="true"><use href="#'+id+'"></use></svg>');
      }else if($prev){
        $prev.remove();
        $prev=null;
      }
    }
    if($rad) $rad.on('change',render);
    $sel.on('change',render);
    render();
  }

  function init(){
    $all('.acf-field[data-name="post_icon_name"], .acf-field[data-name="term_icon_name"]').forEach(n=>{
      if(!n.hasAttribute('data-ld')) n.setAttribute('data-ld','icon-theme-wrap');
    });
    $all('.acf-field[data-name="content_icon_media"], .acf-field[data-name="term_icon_media"]').forEach(n=>{
      if(!n.hasAttribute('data-ld')) n.setAttribute('data-ld','icon-media-wrap');
    });
    const q = [
      '.acf-field[data-name="menu_icon"] select',
      '.acf-field[data-name="post_icon_name"] select',
      '.acf-field[data-name="term_icon_name"] select'
    ].join(',');
    const mq = '.acf-field[data-name="content_icon_media"], .acf-field[data-name="term_icon_media"]';
    function initMedia(field){
      const $field=jQuery(field);
      const $upl=$field.find('.acf-image-uploader');
      let $prev=null;
      function render(){
        const $img=$upl.find('.image-wrap img, .image-wrap svg').first();
        if($img.length){
          if(!$prev) $prev=jQuery('<span class="icon-preview"></span>').insertAfter($upl);
          const $clone=$img.clone().addClass('icon icon--24');
          $clone.removeAttr('width').removeAttr('height');
          $prev.html($clone);
        }else if($prev){
          $prev.remove();
          $prev=null;
        }
      }
      render();
      $field.on('change', 'input[type="hidden"]', render);
    }
    if(window.acf && typeof acf.add_action==='function'){
      acf.add_action('ready',()=>{
        jQuery(q).each(function(){ enhance(this); });
        jQuery(mq).each(function(){ initMedia(this); });
      });
      acf.add_action('append',($el)=>{
        jQuery($el).find(q).each(function(){ enhance(this); });
        jQuery($el).find(mq).each(function(){ initMedia(this); });
      });
    }else if(typeof jQuery!=='undefined'){
      jQuery(()=>{
        jQuery(q).each(function(){ enhance(this); });
        jQuery(mq).each(function(){ initMedia(this); });
      });
    }
    document.addEventListener('change',function(ev){
      const radio = ev.target;
      if(radio && radio.matches('.acf-field[data-name="content_icon_source"] input[type="radio"]')){
        onChange(radio);
        setTimeout(()=>{
          const el=document.activeElement;
          if(el && el.closest('.ld-hidden')){ try{ el.blur(); }catch(e){} }
        },0);
      }
    });
  }
  if(document.readyState!=='loading') init(); else document.addEventListener('DOMContentLoaded', init);
})();

;(function(){
  function $all(s,r=document){return Array.from(r.querySelectorAll(s));}
  function boot(){
    $all('.acf-field[data-name="content_icon_source"]').forEach(field=>{
      const radios=$all('input[type="radio"]',field);
      const checked=radios.find(r=>r.checked);
      if(!checked) return;
      const root=field.closest('.acf-fields, .acf-row, body');
      const themeWrap=root.querySelector('[data-ld="icon-theme-wrap"]');
      const mediaWrap=root.querySelector('[data-ld="icon-media-wrap"]');
      const val=checked.value;
      const toggle=(b,s)=>{ if(typeof window.toggleBlock==='function') window.toggleBlock(b,s); };
      if(val==='none'){ toggle(themeWrap,false); toggle(mediaWrap,false); }
      if(val==='sprite'){ toggle(themeWrap,true); toggle(mediaWrap,false); }
      if(val==='media'){ toggle(themeWrap,false); toggle(mediaWrap,true); }
    });
    if(document.activeElement && document.activeElement.closest('.ld-hidden')){
      try{ document.activeElement.blur(); }catch(e){}
    }
  }
  if(document.readyState!=='loading') boot(); else document.addEventListener('DOMContentLoaded', boot);
})();
