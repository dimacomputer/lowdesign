<?php if(!defined('ABSPATH')) exit;
add_action('admin_menu', function(){
  add_management_page('LowDesign Debug', 'LowDesign Debug', 'manage_options', 'ld-debug', function(){
    $cfg = function_exists('ld_icons_features') ? ld_icons_features() : [];
    $groups = function_exists('acf_get_field_group') ? [
      'group_post_icon' => acf_get_field_group('group_post_icon'),
      'group_term_icon' => acf_get_field_group('group_term_icon'),
      'group_menu_icon' => acf_get_field_group('group_menu_icon'),
    ] : [];
    echo '<div class="wrap"><h1>LowDesign Debug</h1>';
    echo '<h2>Flags</h2><pre>'.esc_html(print_r([
      'LD_ICONS_GATING' => defined('LD_ICONS_GATING')?LD_ICONS_GATING:null,
      'LD_ICONS_BYPASS' => defined('LD_ICONS_BYPASS')?LD_ICONS_BYPASS:null,
    ], true)).'</pre>';
    echo '<h2>ld_icons_features()</h2><pre>'.esc_html(print_r($cfg, true)).'</pre>';
    echo '<h2>ACF groups</h2><pre>'.esc_html(print_r(array_map(fn($g)=>$g?($g['key']??null):null,$groups), true)).'</pre>';
    echo '</div>';
  });
});

