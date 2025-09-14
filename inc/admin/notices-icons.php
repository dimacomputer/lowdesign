<?php if(!defined('ABSPATH')) exit;
add_action('admin_notices', function(){
  if (defined('LD_ICONS_GATING') && !LD_ICONS_GATING) {
    echo '<div class="notice notice-info is-dismissible"><p>Icon gating is <strong>OFF</strong>. All icon UI is visible. Toggle it by setting <code>LD_ICONS_GATING</code> to <code>true</code> in <em>inc/bootstrap.php</em>.</p></div>';
  }
});
