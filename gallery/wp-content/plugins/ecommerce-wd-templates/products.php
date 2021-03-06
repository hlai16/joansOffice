<?php get_header(); ?>
<div id="primary">
  <div id="content" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <header class="entry-header"></header>
      <div class="entry-content">
        <?php wde_front_end(array('product_id' => get_the_ID(), 'type' => 'products', 'layout' => 'displayproduct'), TRUE); ?>
      </div>
    </article>

    <?php endwhile; ?>

  </div><!-- #content -->
  <?php
  if (locate_template('sidebar-content.php', TRUE, FALSE)) {
    get_sidebar('content');
  }
  ?>
</div><!-- #primary -->

<?php get_footer(); ?>