<?php
/**
 * Title: LD Hero — Basic
 * Slug: lowdesign/hero-basic
 * Categories: featured, lowdesign
 * Viewport Width: 1200
 * Description: Hero with 2 CTAs and image.
 */
?>
<!-- wp:group {"className":"container py-5","layout":{"type":"default"}} -->
<div class="wp-block-group container py-5">
  <!-- wp:columns {"verticalAlignment":"center","className":"g-5"} -->
  <div class="wp-block-columns g-5 are-vertically-aligned-center">
    <!-- wp:column {"verticalAlignment":"center"} -->
    <div class="wp-block-column are-vertically-aligned-center">
      <!-- wp:heading {"level":1} -->
      <h1>Headline that explains value</h1>
      <!-- /wp:heading -->
      <!-- wp:paragraph {"fontSize":"medium"} -->
      <p>Short supporting text. Use semantic tokens for consistent contrast and spacing.</p>
      <!-- /wp:paragraph -->
      <!-- wp:buttons {"className":"mt-3"} -->
      <div class="wp-block-buttons mt-3">
        <!-- wp:button {"className":"is-style-ld-primary"} -->
        <div class="wp-block-button is-style-ld-primary"><a class="wp-block-button__link wp-element-button">Primary action</a></div>
        <!-- /wp:button -->
        <!-- wp:button {"className":"is-style-ld-outline"} -->
        <div class="wp-block-button is-style-ld-outline"><a class="wp-block-button__link wp-element-button">Secondary</a></div>
        <!-- /wp:button -->
      </div>
      <!-- /wp:buttons -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"verticalAlignment":"center"} -->
    <div class="wp-block-column are-vertically-aligned-center">
      <!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"rounded"} -->
      <figure class="wp-block-image size-large rounded"><img alt=""/></figure>
      <!-- /wp:image -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</div>
<!-- /wp:group -->
