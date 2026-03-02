<?php
/**
 * Title: LD Posts — Grid
 * Slug: lowdesign/posts-grid
 * Categories: query, lowdesign
 * Viewport Width: 1200
 * Description: 
 */
?>
<!-- wp:group {"className":"container py-5","layout":{"type":"default"}} -->
<div class="wp-block-group container py-5">
  <!-- wp:heading -->
  <h2>Latest posts</h2>
  <!-- /wp:heading -->

  <!-- wp:query {"query":{"perPage":6,"postType":"post","order":"desc","orderBy":"date"}} -->
  <div class="wp-block-query">
    <!-- wp:post-template {"className":"row g-4 mt-1"} -->
      <!-- wp:group {"className":"col-12 col-md-6 col-lg-4"} -->
      <div class="wp-block-group col-12 col-md-6 col-lg-4">
        <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","className":"mb-2 rounded"} /-->
        <!-- wp:post-title {"isLink":true,"className":"h5"} /-->
        <!-- wp:post-date {"className":"small opacity-75"} /-->
      </div>
      <!-- /wp:group -->
    <!-- /wp:post-template -->
  </div>
  <!-- /wp:query -->
</div>
<!-- /wp:group -->
