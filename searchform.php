<form role="search" method="get" class="searchform" action="<?php echo esc_url(home_url('/')); ?>">
  <label class="sr-only" for="s">Search</label>
  <input type="search" id="s" class="input" placeholder="Search…" value="<?php echo get_search_query(); ?>" name="s" />
  <button type="submit" class="btn">Go</button>
</form>