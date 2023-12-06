<?php
/**
 * Filter by Difficulty Levels.
 */
$difficulty_levels = array(
	'beginner'     => __( 'Beginner', 'delicious-recipes' ),
	'intermediate' => __( 'Intermediate', 'delicious-recipes' ),
	'advanced'     => __( 'Advanced', 'delicious-recipes' ),
);
$show_count        = apply_filters( 'delicious_recipes_search_filters_show_count', true );

$args = array(
	'post_type'        => DELICIOUS_RECIPE_POST_TYPE,
	'posts_per_page'   => -1,
	'suppress_filters' => false,
	'post_status'      => 'publish',
	'fields'           => 'ids',
);
?>
<select class="js-select2" multiple="multiple"  name='difficulty_level'>
	<?php foreach ( $difficulty_levels as $key => $value ) : ?>
		<option data-title="<?php echo esc_attr( $value ); ?>" value="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( sanitize_title( $value ) ); ?>" name='difficulty_level'>
			<?php
			echo esc_html( $value );
			if ( $show_count ) :
				$args['meta_query'] = array(
					array(
						'key'     => '_dr_difficulty_level',
						'value'   => $key,
						'compare' => 'LIKE',
					),
				);
				$results            = get_posts( $args );
				$count              = count( $results );
				?>
					<span class='count'>(<?php echo esc_html( $count ); ?>)</span>
				<?php
				endif;
			?>
		</option>
	<?php endforeach; ?>
</select>
<?php
