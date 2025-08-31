<?php
if (!defined('ABSPATH')) exit;
$title = $args['title'] ?? __('Welcome', 'lowdesign-margo');
$subtitle = $args['subtitle'] ?? '';
?>
<section class="ld-hero py-5">
  <h1 class="display-5 mb-2"><?php echo esc_html($title); ?></h1>
  <?php if ($subtitle): ?>
    <p class="lead text-secondary"><?php echo esc_html($subtitle); ?></p>
  <?php endif; ?>
</section>