/* hidden state for sub-blocks */
.ld-hidden{display:none!important}

/* base 24px token for icons and previews */
.icon{width:24px;height:24px;display:inline-block}
.icon--24{width:24px;height:24px}

/* select2 templates (left icon) */
.select2-container--default .ld-icon-sel,
.select2-container--default .ld-icon-opt{
  display:inline-flex;
  width:24px;height:24px;
  margin-right:6px;
  vertical-align:middle;
}
.select2-container--default .ld-icon-sel svg,
.select2-container--default .ld-icon-opt svg{
  width:100%;height:100%;
}

/* inline preview when media SVG is chosen */
.ld-media-inline svg{width:24px;height:24px;display:inline-block}

/* no thick outline jumps on hide/show */
.acf-fields [data-ld="icon-theme-wrap"],
.acf-fields [data-ld="icon-media-wrap"]{outline:0!important}

/* suppress focus halo on radios that get hidden */
.acf-field[data-name="content_icon_source"] input[type="radio"]:focus{
  outline:none!important;box-shadow:none!important
}
