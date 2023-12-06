<?php
/**
 * Template Name: Recipe Dietary
 */
get_header();
$recipe_category_terms = get_terms( array(
    'taxonomy'   => 'recipe-dietary',
    'hide_empty' => true,
) );
?>
<div class="dr-page-template-wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="dr-recipe-post-wrap">
                <?php 
                    if ( ! is_wp_error( $recipe_category_terms ) && ! empty( $recipe_category_terms ) ) {
                        /**
                         * Get taxonomy terms search box.
                         */
                        delicious_recipes_get_template( 'pages/taxonomy/terms-box.php', [ 'terms' => $recipe_category_terms ] );

                        /**
                         * Get terms slider template
                         */
                        delicious_recipes_get_template( 'pages/taxonomy/terms-carousal.php', [ 'terms' => $recipe_category_terms ] );
                    } else {
                        esc_html_e( 'Terms not found for recipe dietary.', 'delicious-recipes' );
                    }
                ?>
            </div>
        </main>
    </div><!-- #primary -->
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();