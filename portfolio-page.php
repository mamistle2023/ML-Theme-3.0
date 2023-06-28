<?php
/**
 * The template for displaying portfolio posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ML_Theme_3.0
 */

get_header(); ?>

<main id="primary" class="site-main">
    <?php
    // Define the WP Query for Portfolio post type
    $args = array( 'post_type' => 'Portfolio', 'posts_per_page' => -1 );
    $loop = new WP_Query( $args );

    while ( $loop->have_posts() ) : $loop->the_post();
        the_content();
    endwhile;
    ?>
</main><!-- #main -->

<?php
get_footer();
