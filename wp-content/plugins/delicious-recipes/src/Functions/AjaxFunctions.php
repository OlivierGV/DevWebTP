<?php
/**
 * AJAX Functions.
 *
 * @package DELICIOUS_RECIPES
 * @subpackage  DELICIOUS_RECIPES
 */

namespace WP_Delicious;

defined( 'ABSPATH' ) || exit;

/**
 * Global Settings.
 */
class AjaxFunctions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialization.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function init() {
		// Initialize hooks.
		$this->init_hooks();

		// Allow 3rd party to remove hooks.
		do_action( 'wfe_ajaxfunctions_unhook', $this );
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function init_hooks() {
		// Ajax for adding featured recipe meta
		add_action( 'wp_ajax_featured_recipe', array( $this, 'featured_recipe_admin_ajax' ) );

		// Ajax for Recipe Categories Widget
		add_action( 'wp_ajax_dr_recipe_taxonomy_terms', array( $this, 'dr_recipe_taxonomy_terms' ) );

		// Clone Existing Recipes
		add_action( 'wp_ajax_dr_clone_recipe_data', array( $this, 'dr_clone_recipe_data' ) );

		// Ajax for Recipe Search
		add_action( 'wp_ajax_recipe_search_results', array( $this, 'recipe_search_results' ) );
		add_action( 'wp_ajax_nopriv_recipe_search_results', array( $this, 'recipe_search_results' ) );

		// AJAX for Whats new page changelog query
		add_action( 'wp_ajax_dr_get_latest_changelog', array( $this, 'get_latest_changelog' ) );
	}

	/**
	 * Ajax for adding featured recipe meta
	 *
	 * */
	public function featured_recipe_admin_ajax() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wp_delicious_featured_recipe_nonce' ) ) {
			exit( 'invalid' );
		}

		header( 'Content-Type: application/json' );
		$post_id         = intval( $_POST['post_id'] );
		$featured_status = esc_attr( get_post_meta( $post_id, 'wp_delicious_featured_recipe', true ) );
		$new_status      = $featured_status == 'yes' ? 'no' : 'yes';
		update_post_meta( $post_id, 'wp_delicious_featured_recipe', $new_status );
		echo json_encode(
			array(
				'ID'         => $post_id,
				'new_status' => $new_status,
			)
		);
		die();
	}

	/**
	 * Ajax for Recipe Categories Widget
	 *
	 * */
	public function dr_recipe_taxonomy_terms() {

		$terms    = array();
		$taxonomy = isset( $_POST['taxonomy'] ) && ! empty( $_POST['taxonomy'] ) ? sanitize_title( $_POST['taxonomy'] ) : false;

		if ( $taxonomy ) {
			$terms = get_terms( array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			) );
		}

		wp_send_json_success( $terms );

	}

	/**
	 * Ajax callback function to clone recipe data.
	 *
	 */
	public function dr_clone_recipe_data() {

        // Nonce checks.
		check_ajax_referer( 'dr_clone_recipe_nonce', 'security' );

		if ( ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) ) {
			return;
		}

		$post_id   = absint( $_POST['post_id'] );
		$post_type = get_post_type( $post_id );

		if ( DELICIOUS_RECIPE_POST_TYPE !== $post_type ) {
			return;
		}
		$post = get_post( $post_id );

		$post_array = array(
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
			'post_status'  => 'draft',
			'post_type'    => DELICIOUS_RECIPE_POST_TYPE,
		);

		// Cloning old recipe.
		$new_post_id = wp_insert_post( $post_array );

		// Cloning old recipe meta.
		$all_old_meta = get_post_meta( $post_id );

		if ( is_array( $all_old_meta ) && count( $all_old_meta ) > 0 ) {
			foreach ( $all_old_meta as $meta_key => $meta_value_array ) {
				$meta_value = isset( $meta_value_array[0] ) ? $meta_value_array[0] : '';

				if ( '' !== $meta_value ) {
					$meta_value = maybe_unserialize( $meta_value );
				}
				update_post_meta( $new_post_id, $meta_key, $meta_value );
			}
		}

		// Cloning taxonomies
		$recipe_taxonomies = array( 'recipe-key', 'recipe-tag', 'recipe-cooking-method', 'recipe-cuisine', 'recipe-course' );
		foreach ( $recipe_taxonomies as $taxonomy ) {
			$recipe_terms      = get_the_terms( $post_id, $taxonomy );
			$recipe_term_names = array();
			if ( is_array( $recipe_terms ) && count( $recipe_terms ) > 0 ) {
				foreach ( $recipe_terms as $post_terms ) {
					$recipe_term_names[] = $post_terms->name;
				}
			}
			wp_set_object_terms( $new_post_id, $recipe_term_names, $taxonomy );
		}
		wp_send_json( array( 'true' ) );
	}

	/**
	 * Ajax for Recipe Search
	 *
	 * */
	public function recipe_search_results() {

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'dr-search-nonce' ) ) {
			exit( 'invalid' );
		}

		$options                = delicious_recipes_get_global_settings();
		$default_posts_per_page = isset ( $options['recipePerPage'] ) && ( ! empty( $options['recipePerPage'] ) ) ? $options['recipePerPage'] : get_option( 'posts_per_page' );
		$search_relation        = isset( $options['searchLogic'] ) ? $options['searchLogic'] : 'AND';

		$recipe_search_args = array(
			'post_type'        => DELICIOUS_RECIPE_POST_TYPE,
			'posts_per_page'   => absint( $default_posts_per_page ),
			'suppress_filters' => false,
			'post_status'      => 'publish'
		);

		$meta_query = array();

        if ( isset( $_REQUEST['search']['recipe_ingredients'] ) && ! empty( $_REQUEST['search']['recipe_ingredients'] ) && is_array( $_REQUEST['search']['recipe_ingredients'] ) ) {
			$recipe_ingredients = array_map( 'sanitize_text_field', $_REQUEST['search']['recipe_ingredients'] );
			foreach( $recipe_ingredients as $ingredient ) {
				array_push( $meta_query,
					array(
						'key' 		=> '_dr_recipe_ingredients',
						'value' 	=> sanitize_text_field( $ingredient ),
						'compare' 	=> 'LIKE',
					)
				);
			}
		}

		if( isset( $_REQUEST['search']['seasons'] ) && ! empty( $_REQUEST['search']['seasons'] ) ){
			$seasons = array_map( 'sanitize_text_field', $_REQUEST['search']['seasons'] );
			if ( is_array( $seasons ) ) {
				foreach ( $seasons as $season ) {
					array_push( $meta_query,
						array(
							'key' 		=> '_dr_best_season',
							'value' 	=> $season,
							'compare' 	=> '=',
						)
					);
				}
			} else {
				array_push( $meta_query,
					array(
						'key' 		=> '_dr_best_season',
						'value' 	=> $seasons,
						'compare' 	=> 'IN',
					)
				);
			}
		}

		if( isset( $_REQUEST['search']['difficulty_level'] ) && ! empty( $_REQUEST['search']['difficulty_level'] ) ){
			$difficulty_level = array_map( 'sanitize_text_field', $_REQUEST['search']['difficulty_level'] );
			if ( is_array( $difficulty_level ) ) {
				foreach ( $difficulty_level as $level ) {
					array_push( $meta_query,
						array(
							'key' 		=> '_dr_difficulty_level',
							'value' 	=> $level,
							'compare' 	=> '=',
						)
					);
				}
			} else {
				array_push( $meta_query,
					array(
						'key' 		=> '_dr_difficulty_level',
						'value' 	=> $difficulty_level,
						'compare' 	=> 'IN',
					)
				);
			}
		}

		if ( isset( $_REQUEST['search']['simple_factor'] ) && ! empty( $_REQUEST['search']['simple_factor'] ) ) {
			$simple_factor_array = is_array( $_REQUEST['search']['simple_factor'] ) ? array_map( 'sanitize_text_field', $_REQUEST['search']['simple_factor'] ) : array();
			foreach( $simple_factor_array as $key => $factor ) {
				switch( $factor ) {
					case '10-ingredients-or-less' :
						array_push( $meta_query,
							array(
								'key'     => '_dr_ingredient_count',
								'value'   => 10,
								'compare' => '<=',
							)
						);
					break;
					case '15-minutes-or-less' :
						array_push( $meta_query,
							array(
								'key'     => '_dr_recipe_total_time',
								'value'   => 15,
								'compare' => '<=',
							)
						);
					break;
					case '30-minutes-or-less' :
						array_push( $meta_query,
							array(
								'key'     => '_dr_recipe_total_time',
								'value'   => 30,
								'compare' => '<=',
							)
						);
					break;
					case '7-ingredients-or-less' :
						array_push( $meta_query,
							array(
								'key'     => '_dr_ingredient_count',
								'value'   => 7,
								'compare' => '<=',
							)
						);
					break;
				}
			}
		}

		if ( !empty( $meta_query ) ) {
			$recipe_search_args['meta_query'] = $meta_query;

			switch( $search_relation ) {
				case 'AND':
				default:
					$recipe_search_args['meta_query']['relation'] = 'AND';
					break;

				case 'OR':
					$recipe_search_args['meta_query']['relation'] = 'OR';
					break;
			}
		}

		$taxquery = array();

		if( ! empty( $_REQUEST["search"]["recipe_courses"] ) && $_REQUEST["search"]["recipe_courses"] != '-1'  ){
			$recipe_courses = is_array( $_REQUEST["search"]["recipe_courses"] ) ? array_map( 'absint', $_REQUEST["search"]["recipe_courses"] ) : array();
			foreach ( $recipe_courses as $course ) {
				array_push( $taxquery, array(
						'taxonomy' => 'recipe-course',
						'field'    => 'term_id',
						'terms'    => $course,
						'include_children' => false,
						'compare'  => '=',
					)
				);
			}
		}

		if( ! empty( $_REQUEST["search"]["recipe_cooking_methods"] ) && $_REQUEST["search"]["recipe_cooking_methods"] != '-1'  ){
			$recipe_cooking_methods = is_array( $_REQUEST["search"]["recipe_cooking_methods"] ) ? array_map( 'absint', $_REQUEST["search"]["recipe_cooking_methods"] ) : array();
			foreach ( $recipe_cooking_methods as $method ) {
				array_push( $taxquery, array(
						'taxonomy' => 'recipe-cooking-method',
						'field'    => 'term_id',
						'terms'    => $method,
						'include_children' => false,
						'compare'  => '=',
					));
			}
		}

		if( ! empty( $_REQUEST["search"]["recipe_cuisines"] ) && $_REQUEST["search"]["recipe_cuisines"] != '-1'  ){
			$recipe_cuisines = is_array( $_REQUEST["search"]["recipe_cuisines"] ) ? array_map( 'absint', $_REQUEST["search"]["recipe_cuisines"] ) : array();
			foreach( $recipe_cuisines as $cuisines ) {
				array_push( $taxquery, array(
						'taxonomy' => 'recipe-cuisine',
						'field'    => 'term_id',
						'terms'    => $cuisines,
						'include_children' => false,
						'compare'  => '=',
					));
			}
		}

		if( ! empty( $_REQUEST["search"]["recipe_keys"] ) && $_REQUEST["search"]["recipe_keys"] != '-1'  ){
			$recipe_keys = is_array( $_REQUEST["search"]["recipe_keys"] ) ? array_map( 'absint', $_REQUEST["search"]["recipe_keys"] ) : array();
			foreach ( $recipe_keys as $key ) {
				array_push( $taxquery, array(
						'taxonomy' => 'recipe-key',
						'field'    => 'term_id',
						'terms'    => $key,
						'include_children' => false,
						'compare'  => '=',
					));
			}
		}

		if( ! empty( $_REQUEST["search"]["recipe_tags"] ) && $_REQUEST["search"]["recipe_tags"] != '-1'  ){
			$recipe_tags = is_array( $_REQUEST["search"]["recipe_tags"] ) ? array_map( 'absint', $_REQUEST["search"]["recipe_tags"] ) : array();
			foreach ( $recipe_tags as $tag ) {
				array_push( $taxquery, array(
						'taxonomy' => 'recipe-tag',
						'field'    => 'term_id',
						'terms'    => $tag,
						'include_children' => false,
						'compare'  => '=',
					));
			}
		}

		if( ! empty( $_REQUEST["search"]["recipe_badges"] ) && $_REQUEST["search"]["recipe_badges"] != '-1'  ){
			$recipe_badges = is_array( $_REQUEST["search"]["recipe_badges"] ) ? array_map( 'absint', $_REQUEST["search"]["recipe_badges"] ) : array();
			foreach ( $recipe_badges as $badges ) {
				array_push( $taxquery, array(
						'taxonomy' => 'recipe-badge',
						'field'    => 'term_id',
						'terms'    => $badges,
						'include_children' => false,
						'compare'  => '=',
					));
			}
		}

		if( ! empty( $_REQUEST["search"]["recipe_dietary"] ) && $_REQUEST["search"]["recipe_dietary"] != '-1'  ){
			$recipe_dietary = is_array( $_REQUEST["search"]["recipe_dietary"] ) ? array_map( 'absint', $_REQUEST["search"]["recipe_dietary"] ) : array();
			foreach ( $recipe_dietary as $dietary ) {
				array_push( $taxquery, array(
						'taxonomy' => 'recipe-dietary',
						'field'    => 'term_id',
						'terms'    => $dietary,
						'include_children' => false,
						'compare'  => '=',
					));
			}
		}

		if( ! empty( $taxquery ) ) {
			$recipe_search_args['tax_query'] = $taxquery;

			switch( $search_relation ) {
				case 'AND':
				default:
					$recipe_search_args['tax_query']['relation'] = 'AND';
					break;

				case 'OR':
					$recipe_search_args['tax_query']['relation'] = 'OR';
					break;
			}
		}

		if ( 'OR' === $search_relation ) {
			$recipe_search_args['relation'] = 'OR';
		}

		if ( isset( $_REQUEST['search']['sorting']['0'] ) && ! empty( $_REQUEST['search']['sorting']['0'] ) ) {
			$sort = sanitize_title( $_REQUEST['search']['sorting']['0'] );
			switch( $sort ) {
				case 'title_asc' :
					$recipe_search_args['order'] = 'ASC';
					$recipe_search_args['orderby'] = 'title';
				break;
				case 'title_desc' :
					$recipe_search_args['order'] = 'DESC';
					$recipe_search_args['orderby'] = 'title';
				break;
				case 'date_desc' :
					$recipe_search_args['order'] = 'DESC';
					$recipe_search_args['orderby'] = 'date';
				break;
				case 'date_asc' :
					$recipe_search_args['order'] = 'ASC';
					$recipe_search_args['orderby'] = 'date';
				break;
			}
		}

		if ( isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ) {
			$recipe_search_args['paged'] = absint( $_REQUEST['paged'] );
		}

		$recipe_search = new \WP_Query( $recipe_search_args );

		$recipe_search_terms = array();
		$recipe_search_metas = array();
		// The counts is suitable only for "AND" operation.
		if ( 'AND' === $search_relation ) {
			$recipe_search_args['fields'] = 'ids';
			$recipe_search_args['posts_per_page'] = '-1';

			$recipe_search_ids = get_posts( $recipe_search_args );
			$recipe_search_terms = array(
				'recipe_courses' => $this->filter_terms_by_cpt( 'recipe-course', [ $recipe_search_args['post_type'] ], $recipe_search_ids),
				'recipe_cuisines' => $this->filter_terms_by_cpt( 'recipe-cuisine', [ $recipe_search_args['post_type'] ], $recipe_search_ids),
				'recipe_cooking_methods' => $this->filter_terms_by_cpt( 'recipe-cooking-method', [ $recipe_search_args['post_type'] ], $recipe_search_ids),
				'recipe_tags' => $this->filter_terms_by_cpt( 'recipe-tag', [ $recipe_search_args['post_type'] ], $recipe_search_ids),
				'recipe_keys' => $this->filter_terms_by_cpt( 'recipe-key', [ $recipe_search_args['post_type'] ], $recipe_search_ids),
				'recipe_badges' => $this->filter_terms_by_cpt( 'recipe-badge', [ $recipe_search_args['post_type'] ], $recipe_search_ids),
				'recipe_dietary' => $this->filter_terms_by_cpt( 'recipe-dietary', [ $recipe_search_args['post_type'] ], $recipe_search_ids),
			);

			if ( empty( $recipe_search_ids ) ) {
				$recipe_search_ids = [0];
			}

			$recipe_search_metas = array(
				'seasons' => array(
					'fall' => $this->filter_by_meta_data( '_dr_best_season', 'fall', $recipe_search_ids ),
					'winter' => $this->filter_by_meta_data( '_dr_best_season', 'winter', $recipe_search_ids ),
					'summer' => $this->filter_by_meta_data( '_dr_best_season', 'summer', $recipe_search_ids ),
					'spring' => $this->filter_by_meta_data( '_dr_best_season', 'spring', $recipe_search_ids ),
					'available' => $this->filter_by_meta_data( '_dr_best_season', 'available', $recipe_search_ids )
				),
				'difficulty_level' => array(
					'beginner' => $this->filter_by_meta_data( '_dr_difficulty_level', 'beginner', $recipe_search_ids ),
					'intermediate' => $this->filter_by_meta_data( '_dr_difficulty_level', 'intermediate', $recipe_search_ids ),
					'advanced' => $this->filter_by_meta_data( '_dr_difficulty_level', 'intermediate', $recipe_search_ids )
				),
				'simple_factor' => array(
					'10-ingredients-or-less' => $this->filter_by_simple_factor( '10-ingredients-or-less', '', $recipe_search_ids ),
					'15-minutes-or-less' => $this->filter_by_simple_factor( '15-minutes-or-less', '', $recipe_search_ids ),
					'30-minutes-or-less' => $this->filter_by_simple_factor( '30-minutes-or-less', '', $recipe_search_ids ),
					'7-ingredients-or-less' => $this->filter_by_simple_factor( '7-ingredients-or-less', '', $recipe_search_ids )
				),
				'recipe_ingredients' => $this->filter_by_ingredients( $recipe_search_ids )
			);
		}

		$results = array();

		while ( $recipe_search->have_posts() ) {

			$recipe_search->the_post();

			$recipe = get_post( get_the_ID() );
			$recipe_metas = \delicious_recipes_get_recipe( $recipe );

			// Get global toggles.
			$global_toggles = delicious_recipes_get_global_toggles_and_labels();

			$img_size = $global_toggles['enable_recipe_archive_image_crop'] ? 'recipe-archive-grid' : 'full';
			$thumbnail_id = has_post_thumbnail( $recipe_metas->ID ) ? get_post_thumbnail_id( $recipe_metas->ID ) : '';
			$thumbnail    = $thumbnail_id ? get_the_post_thumbnail( $recipe_metas->ID, $img_size ) : '';

			$recipe_keys = array();

			if ( ! empty( $recipe_metas->recipe_keys ) ) {
				foreach( $recipe_metas->recipe_keys as $recipe_key ) {
					$key  = get_term_by( 'name', $recipe_key, 'recipe-key' );
					$link = get_term_link( $key, 'recipe-key' );
					$icon = delicious_recipes_get_tax_icon( $key, true );
					$recipe_keys[] = array(
						'key'  => $recipe_key,
						'link' => $link,
						'icon' => $icon
					);
				}
			}

			$recipe_badges = false;

			if ( ! empty( $recipe_metas->badges ) ) {
				$badge       = get_term_by( 'name', $recipe_metas->badges[0], 'recipe-badge' );
				$link        = get_term_link( $badge, 'recipe-badge' );
				$badge_metas = get_term_meta( $badge->term_id, 'dr_taxonomy_metas', true );
				$tax_color   = isset( $badge_metas['taxonomy_color'] )  && ! empty( $badge_metas['taxonomy_color'] ) ? $badge_metas['taxonomy_color'] : '#E84E3B';

				$recipe_badges = array(
					'badge' => $recipe_metas->badges[0],
					'link'  => $link,
					'color' => $tax_color
				);
			}

			$results[] = array(
				'recipe_id'        => $recipe_metas->ID,
				'title'            => $recipe_metas->name,
				'permalink'        => $recipe_metas->permalink,
				'thumbnail_id'     => $recipe_metas->thumbnail_id,
				'thumbnail_url'    => $recipe_metas->thumbnail,
				'thumbnail'        => $thumbnail,
				'recipe_keys'      => $recipe_keys,
				'total_time'       => $recipe_metas->total_time,
				'difficulty_level' => $recipe_metas->difficulty_level,
				'recipe_calories'  => $recipe_metas->recipe_calories,
				'enable_pinit'     => delicious_recipes_enable_pinit_btn(),
				'badges'           => $recipe_badges,
			);

		}
			$pagination = false;
			/**
			 * Get Pagination.
			 */
			$total_pages = $recipe_search->max_num_pages;
			$big         = 999999999;                   // need an unlikely integer
			$paged       = isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;

			if ( $total_pages > 1 ){
				$current_page = max( 1, $paged );

				$pagination = paginate_links(array(
					'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'    => '?paged=%#%',
					'current'   => $current_page,
					'total'     => absint( $total_pages ),
					'prev_text' => __( 'Prev', 'delicious-recipes' ) .
					'<svg xmlns="http://www.w3.org/2000/svg" width="18.479" height="12.689" viewBox="0 0 18.479 12.689">
						<g transform="translate(17.729 11.628) rotate(180)">
							<path d="M7820.11-1126.021l5.284,5.284-5.284,5.284" transform="translate(-7808.726 1126.021)" fill="none"
								stroke="#374757" stroke-linecap="round" stroke-width="1.5" />
							<path d="M6558.865-354.415H6542.66" transform="translate(-6542.66 359.699)" fill="none" stroke="#374757"
								stroke-linecap="round" stroke-width="1.5" />
						</g>
					</svg>',
					'next_text' => __( "Next", 'delicious-recipes' ) .
					'<svg xmlns="http://www.w3.org/2000/svg" width="18.479" height="12.689" viewBox="0 0 18.479 12.689"><g transform="translate(0.75 1.061)">
							<path d="M7820.11-1126.021l5.284,5.284-5.284,5.284" transform="translate(-7808.726 1126.021)" fill="none"
								stroke="#374757" stroke-linecap="round" stroke-width="1.5" />
							<path d="M6558.865-354.415H6542.66" transform="translate(-6542.66 359.699)" fill="none" stroke="#374757"
								stroke-linecap="round" stroke-width="1.5" />
						</g>
					</svg>',
				));
			}
		// Reset postdata.
		wp_reset_postdata();

		wp_send_json_success( [ 'results' => $results, 'pagination' => $pagination, 'terms' => $recipe_search_terms, 'metas' => $recipe_search_metas, 'logic' => $search_relation ] );
		die();

	}

	/**
	 * Get Latest Changelog
	 *
	 * @return void
	 */
	public function get_latest_changelog() {
		$changelog = null;
		$access_type = get_filesystem_method();

		if ($access_type === 'direct') {
			$creds = request_filesystem_credentials(
				site_url() . '/wp-admin/',
				'', false, false,
				[]
			);

			if (WP_Filesystem($creds)) {
				global $wp_filesystem;

				$changelog = $wp_filesystem->get_contents(
					plugin_dir_path( DELICIOUS_RECIPES_PLUGIN_FILE ) . '/changelog.txt'
				);
			}
		}

		wp_send_json_success([
			'changelog' => apply_filters(
				'delicious_recipes_changelogs_list',
				[
					[
						'title'     => __('Plugin', 'delicious-recipes'),
						'changelog' => $changelog,
					]
				]
			)
		]);
	}

	private function filter_terms_by_cpt($taxonomy, $post_types  =array(), $post_ids = array() ){
		global $wpdb;

		$post_types=(array) $post_types;
		$key = 'wpse_terms'.md5($taxonomy.serialize($post_types));
		$results = wp_cache_get($key);

		if ( empty( $post_ids ) ) {
			$post_ids = [0];
		}

		if ( false === $results ) {
			$where =" WHERE 1=1";
			if( !empty($post_types) ){
				$post_types_str = implode(',',$post_types);
				$where.= $wpdb->prepare(" AND p.post_type IN(%s)", $post_types_str);
			}

			$where .= $wpdb->prepare(" AND tt.taxonomy = %s",$taxonomy);

			$_post_ids = implode(', ', $post_ids );
			$query = "
				SELECT t.*, COUNT(*) as count
				FROM $wpdb->terms AS t
				INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
				INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id
				$where
				AND (p.ID in ($_post_ids))
				GROUP BY t.term_id";

			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results );
		}

		return $results;
	}

	private function filter_by_meta_data( $key, $value = '', $post_ids = array() ) {
		$args = array(
			'post_type'        => DELICIOUS_RECIPE_POST_TYPE,
			'posts_per_page'   => -1,
			'suppress_filters' => false,
			'post_status'      => 'publish',
			'post__in'         => empty( $post_ids ) ? 0 : $post_ids,
			'meta_key'         => $key,
			'meta_value'       => $value,
			'meta_compare'     => '=',
			'fields'           => 'ids'
		);

		return count( get_posts( $args ) );
	}

	private function filter_by_simple_factor( $key, $value = '', $post_ids = array() ) {
		$args = array(
			'post_type'        => DELICIOUS_RECIPE_POST_TYPE,
			'posts_per_page'   => -1,
			'suppress_filters' => false,
			'post_status'      => 'publish',
			'post__in'         => empty( $post_ids ) ? 0 : $post_ids,
			'fields'           => 'ids'
		);

		switch ( $key ) {
			case '10-ingredients-or-less':
				$args['meta_key'] = '_dr_ingredient_count';
				$args['meta_value_num'] = 10;
				$args['meta_compare'] = '<=';
				break;

			case '15-minutes-or-less':
				$args['meta_key'] = '_dr_recipe_total_time';
				$args['meta_value_num'] = 15;
				$args['meta_compare'] = '<=';
				break;

			case '30-minutes-or-less':
				$args['meta_key'] = '_dr_recipe_total_time';
				$args['meta_value_num'] = 30;
				$args['meta_compare'] = '<=';
				break;

			case '7-ingredients-or-less':
				$args['meta_key'] = '_dr_ingredient_count';
				$args['meta_value_num'] = 7;
				$args['meta_compare'] = '<=';
				break;

			default:
				break;
		}

		return count( get_posts( $args ) );
	}

	private function filter_by_ingredients( $post_ids = array() ) {
		$args = array(
			'post_type'        => DELICIOUS_RECIPE_POST_TYPE,
			'posts_per_page'   => -1,
			'suppress_filters' => false,
			'post_status'      => 'publish',
			'post__in'         => empty( $post_ids ) ? 0 : $post_ids,
			'fields'           => 'ids'
		);

		$recipes           = get_posts( $args );
		$ingredients_array = array();

		foreach ( $recipes as $recipe ) {
			$recipe_meta        = get_post_meta( $recipe, 'delicious_recipes_metadata', true );
			$recipe_ingredients = isset( $recipe_meta['recipeIngredients'] ) && $recipe_meta['recipeIngredients'] ? $recipe_meta['recipeIngredients'] : '';
			$ingres_per_recipe  = array();

			if ( isset( $recipe_ingredients ) && ! empty( $recipe_ingredients ) ) {
				foreach ( $recipe_ingredients as $recipe_ingredient ) {
					if ( isset( $recipe_ingredient['ingredients'] ) && ! empty( $recipe_ingredient['ingredients'] ) ) {
						foreach ( $recipe_ingredient['ingredients'] as $ingredients ) {

							$ingredient = strip_tags( preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $ingredients['ingredient'] ) );
							if ( ! in_array( $ingredient, array_values( $ingres_per_recipe ) ) ) {
								$ingres_per_recipe[] = ucfirst( $ingredient );
								$ingredients_array[] = ucfirst( $ingredient );
							}
						}
					}
				}
			}
		}

		return array_count_values( $ingredients_array );
	}
}

new AjaxFunctions();
