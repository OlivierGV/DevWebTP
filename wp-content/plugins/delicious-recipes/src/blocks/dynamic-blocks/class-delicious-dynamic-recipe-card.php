<?php
/**
 * Dynamic Recipe Card Block
 *
 * @since   1.0.8
 * @package Delicious_Recipes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Delicious_Dynamic_Recipe_Card Class.
 */
class Delicious_Dynamic_Recipe_Card {
	/**
	 * The post Object.
	 *
	 * @since 1.0.8
	 */
	private static $recipe;

	/**
	 * Class instance Structured Data Helpers.
	 *
	 * @var Delicious_Recipes_Structured_Data_Helpers
	 * @since 1.0.8
	 */
	public static $structured_data_helpers;

	/**
	 * Class instance Helpers.
	 *
	 * @var Delicious_Recipes_Helpers
	 * @since 1.0.8
	 */
	public static $helpers;

	/**
	 * Recipe Block ID.
	 *
	 * @since 1.2.0
	 */
	public static $recipeBlockID;

	/**
	 * Block attributes.
	 *
	 * @since 1.0.8
	 */
	public static $attributes;

	/**
	 * Block settings.
	 *
	 * @since 1.0.8
	 */
	public static $settings;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		self::$structured_data_helpers = new Delicious_Recipes_Structured_Data_Helpers();
		self::$helpers = new Delicious_Recipes_Helpers();
	}

	/**
	 * Registers the recipe-card block as a server-side rendered block.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( delicious_recipes_block_is_registered( 'delicious-recipes/dynamic-recipe-card' ) ) {
			return;
		}

		$attributes = array(
            'id' => array(
                'type'    => 'string',
                'default' => 'dr-dynamic-recipe-card'
            ),
            'image' => array(
                'type' => 'object',
            ),
            'hasImage' => array(
                'type'    => 'boolean',
                'default' => false
            ),
            'video' => array(
                'type' => 'object',
            ),
            'hasVideo' => array(
                'type'    => 'boolean',
                'default' => false
            ),
            'videoTitle' => array(
                'type'     => 'string',
                'selector' => '.video-title',
                'default'  => 'Video',
            ),
            'hasInstance' => array(
                'type'    => 'boolean',
                'default' => false
            ),
            'recipeTitle' => array(
                'type'     => 'string',
                'selector' => '.recipe-card-title',
            ),
            'summary' => array(
                'type'     => 'string',
                'selector' => '.recipe-card-summary',
                'default'  => ''
			),
			'summaryTitle' => array(
                'type'     => 'string',
                'selector' => '.summary-title',
                'default'  => 'Description',
            ),
            'jsonSummary' => array(
                'type' => 'string',
            ),
            'course' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'string'
                )
            ),
            'cuisine' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'string'
                )
			),
			'method' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'string'
                )
			),
			'recipeKey' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'string'
                )
			),
            'difficulty' => array(
				'type'    => 'string',
				'default' => 'beginner'
			),
			'difficultyTitle' => array(
				'type' => 'string',
				'selector' => '.difficulty-label',
				'default' => __("Difficulty", "delicious-recipes"),
			),
			'season' => array(
				'type'    => 'string',
				'default' => 'summer'
			),
			'seasonTitle' => array(
				'type' => 'string',
				'selector' => '.season-label',
				'default' => __("Best Season", "delicious-recipes"),
			),
            'keywords' => array(
                'type'  => 'array',
                'items' => array(
                    'type' => 'string'
                )
            ),
            'settings' => array(
                'type'    => 'array',
                'default' => array(
                    array(
                        'print_btn'            => true,
                        'pin_btn'              => true,
						'custom_author_name'   => '',
                        'displayCourse'        => true,
                        'displayCuisine'       => true,
                        'displayCookingMethod' => true,
                        'displayRecipeKey'     => true,
                        'displayDifficulty'    => true,
                        'displayAuthor'        => true,
                        'displayServings'      => true,
                        'displayPrepTime'      => true,
                        'displayCookingTime'   => true,
                        'displayRestTime'      => true,
                        'displayTotalTime'     => true,
                        'displayCalories'      => true,
                        'displayBestSeason'    => true,
                    )
                ),
                'items' => array(
                    'type' => 'object'
                )
			),
			'details' => array(
				'type'    => 'array',
				'default' => self::get_details_default(),
				'items'   => array(
					'type' => 'object'
				)
			),
            'ingredientsTitle' => array(
                'type'     => 'string',
                'selector' => '.ingredients-title',
                'default'  => 'Ingredients',
            ),
            'jsonIngredientsTitle' => array(
                'type' => 'string',
            ),
            'ingredients' => array(
                'type'    => 'array',
                'default' => self::get_ingredients_default(),
                'items'   => array(
                    'type' => 'object'
                )
            ),
            'directionsTitle' => array(
                'type'     => 'string',
                'selector' => '.directions-title',
                'default'  => 'Instructions',
            ),
            'jsonDirectionsTitle' => array(
                'type' => 'string',
            ),
            'steps' => array(
                'type'    => 'array',
                'default' => self::get_steps_default(),
                'items'   => array(
                    'type' => 'object'
                )
            ),
            'notesTitle' => array(
                'type'     => 'string',
                'selector' => '.notes-title',
                'default'  => 'Notes',
            ),
            'notes' => array(
                'type'     => 'string',
                'selector' => '.recipe-card-notes-list',
                'default'  => ''
            )
        );

		// Hook server side rendering into render callback
		register_block_type(
			'delicious-recipes/dynamic-recipe-card', array(
				'attributes'      => $attributes,
				'render_callback' => array( $this, 'render' ),
		) );
	}

	/**
	 * Renders the block.
	 *
	 * @param array  $attributes The attributes of the block.
	 * @param string $content    The HTML content of the block.
	 *
	 * @return string The block preceded by its JSON-LD script.
	 */
	public function render( $attributes, $content ) {

		if ( ! is_array( $attributes ) ) {
			return $content;
		}

		if ( is_singular() ) {
			add_filter( 'the_content', array( $this, 'filter_the_content' ) );
		}

		$attributes = self::$helpers->omit( $attributes, array( 'toInsert', 'activeIconSet', 'showModal', 'searchIcon', 'icons' ) );
		// Import variables into the current symbol table from an array
		extract( $attributes );

		// Recipe post variables
		self::$recipe 			= get_post();
		$recipe_ID 				= get_the_ID( self::$recipe );
		$recipe_title 			= get_the_title( self::$recipe );
		$recipe_thumbnail_url 	= get_the_post_thumbnail_url( self::$recipe );
		$recipe_thumbnail_id 	= get_post_thumbnail_id( self::$recipe );
		$recipe_permalink 		= get_the_permalink( self::$recipe );
		$recipe_author_name 	= get_the_author_meta( 'display_name', self::$recipe->post_author );
		$attachment_id 			= isset( $image['id'] ) ? $image['id'] : $recipe_thumbnail_id;
		
		// Variables from attributes
		// add default value if not exists
		$recipeTitle 	= isset( $recipeTitle ) ? $recipeTitle : '';
		$summary 		= isset( $summary ) ? $summary : '';
		$className 		= isset( $className ) ? $className : '';
		$hasImage 		= isset( $hasImage ) ? $hasImage : false;
		$course 		= isset( $course ) ? $course : array();
		$cuisine 		= isset( $cuisine ) ? $cuisine : array();
		$difficulty 	= isset( $difficulty ) ? $difficulty : array();
		$keywords 		= isset( $keywords ) ? $keywords : array();
		$details 		= isset( $details ) ? $details : array();
		$ingredients 	= isset( $ingredients ) ? $ingredients : array();
		$steps 			= isset( $steps ) ? $steps : array();
		
		// Store variables
		self::$recipeBlockID = esc_attr( $id );
		self::$attributes    = $attributes;
		self::$settings      = self::$helpers->parse_block_settings( $attributes );
		self::$attributes['summaryTitle']     = isset( $summaryTitle ) ? $summaryTitle : __('Description', 'delicious-recipes');
		self::$attributes['ingredientsTitle'] = isset( $ingredientsTitle ) ? $ingredientsTitle : __('Ingredients', 'delicious-recipes');
		self::$attributes['directionsTitle']  = isset( $directionsTitle ) ? $directionsTitle : __('Instructions', 'delicious-recipes');
		self::$attributes['videoTitle']       = isset( $videoTitle ) ? $videoTitle : __('Video', 'delicious-recipes');
		self::$attributes['difficultyTitle']  = isset( $difficultyTitle ) ? $difficultyTitle : __('Difficulty', 'delicious-recipes');
		self::$attributes['seasonTitle']      = isset( $seasonTitle ) ? $seasonTitle : __('Best Season', 'delicious-recipes');
		
		$class = 'dr-summary-holder wp-block-delicious-recipes-block-recipe-card';
		$class .= $hasImage && isset($image['url']) ? '' : ' recipe-card-noimage';
		$RecipeCardClassName = implode( ' ', array( $class, $className ) );

		$custom_author_name = $recipe_author_name;
		if ( ! empty( self::$settings['custom_author_name'] ) ) {
			$custom_author_name = self::$settings['custom_author_name'];
		}
		
		$styles = '';
		$printStyles = self::$helpers->render_styles_attributes( $styles );
		$pin_description = strip_tags($recipeTitle);

		$recipe_card_image = '';

		if ( $hasImage && isset( $image['url'] ) ) {
			$img_id = $image['id'];
			$src 	= $image['url'];
			$alt 	= ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) );
			$sizes 	= isset( $image['sizes'] ) ? $image['sizes'] : array();
			$size 	= self::get_recipe_image_size( $sizes, $src );
			$img_class = ' delicious-recipes-card-image';

			// Check if attachment image is from imported content
			// in this case we don't have attachment in our upload directory
			$upl_dir = wp_upload_dir();
			$findpos = strpos( $src, $upl_dir['baseurl'] );

			if ( $findpos === false ) {
				$attachment = sprintf(
					'<img src="%s" alt="%s" class="%s"/>',
					$src,
					$alt,
					trim( $img_class )
				);
			}
			else {
				$attachment = wp_get_attachment_image(
					$img_id,
					$size,
					false,
					array(
						'alt' => $alt,
                        'id' => $img_id,
                        'class' => trim( $img_class )
					)
				);
			}

			$recipe_card_image = '<div class="dr-image">
				<figure>
					'. $attachment .'
					<figcaption>
						'.
							( self::$settings['pin_btn'] ? self::get_pinterest_button( $image, $recipe_permalink, $pin_description ) : '' ).
							( self::$settings['print_btn'] ? self::get_print_button( $id, array( 'title' => __( "Print", "delicious-recipes" ), 'style' => $printStyles ) ) : '' )
						.'
					</figcaption>
				</figure>
			</div>';
		}
		elseif ( ! $hasImage && ! empty( $recipe_thumbnail_url ) ) {
			$img_id = $recipe_thumbnail_id;
			$src 	= $recipe_thumbnail_url;
			$alt 	= ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) );
			$sizes 	= isset( $image['sizes'] ) ? $image['sizes'] : array();
			$size 	= self::get_recipe_image_size( $sizes, $src );
			$img_class = ' delicious-recipes-card-image';

			// Check if attachment image is from imported content
			// in this case we don't have attachment in our upload directory
			$upl_dir = wp_upload_dir();
			$findpos = strpos( $src, $upl_dir['baseurl'] );

			if ( $findpos === false ) {
				$attachment = sprintf(
					'<img src="%s" alt="%s" class="%s"/>',
					$src,
					$alt,
					trim( $img_class )
				);
			}
			else {
				$attachment = wp_get_attachment_image(
					$img_id,
					$size,
					false,
					array(
						'alt'   => $alt,
						'id'    => $img_id,
						'class' => trim( $img_class )
					)
				);
			}

			$recipe_card_image = '<div class="dr-image">
				<figure>
					'. sprintf( '<img id="%s" src="%s" alt="%s" class="%s"/>', $img_id, $src, $alt, trim($img_class) ) .'
					<figcaption>
						'.
							( self::$settings['pin_btn'] ? self::get_pinterest_button( array( 'url' => $recipe_thumbnail_url ), $recipe_permalink, $pin_description ) : '' ).
							( self::$settings['print_btn'] ? self::get_print_button( $id, array( 'title' => __( "Print", "delicious-recipes" ), 'style' => $printStyles ) ) : '' )
						.'
					</figcaption>
				</figure>
			</div>';
		} 
		else {
			$fallback_svg = delicious_recipes_get_fallback_svg( 'recipe-feat-gallery', true );
			$recipe_card_image = '<div class="dr-image">
				<figure>
					'. $fallback_svg .'
					<figcaption>
						'.
							( self::$settings['print_btn'] ? self::get_print_button( $id, array( 'title' => __( "Print", "delicious-recipes" ), 'style' => $printStyles ) ) : '' )
						.'
					</figcaption>
				</figure>
			</div>';
		}

		$recipe_card_heading = '
			<div class="dr-title-wrap">
				'. sprintf( '<h2 class="%s">%s</h2>', "dr-title recipe-card-title", ( $recipeTitle ? strip_tags( $recipeTitle ) : strip_tags( $recipe_title ) ) ) .
				'<div class="dr-entry-meta">'.
					( self::$settings['displayAuthor'] ? '<span class="dr-byline"><span class="dr-meta-title">
					<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ). 'assets/images/sprite.svg#author"></use></svg>'. __( "Author:", "delicious-recipes" ) . " " .
					'</span>'. $custom_author_name .'</span>' : '' ) .
					( self::$settings['displayCookingMethod'] ? self::get_recipe_terms( 'recipe-cooking-method' ) : '' ) .
					( self::$settings['displayCuisine'] ? self::get_recipe_terms( 'recipe-cuisine' ) : '' ) .
					( self::$settings['displayCourse'] ? self::get_recipe_terms( 'recipe-course' ) : '' ) .
					( self::$settings['displayRecipeKey'] ? self::get_recipe_terms( 'recipe-key' ) : '' ) .
				'</div>'.
			'</div>';

		$summary_text = '';
		if ( ! empty( $summary ) ) {
			$summary_class = 'dr-summary recipe-card-summary';
			$summary_text = sprintf(
				'<div class="%s"><h3 class="%s">%s</h3><p>%s</p></div>',
				esc_attr( $summary_class ),
				'dr-title summary-title',
				@$summaryTitle,
				$summary
			);
		}

		$details_content     = self::get_details_content( $details );
		$ingredients_content = self::get_ingredients_content( $ingredients );
		$steps_content       = self::get_steps_content( $steps );
		$recipe_card_video   = self::get_video_content();

		$strip_tags_notes = isset( $notes ) ? strip_tags($notes) : '';
		$notes            = str_replace('<li></li>', '', $notes);       // remove empty list item
		$notes_content = ! empty($strip_tags_notes) ?
			sprintf(
				'<div class="dr-note">
					<h3 class="dr-title notes-title">%s</h3>
					<ul class="recipe-card-notes-list">%s</ul>
				</div>',
				@$notesTitle,
				@$notes
			) : '';

		$keywords_text = '';
		if ( ! empty( $keywords ) ) {
			$keywords_class = 'dr-keywords';
			$keywords_text = sprintf(
				'<div class="%s"><span class="%s">%s</span>%s</div>',
				esc_attr( $keywords_class ),
				'dr-meta-title',
				__("Keywords:", 'delicious-recipes'),
				implode( ', ', $keywords )
			);
		}

		$json_ld              = self::get_json_ld( $attributes );
		$structured_data_json = '';

		if ( ! empty( $json_ld ) ) {
			$structured_data_json = '<script type="application/ld+json">' . wp_json_encode( $json_ld ) . '</script>';
		}

		$block_content = sprintf(
			'<div class="%1$s" id="%2$s">
				<div class="dr-post-summary">
					<div class="dr-recipe-summary-inner">%3$s</div>
					%4$s
					%5$s
				</div>
			</div>',
			esc_attr( trim($RecipeCardClassName) ),
			esc_attr( $id ),
			$recipe_card_image .
			$recipe_card_heading ,
			$details_content .
			$summary_text ,
			$ingredients_content .
			$steps_content .
			$recipe_card_video .
			$notes_content .
			$keywords_text .
			$structured_data_json
		);

		return $block_content;
	}

	/**
	 * Returns the JSON-LD for a recipe-card block.
	 *
	 * @return array The JSON-LD representation of the recipe-card block.
	 */
	protected static function get_json_ld() {
		$attributes = self::$attributes;
		$tag_list  	= wp_get_post_terms( self::$recipe->ID, 'post_tag', array( 'fields' => 'names' ) );
		$cat_list 	= wp_get_post_terms( self::$recipe->ID, 'category', array( 'fields' => 'names' ) );

		$json_ld = array(
			'@context' 		=> 'https://schema.org',
			'@type'    		=> 'Recipe',
			'name'			=> isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title,
			'image'			=> '',
			'description' 	=> isset( $attributes['summary'] ) ? $attributes['summary'] : self::$recipe->post_excerpt,
			'keywords'  	=> $tag_list,
			'author' 		=> array(
				'@type'		=> 'Person',
				'name'		=> get_the_author()
			),
			'datePublished' => get_the_time('c'),
			'prepTime' 		=> '',
			'cookTime'		=> '',
			'totalTime' 	=> '',
			'recipeCategory' => $cat_list,
			'recipeCuisine'  => array(),
			'cookingMethod'  => array(),
			'recipeYield'	=> '',
			'nutrition' 	=> array(
				'@type' 	=> 'NutritionInformation'
			),
			'recipeIngredient'	 => array(),
			'recipeInstructions' => array(),
			'video'			=> array(
				'@type'			=> 'VideoObject',
				'name'  		=> isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title,
				'description' 	=> isset( $attributes['summary'] ) ? $attributes['summary'] : self::$recipe->post_excerpt,
				'thumbnailUrl' 	=> '',
				'contentUrl' 	=> '',
				'embedUrl' 		=> '',
				'uploadDate' 	=> get_the_time('c'), // by default is post plublish date
				'duration' 		=> '',
			),
		);

		if ( ! empty( $attributes['recipeTitle'] ) ) {
			$json_ld['name'] = $attributes['recipeTitle'];
		}

		if ( ! empty( $attributes['summary'] ) ) {
			$json_ld['description'] = strip_tags( $attributes['summary'] );
		}

		if ( ! empty( $attributes['image'] ) && isset( $attributes['hasImage'] ) && $attributes['hasImage'] ) {
			$image_id = isset( $attributes['image']['id'] ) ? $attributes['image']['id'] : 0;
 			$image_sizes = isset( $attributes['image']['sizes'] ) ? $attributes['image']['sizes'] : array();
 			$image_sizes_url = array(
 				self::get_image_size_url( $image_id, 'full', $image_sizes ),
 				self::get_image_size_url( $image_id, 'delrecpe-structured-data-1_1', $image_sizes ),
 				self::get_image_size_url( $image_id, 'delrecpe-structured-data-4_3', $image_sizes ),
 				self::get_image_size_url( $image_id, 'delrecpe-structured-data-16_9', $image_sizes ),
 			);
 			$json_ld['image'] = array_values( array_unique( $image_sizes_url ) );
		}

		if ( isset( $attributes['video'] ) && ! empty( $attributes['video'] ) && isset( $attributes['hasVideo'] ) && $attributes['hasVideo'] ) {
			$video = $attributes['video'];
			$video_id = isset( $video['id'] ) ? $video['id'] : 0;
			$video_type = isset( $video['type'] ) ? $video['type'] : '';

			if ( 'self-hosted' === $video_type ) {
 				$video_attachment = get_post( $video_id );

 				if ( $video_attachment ) {
 					$video_data = wp_get_attachment_metadata( $video_id );
 					$video_url = wp_get_attachment_url( $video_id );

 					$image_id = get_post_thumbnail_id( $video_id );
 					$thumb = wp_get_attachment_image_src( $image_id, 'full' );
 					$thumbnail_url = $thumb && isset( $thumb[0] ) ? $thumb[0] : '';

 					$json_ld['video'] = array_merge(
 						$json_ld['video'], array(
 							'name' => $video_attachment->post_title,
 							'description' => $video_attachment->post_content,
 							'thumbnailUrl' => $thumbnail_url,
 							'contentUrl' => $video_url,
 							'uploadDate' => date( 'c', strtotime( $video_attachment->post_date ) ),
 							'duration' => 'PT' . $video_data['length'] . 'S',
 						)
 					);
 				}
 			}

			if ( isset( $video['title'] ) && ! empty( $video['title'] ) ) {
				$json_ld['video']['name'] = esc_html( $video['title'] );
			}
			if ( isset( $video['caption'] ) && !empty( $video['caption'] ) ) {
				$json_ld['video']['description'] = esc_html( $video['caption'] );
			}
			if ( isset( $video['description'] ) && !empty( $video['description'] ) ) {
				if ( is_string( $video['description'] ) ) {
					$json_ld['video']['description'] = esc_html( $video['description'] );
				}
			}
			if ( isset( $video['poster']['url'] ) ) {
				$json_ld['video']['thumbnailUrl'] = esc_url( $video['poster']['url'] );

				if ( isset( $video['poster']['id'] ) ) {
 					$poster_id = $video['poster']['id'];
 					$poster_sizes_url = array(
 						self::get_image_size_url( $poster_id, 'full' ),
 						self::get_image_size_url( $poster_id, 'delrecpe-structured-data-1_1' ),
 						self::get_image_size_url( $poster_id, 'delrecpe-structured-data-4_3' ),
 						self::get_image_size_url( $poster_id, 'delrecpe-structured-data-16_9' ),
 					);
 					$json_ld['video']['thumbnailUrl'] = array_values( array_unique( $poster_sizes_url ) );
 				}
			}
			if ( isset( $video['url'] ) ) {
				$json_ld['video']['contentUrl'] = esc_url( $video['url'] );

				if ( 'embed' === $video_type ) {
					$video_embed_url = $video['url'];

					$json_ld['video']['@type'] = 'VideoObject';

					if ( ! empty( $attributes['image'] ) && isset( $attributes['hasImage'] ) && $attributes['hasImage'] ) {
						$image_id = isset( $attributes['image']['id'] ) ? $attributes['image']['id'] : 0;
 						$image_sizes = isset( $attributes['image']['sizes'] ) ? $attributes['image']['sizes'] : array();
 						$image_sizes_url = array(
 							self::get_image_size_url( $image_id, 'full', $image_sizes ),
 							self::get_image_size_url( $image_id, 'delrecpe-structured-data-1_1', $image_sizes ),
 							self::get_image_size_url( $image_id, 'delrecpe-structured-data-4_3', $image_sizes ),
 							self::get_image_size_url( $image_id, 'delrecpe-structured-data-16_9', $image_sizes ),
 						);
 						$json_ld['video']['thumbnailUrl'] = array_values( array_unique( $image_sizes_url ) );
					}

					if ( strpos( $video['url'], 'youtu' ) ) {
						$video_embed_url = self::$helpers->convert_youtube_url_to_embed( $video['url'] );
					}
					elseif ( strpos( $video['url'] , 'vimeo' ) ) {
						$video_embed_url = self::$helpers->convert_vimeo_url_to_embed( $video['url'] );
					}

					$json_ld['video']['embedUrl'] = esc_url( $video_embed_url );
				}
			}
			if ( isset( $video['date'] ) && 'embed' === $video_type ) {
				$json_ld['video']['uploadDate'] = $video['date'];
			}
		}
		else {

			// we have no video added
			// removed video attribute from json_ld array
			unset( $json_ld['video'] );

		}

		if ( ! empty( $attributes['course'] ) && self::$settings['displayCourse'] ) {
			$json_ld['recipeCategory'] = $attributes['course'];
		}

		if ( ! empty( $attributes['cuisine'] ) && self::$settings['displayCuisine'] ) {
			$json_ld['recipeCuisine'] = $attributes['cuisine'];
		}

		if ( ! empty( $attributes['method'] ) && self::$settings['displayCookingMethod'] ) {
			$json_ld['cookingMethod'] = $attributes['method'];
		}

		if ( ! empty( $attributes['keywords'] ) ) {
			$json_ld['keywords'] = $attributes['keywords'];
		}

		if ( ! empty( $attributes['details'] ) && is_array( $attributes['details'] ) ) {
			$details = array_filter( $attributes['details'], 'is_array' );

			foreach ( $details as $key => $detail ) {
				if ( $key === 4 ) {
					if ( ! empty( $detail[ 'value' ] ) && self::$settings['displayServings'] ) {
						if ( !is_array( $detail['value'] ) ) {
							$yield = array(
 								$detail['value']
 							);

							if ( isset( $detail['unit'] ) && ! empty( $detail['unit'] ) ) {
								$yield[] = $detail['value'] .' '. $detail['unit'];
							}
						}
						elseif ( isset( $detail['jsonValue'] ) ) {
							$yield = array(
 								$detail['jsonValue']
 							);

							if ( isset( $detail['unit'] ) && ! empty( $detail['unit'] ) ) {
								$yield[] = $detail['value'] .' '. $detail['unit'];
							}
						}

						if ( isset( $yield ) ) {
 							$json_ld['recipeYield'] = $yield;
 						}
					}
				}
				elseif ( $key === 5 ) {
					if ( ! empty( $detail[ 'value' ] ) && self::$settings['displayCalories'] ) {
						if ( !is_array( $detail['value'] ) ) {
							$json_ld['nutrition']['calories'] = $detail['value'] .' cal';
						}
						elseif ( isset( $detail['jsonValue'] ) ) {
							$json_ld['nutrition']['calories'] = $detail['jsonValue'] .' cal';
						}
					}
				}
				elseif ( $key === 0 ) {
					if ( ! empty( $detail[ 'value' ] ) && self::$settings['displayPrepTime'] ) {
						if ( !is_array( $detail['value'] ) ) {
							$prepTime = self::$structured_data_helpers->get_number_from_string( $detail['value'] );
						    $json_ld['prepTime'] = self::$structured_data_helpers->get_period_time( $detail['value'] );
						}
						elseif ( isset( $detail['jsonValue'] ) ) {
							$prepTime = self::$structured_data_helpers->get_number_from_string( $detail['jsonValue'] );
						    $json_ld['prepTime'] = self::$structured_data_helpers->get_period_time( $detail['jsonValue'] );
						}
					}
				}
				elseif ( $key === 1 ) {
					if ( ! empty( $detail[ 'value' ] ) && self::$settings['displayCookingTime'] ) {
						if ( !is_array( $detail['value'] ) ) {
							$cookTime = self::$structured_data_helpers->get_number_from_string( $detail['value'] );
						    $json_ld['cookTime'] = self::$structured_data_helpers->get_period_time( $detail['value'] );
						}
						elseif ( isset( $detail['jsonValue'] ) ) {
							$cookTime = self::$structured_data_helpers->get_number_from_string( $detail['jsonValue'] );
						    $json_ld['cookTime'] = self::$structured_data_helpers->get_period_time( $detail['jsonValue'] );
						}
					}
				}
				elseif ( $key === 3 ) {
					if ( ! empty( $detail[ 'value' ] ) && self::$settings['displayTotalTime'] ) {
						if ( !is_array( $detail['value'] ) ) {
							$json_ld['totalTime'] = self::$structured_data_helpers->get_period_time( $detail['value'] );
						}
						elseif ( isset( $detail['jsonValue'] ) ) {
							$json_ld['totalTime'] = self::$structured_data_helpers->get_period_time( $detail['jsonValue'] );
						}
					}
				}
			}

			if ( empty( $json_ld['totalTime'] ) ) {
				if ( isset( $prepTime, $cookTime ) && ( $prepTime + $cookTime ) > 0 ) {
					$json_ld['totalTime'] = self::$structured_data_helpers->get_period_time( $prepTime + $cookTime );
				}
			}
		}

		if ( ! empty( $attributes['ingredients'] ) && is_array( $attributes['ingredients'] ) ) {
			$ingredients = array_filter( $attributes['ingredients'], 'is_array' );
			foreach ( $ingredients as $ingredient ) {
				$isGroup = isset( $ingredient['isGroup'] ) ? $ingredient['isGroup'] : false;

				if ( ! $isGroup ) {
					$json_ld['recipeIngredient'][] = self::$structured_data_helpers->get_ingredient_json_ld( $ingredient );
				}

			}
		}

		if ( ! empty( $attributes['steps'] ) && is_array( $attributes['steps'] ) ) {
			$steps = array_filter( $attributes['steps'], 'is_array' );
			$groups_section = array();
			$instructions = array();

			foreach ( $steps as $key => $step ) {
				$isGroup = isset( $step['isGroup'] ) ? $step['isGroup'] : false;
				$parent_permalink = get_the_permalink( self::$recipe );
				
				if ( $isGroup ) {
					$groups_section[ $key ] = array(
						'@type' => 'HowToSection',
						'name' => '',
						'itemListElement' => array(),
					);
					if ( ! empty( $step['jsonText'] ) ) {
						$groups_section[ $key ]['name'] = $step['jsonText'];
					} else {
						$groups_section[ $key ]['name'] = self::$structured_data_helpers->step_text_to_JSON( $step['text'] );
					}
				}

				if ( count( $groups_section ) > 0 ) {
					end( $groups_section );
					$last_key = key( $groups_section );

					if ( ! $isGroup && $key > $last_key ) {
						$groups_section[ $last_key ]['itemListElement'][] = self::$structured_data_helpers->get_step_json_ld( $step, $parent_permalink );
					}
				} else {
					$instructions[] = self::$structured_data_helpers->get_step_json_ld( $step, $parent_permalink );
				}
			}

			$groups_section = array_merge( $instructions, $groups_section );
			$json_ld['recipeInstructions'] = $groups_section;
		}

		return $json_ld;
	}

	public static function get_details_default() {
		return array(
			array(
		    	'id' 		=> self::$helpers->generateId( "detail-item" ),
		    	'icon' 		=> 'time',
		    	'label' 	=> __( "Prep time", "delicious-recipes" ),
		    	'unit' 		=> __( "minutes", "delicious-recipes" ),
		    	'value'		=> '30'
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "detail-item" ),
		        'icon' 		=> 'time',
		        'label' 	=> __( "Cook time", "delicious-recipes" ),
		        'unit' 		=> __( "minutes", "delicious-recipes" ),
		        'value'		=> '40'
			),
			array(
		        'id' 		=> self::$helpers->generateId( "detail-item" ),
		        'icon' 		=> 'time',
		        'label' 	=> __( "Rest time", "delicious-recipes" ),
		        'unit' 		=> __( "minutes", "delicious-recipes" ),
		        'value'		=> '40'
			),
			array(
		        'id' 		=> self::$helpers->generateId( "detail-item" ),
		        'icon' 		=> 'time',
		        'label' 	=> __( "Total time", "delicious-recipes" ),
		        'unit' 		=> __( "minutes", "delicious-recipes" ),
		        'value'		=> '0'
			),
			array(
				'id' 		=> self::$helpers->generateId( "detail-item" ),
				'icon' 		=> 'yield',
				'label' 	=> __( "Servings", "delicious-recipes" ),
				'unit' 		=> __( "servings", "delicious-recipes" ),
				'value'		=> '4'
			),
			array(
		        'id' 		=> self::$helpers->generateId( "detail-item" ),
		        'icon' 		=> 'calories',
		        'label' 	=> __( "Calories", "delicious-recipes" ),
		        'unit' 		=> __( "kcal", "delicious-recipes" ),
		        'value'		=> '300'
		    )
		);
	}

	public static function get_ingredients_default() {
		return array(
			array(
				'id' 		=> self::$helpers->generateId( "ingredient-item" ),
				'name' 		=> array(),
			),
		    array(
		    	'id' 		=> self::$helpers->generateId( "ingredient-item" ),
		    	'name' 		=> array(),
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "ingredient-item" ),
		        'name' 		=> array(),
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "ingredient-item" ),
		        'name' 		=> array(),
		    )
		);
	}

	public static function get_steps_default() {
		return array(
			array(
				'id' 		=> self::$helpers->generateId( "direction-step" ),
				'text' 		=> array(),
			),
		    array(
		    	'id' 		=> self::$helpers->generateId( "direction-step" ),
		    	'text' 		=> array(),
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "direction-step" ),
		        'text' 		=> array(),
		    ),
		    array(
		        'id' 		=> self::$helpers->generateId( "direction-step" ),
		        'text' 		=> array(),
		    )
		);
	}

	public static function get_details_content( array $details ) {
		$detail_items = self::get_detail_items( $details );
		$details_class = 'dr-extra-meta';

		if ( !empty($detail_items) ) {
			return sprintf(
				'<div class="%s">%s</div>',
				esc_attr( $details_class ),
				$detail_items
			);
		} else {
			return '';
		}
	}

	public static function get_detail_items( array $details ) {
		$output = '';

		$attributes 	= self::$attributes;
		extract( $attributes );

		$difficulty     = isset( $difficulty ) && self::$settings['displayDifficulty'] ? $difficulty : '';

		if( $difficulty) {
			$svg = '<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ). 'assets/images/sprite.svg#difficulty"></use></svg>';
			$output .= sprintf(
				'<span class="%1$s"><span class="%2$s">%3$s %4$s:</span><b>%5$s</b></span>',
				'dr-sim-metaa dr-lavel',
				'dr-meta-title',
				$svg,
				$difficultyTitle,
				ucfirst( $difficulty )
			);
		}
		
		foreach ( $details as $index => $detail ) {
			$value    = '';
			$icon_svg = '';
			$icon     = ! empty( $detail['icon'] ) ? $detail['icon'] : '';
			$label    = ! empty( $detail['label'] ) ? $detail['label'] : '';
			$unit     = ! empty( $detail['unit'] ) ? $detail['unit'] : '';

			if( ! empty( $icon ) ) {
				$icon_svg = '<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ). 'assets/images/sprite.svg#'.$icon.'"></use></svg>';
			}

			if ( ! empty( $detail[ 'value' ] ) ) {
				if ( ! is_array( $detail['value'] ) ) {
					$value = $detail['value'];
				} elseif ( isset( $detail['jsonValue'] ) ) {
					$value = $detail['jsonValue'];
				}
			}
			
			if ( 0 === $index && self::$settings['displayPrepTime'] != '1' ) {
				continue;
			} elseif ( 1 === $index && self::$settings['displayCookingTime'] != '1' ) {
				continue;
			} elseif ( 2 === $index && self::$settings['displayRestTime'] != '1' ) {
				continue;
			} elseif ( 3 === $index && self::$settings['displayTotalTime'] != '1' ) {
				continue;
			} elseif ( 4 === $index && self::$settings['displayServings'] != '1' ) {
				continue;
			} elseif ( 5 === $index && self::$settings['displayCalories'] != '1' ) {
				continue;
			}

			// convert minutes to hours for 'prep time', 'cook time' and 'total time'
			if ( 0 === $index || 1 === $index || 2 === $index || 3 === $index ) {
				if ( ! empty( $detail['value'] ) ) {
					$converts = self::$helpers->convertMinutesToHours( $detail['value'], true );
					if ( ! empty( $converts ) ) {
						$value = $unit = '';
						if ( isset( $converts['hours'] ) ) {
							$value .= $converts['hours']['value'];
							$value .= ' '. $converts['hours']['unit'];
						}
						if ( isset( $converts['minutes'] ) ) {
							$unit .= $converts['minutes']['value'];
							$unit .= ' '. $converts['minutes']['unit'];
						}
					}
				}
			}

			$output .= sprintf(
				'<span class="%1$s"><span class="dr-meta-title">%2$s:</span><b>%3$s</b></span>',
				'dr-sim-metaa',
				$icon_svg . $label ,
				$value .' '. $unit
			);
		}

		$season     = isset( $season ) && self::$settings['displayBestSeason'] ? $season : '';

		if( $season) {
			$svg = '<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ). 'assets/images/sprite.svg#season"></use></svg>';
			$output .= sprintf(
				'<span class="%1$s"><span class="%2$s">%3$s %4$s:</span><b>%5$s</b></span>',
				'dr-sim-metaa dr-season',
				'dr-meta-title',
				$svg,
				$seasonTitle,
				ucfirst( $season )
			);
		}

		return force_balance_tags( $output );
	}

	public static function get_ingredients_content( array $ingredients ) {
		$ingredient_items = self::get_ingredient_items( $ingredients );

		$listClassNames = implode( ' ', array( 'ingredients-list', 'dr-unordered-list' ) );

		return sprintf(
			'<div class="dr-ingredients-list"><div class="dr-ingrd-title-wrap"><h3 class="ingredients-title dr-title">%s</h3></div><ul class="%s">%s</ul></div>',
			self::$attributes['ingredientsTitle'],
			$listClassNames,
			$ingredient_items
		);
	}

	public static function get_ingredient_items( array $ingredients ) {
		$output = '';

		foreach ( $ingredients as $index => $ingredient ) {
			$name = '';
			$isGroup = isset( $ingredient['isGroup'] ) ? $ingredient['isGroup'] : false;
			$ingredient_id = isset( $ingredient['id'] ) ? 'dr-ing-' . $ingredient['id'] : '';

			if ( !$isGroup ) {
				if ( ! empty( $ingredient[ 'name' ] ) ) {
					$name = sprintf( '<span class="dr-ingredient-name">%s</span>', self::wrap_ingredient_name( $ingredient['name'] ) );

					$name = sprintf(
						'<input type="checkbox" id="%s"><label for ="%s">%s</label>',
						esc_attr( $ingredient_id ),
						esc_attr( $ingredient_id ),
						$name
					);
					$output .= sprintf(
						'<li>%s</li>',
						$name
					);
				}
			} else {
				if ( ! empty( $ingredient[ 'name' ] ) ) {
					$name = self::wrap_ingredient_name( $ingredient['name'] );
					$output .= sprintf(
						'<h4 class="dr-title">%s</h4>',
						$name
					);
				}
			}
		}

		return force_balance_tags( $output );
	}

	public static function get_steps_content( array $steps ) {
		$direction_items = self::get_direction_items( $steps );

		$listClassNames = implode( ' ', array( 'directions-list', 'dr-ordered-list' ) );

		return sprintf(
			'<div class="dr-instructions"><div class="dr-instrc-title-wrap"><h3 class="directions-title dr-title">%s</h3></div><ol class="%s">%s</ol></div>',
			self::$attributes['directionsTitle'],
			$listClassNames,
			$direction_items
		);
	}

	public static function get_direction_items( array $steps ) {
		$output = '';

		foreach ( $steps as $index => $step ) {
			$text = '';
			$isGroup = isset( $step['isGroup'] ) ? $step['isGroup'] : false;

			if ( !$isGroup ) {
				if ( ! empty( $step['text'] ) ) {
					$text = self::wrap_direction_text( $step['text'] );
					$output .= sprintf(
						'<li>%s</li>',
						$text
					);
				}
			} else {
				if ( ! empty( $step['text'] ) ) {
					$text = self::wrap_direction_text( $step['text'] );
					$output .= sprintf(
						'<h4 class="dr-title">%s</h4>',
						$text
					);
				}
			}
		}

		return force_balance_tags( $output );
	}

	public static function get_recipe_terms( $taxonomy ) {
		$attributes 	= self::$attributes;
		$render 		= true;

		$className = $label = $terms_output = '';

		extract( $attributes );

		$course     = isset( $course ) ? $course : array();
		$cuisine    = isset( $cuisine ) ? $cuisine : array();
		$method     = isset( $method ) ? $method : array();
		$recipeKey  = isset( $recipeKey ) ? $recipeKey : array();

		if ( 'recipe-course' === $taxonomy ) {
			if ( empty( $course ) ) {
				$render = false;
			}
			// $terms     = $course;
			if ( is_array( $course ) && ! empty( $course ) ) {
				$terms = array_map(function( $term ) {
					$_term = get_term_by( 'name', $term, 'recipe-course' );
					if ( $_term ) {
						return '<a href="' . esc_url( get_term_link( $_term, 'recipe-course' ) ) . '" title="' . esc_attr( $term ) . '">' . $term . '</a>';
					}

					return '<a href="javascript:void(0);" title="' . esc_attr( $term ) . '">' . $term . '</a>'; 
				}, $course);
			} else {
				$terms = $course;
			}
			$className = 'dr-category';
			$label     = __( "Courses:", "delicious-recipes" );
			$svg       = '<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ).'assets/images/sprite.svg#category"></use></svg>';
		}
		elseif ( 'recipe-cuisine' === $taxonomy ) {
			if ( empty( $cuisine ) ) {
				$render = false;
			}
			// $terms     = $cuisine;
			if ( is_array( $cuisine ) && ! empty( $cuisine ) ) {
				$terms = array_map(function( $term ) {
					$_term = get_term_by( 'name', $term, 'recipe-cuisine' );
					if ( $_term ) {
						return '<a href="' . esc_url( get_term_link( $_term, 'recipe-cuisine' ) ) . '" title="' . esc_attr( $term ) . '">' . $term . '</a>';
					}

					return '<a href="javascript:void(0);" title="' . esc_attr( $term ) . '">' . $term . '</a>'; 
				}, $cuisine);
			} else {
				$terms = $cuisine;
			}
			$className = 'dr-cuisine';
			$label     = __( "Cuisine:", "delicious-recipes" );
			$svg       = '<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ).'assets/images/sprite.svg#cuisine"></use></svg>';
		}
		elseif ( 'recipe-cooking-method' === $taxonomy ) {
			if ( empty( $method ) ) {
				$render = false;
			}
			// $terms     = $method;
			if ( is_array( $method ) && ! empty( $method ) ) {
				$terms = array_map(function( $term ) {
					$_term = get_term_by( 'name', $term, 'recipe-cooking-method' );
					if ( $_term ) {
						return '<a href="' . esc_url( get_term_link( $_term, 'recipe-cooking-method' ) ) . '" title="' . esc_attr( $term ) . '">' . $term . '</a>';
					}

					return '<a href="javascript:void(0);" title="' . esc_attr( $term ) . '">' . $term . '</a>'; 
				}, $method);
			} else {
				$terms = $method;
			}
			$className = 'dr-method';
			$label     = __( "Cooking Method:", "delicious-recipes" );
			$svg       = '<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ).'assets/images/sprite.svg#cooking-method"></use></svg>';
		}
		elseif ( 'recipe-key' === $taxonomy ) {
			/**
			 * Map icons based on the recipe keys.
			 * Sanitize the recipe key (@see https://developer.wordpress.org/reference/functions/sanitize_title/) to make sure the icon maps works with translation also.
			 */
			$recipe_key_icons = [
				sanitize_title( __("Gluten Free", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Gluten Free", "delicious-recipes") . '" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"><g transform="translate(-1411 -3350)"><circle class="gluten-free-circle" fill="#f7bd31" cx="19" cy="19" r="19" transform="translate(1411 3350)" /><g transform="translate(1384.164 2856.629) rotate(30)"><path class="gluten-free-path" fill="#fff" d="M301.678,410.675a5.81,5.81,0,0,1,0,9.009,5.794,5.794,0,0,1,0-9.009Z" transform="translate(-5.947 -2.26)" /><path class="gluten-free-path" fill="#fff" d="M302.092,437.167a.62.62,0,0,1-.015-.083,8.772,8.772,0,0,1,.357-3.022,4.69,4.69,0,0,1,1.005-1.82,4.5,4.5,0,0,1,2.04-1.249,5.232,5.232,0,0,1,1.345-.215,5.44,5.44,0,0,1,1.27.1c.036.007.072.018.123.03.006.17.022.341.016.511-.009.227-.027.454-.059.68a4.912,4.912,0,0,1-1.3,2.775,6.345,6.345,0,0,1-1.176.948,9.312,9.312,0,0,1-3.2,1.287c-.121.025-.243.042-.365.061A.212.212,0,0,1,302.092,437.167Z" transform="translate(-6.128 -3.689)" /><path class="gluten-free-path" fill="#fff" d="M308.209,424.646a3.9,3.9,0,0,1,0,.883,5.483,5.483,0,0,1-.231,1.184,4.782,4.782,0,0,1-1.61,2.362,8.888,8.888,0,0,1-2.955,1.535,8.631,8.631,0,0,1-1.2.284c-.04.007-.08.008-.134.015-.005-.095-.013-.179-.015-.264a8.9,8.9,0,0,1,.317-2.693,5.166,5.166,0,0,1,.818-1.7,4.226,4.226,0,0,1,1.369-1.155,4.852,4.852,0,0,1,1.941-.571,5.462,5.462,0,0,1,1.616.091C308.157,424.629,308.181,424.637,308.209,424.646Z" transform="translate(-6.127 -3.244)" /><path class="gluten-free-path" fill="#fff" d="M301.114,424.657c-.245-.049-.477-.089-.706-.143a9.159,9.159,0,0,1-3.1-1.345,5.657,5.657,0,0,1-1.283-1.162,4.946,4.946,0,0,1-.946-2.095,5.51,5.51,0,0,1-.11-1.37c0-.052.009-.1.015-.167.107-.02.212-.045.318-.061a5.283,5.283,0,0,1,3.065.4,4.237,4.237,0,0,1,2.214,2.33,7.314,7.314,0,0,1,.467,1.818,9.906,9.906,0,0,1,.081,1.418C301.133,424.4,301.122,424.515,301.114,424.657Z" transform="translate(-5.623 -2.799)" /> <path class="gluten-free-path" fill="#fff" d="M295,430.9c.165-.027.333-.062.5-.08a5.649,5.649,0,0,1,.625-.045,4.843,4.843,0,0,1,3.539,1.37,4.276,4.276,0,0,1,.935,1.463,7.233,7.233,0,0,1,.452,1.775,9.98,9.98,0,0,1,.081,1.452c0,.111-.013.221-.021.347-.139-.023-.268-.04-.395-.066a9.294,9.294,0,0,1-3.317-1.355,5.753,5.753,0,0,1-1.318-1.156,4.938,4.938,0,0,1-1-2.166,5.7,5.7,0,0,1-.11-1.358.892.892,0,0,1,.006-.089C294.984,430.962,294.991,430.937,295,430.9Z" transform="translate(-5.623 -3.689)" /> <path class="gluten-free-path" fill="#fff" d="M302.085,424.648c-.006-.092-.011-.173-.013-.253a8.771,8.771,0,0,1,.34-2.79,4.989,4.989,0,0,1,.834-1.664,4.316,4.316,0,0,1,1.741-1.3,5.041,5.041,0,0,1,1.9-.392,5.532,5.532,0,0,1,1.193.1l.087.02.052.017c.006.155.021.311.018.466,0,.191-.018.381-.039.569a4.961,4.961,0,0,1-1.259,2.865,6.255,6.255,0,0,1-1.294,1.049,9.344,9.344,0,0,1-3.368,1.289l-.056.007Z" transform="translate(-6.128 -2.799)" /> <path class="gluten-free-path" fill="#fff" d="M301.108,430.909c-.076-.01-.15-.016-.221-.03a9.362,9.362,0,0,1-3.521-1.412,5.635,5.635,0,0,1-1.3-1.146,4.954,4.954,0,0,1-.984-2.137,5.561,5.561,0,0,1-.111-1.381c0-.03,0-.059.007-.089s.009-.043.018-.077c.137-.024.275-.055.414-.073a5.52,5.52,0,0,1,2.242.152,4.713,4.713,0,0,1,1.835,1,4.043,4.043,0,0,1,1,1.367,6.735,6.735,0,0,1,.545,1.888,7.454,7.454,0,0,1,.092,1.037c0,.28,0,.56,0,.84A.432.432,0,0,1,301.108,430.909Z" transform="translate(-5.623 -3.244)" /> </g> </g> </svg>',
				sanitize_title( __("Dairy Free", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Dairy Free", "delicious-recipes") . '" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <defs>dairy-free</defs> <g data-name="Group 4626" transform="translate(-521 -148)"> <g data-name="Group 4430" transform="translate(-698 -2588)"> <circle data-name="Ellipse 97" cx="19" cy="19" r="19" transform="translate(1219 2736)" fill="#2fcde1" /> <g data-name="Group 4424" transform="translate(1814.985 2514.889)"> <path data-name="Path 30559" d="M-579.2,243.665h3.987c-.2,1.972.9,3.49,1.555,5.165a10.283,10.283,0,0,1,.79,3.37c.1,3.492.04,6.988.035,10.483,0,1.363-.27,1.626-1.6,1.626-1.929,0-3.858,0-5.787,0-.987,0-1.335-.307-1.339-1.29-.015-3.8-.063-7.606.04-11.405a8.492,8.492,0,0,1,.869-3.016C-579.97,247.015-579,245.54-579.2,243.665Z" transform="translate(0 -12.909)" fill="#fff" /> <path data-name="Path 30560" d="M-567.31,229.907c-.475,0-.952-.035-1.423.008-.677.062-.919-.172-.933-.883-.014-.753.3-.9.947-.877q1.548.049,3.1,0c.737-.024.687.435.7.935.017.528-.076.871-.718.83-.556-.036-1.116-.007-1.675-.007Z" transform="translate(-9.925)" fill="#fff" /> </g> </g> <line data-name="Line 1" x2="38" transform="translate(527.065 153.065) rotate(45)" fill="none" stroke="#fff" stroke-width="2" /> </g> </svg>',
				sanitize_title( __("Low Carb", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Low Carb", "delicious-recipes") . '" xmlns="http://www.w3.org/2000/svg" width="38" height="37" viewBox="0 0 38 37"> <g transform="translate(-1219 -2677)"> <ellipse class="low-carb-circle" fill="#a6846e" cx="19" cy="18.5" rx="19" ry="18.5" transform="translate(1219 2677)" /> <g transform="translate(1834.406 2484.021)"> <path class="low-carb-path" fil=" #fff" d="M-598.782,202.979a29.11,29.11,0,0,1,8.668,1.457,10.235,10.235,0,0,1,5.031,4.1,3.644,3.644,0,0,1,.52,1.769c.067,1.255-.076,2.523,0,3.777a4.349,4.349,0,0,1-2.155,3.938,8.076,8.076,0,0,1-4.536,1.515,32.081,32.081,0,0,1-4.259-.218,4.569,4.569,0,0,1-2.94-1.56c-.811-.878-1.646-1.737-2.409-2.656a5.206,5.206,0,0,0-3.793-1.837,17.946,17.946,0,0,1-2.187-.25,3.066,3.066,0,0,1-1.348-.6,1.752,1.752,0,0,1-.523-1.126c-.075-1.424-.009-2.856-.083-4.281a2.629,2.629,0,0,1,1.857-2.815,14.141,14.141,0,0,1,3.139-.85C-602.139,203.123-600.456,203.09-598.782,202.979Zm13.26,6.987a3.92,3.92,0,0,0-1.16-2.42,10.247,10.247,0,0,0-6.247-3.344,38.383,38.383,0,0,0-9.911-.418,9.005,9.005,0,0,0-3.915,1.06,11,11,0,0,0-1.047.741l.089.161c.2-.073.406-.149.611-.218a16.534,16.534,0,0,1,2.445-.8,30.992,30.992,0,0,1,12.314.286,9.946,9.946,0,0,1,6.185,4.126C-586,209.377-585.809,209.6-585.523,209.966Zm-.066.677a.809.809,0,0,0-.2.108c-2.037,2.3-4.723,2.808-7.607,2.725a5.907,5.907,0,0,1-4.587-2.135c-.336-.4-.733-.761-1.027-1.19a5.242,5.242,0,0,0-3.788-2.2,11.466,11.466,0,0,1-5.223-1.6,1.583,1.583,0,0,0,1.33,1.335,25.736,25.736,0,0,0,3.2.662,5.72,5.72,0,0,1,4.268,2.311,10.3,10.3,0,0,0,2.473,2.383,7.259,7.259,0,0,0,4.667.949,9.3,9.3,0,0,0,5.239-1.534A2.523,2.523,0,0,0-585.589,210.643Zm-5.87.8c.838.021,1.452-.448,1.47-1.122a1.832,1.832,0,0,0-1.875-1.579c-.842-.021-1.463.451-1.474,1.122A1.828,1.828,0,0,0-591.459,211.443Z" /> </g> </g> </svg>',
				sanitize_title( __("Vegetarian", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Vegetarian", "delicious-recipes") .'" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <g transform="translate(-1219 -2556)"> <circle class="vegeterian-meals-circle" fill="#8fbc04" cx="19" cy="19" r="19" transform="translate(1219 2556)" /> <path class="vegeterian-meals-path" fill="#fff" d="M-158.562,677.785c.064,1.227-.807,2.049-1.97,2.671-.917.491-1.944.821-2.4,1.924a6.7,6.7,0,0,0-.267,4.213c.126.531.312,1.048.457,1.576.069.251.108.511.2.957a3.814,3.814,0,0,1-2.294-1.941,21.259,21.259,0,0,1-.792-2.854c-.28-1.2-.514-1.465-1.712-1.7a4.767,4.767,0,0,1-3.946-5.026.938.938,0,0,0-.266-.674,5.845,5.845,0,0,1-2.161-5.753,5.368,5.368,0,0,1,4.085-4.451,10.571,10.571,0,0,0,3.371-1.484,5.412,5.412,0,0,1,7.328,1.285.882.882,0,0,0,.518.284,3.229,3.229,0,0,1,2.057,4.868.854.854,0,0,0,.018.664,3.9,3.9,0,0,1-.492,4.116A8.971,8.971,0,0,1-158.562,677.785Zm-5.546,8.753c0-.231,0-.462,0-.693a16.288,16.288,0,0,1,1.158-7.414,6.982,6.982,0,0,0,.353-5.056.783.783,0,0,1,.152-.6,4.4,4.4,0,0,1,2.676-1.659c.511-.1,1.033-.135,1.618-.209-1.948-.911-3.391-.046-4.788,1.223a10.388,10.388,0,0,1,.827-5.4c-1.122,1.468-2.019,3.026-1.743,4.954.142.995.464,1.963.678,2.949a11.187,11.187,0,0,1,.133,1.155c-1.5-1.773-2.841-2.488-4.411-2.193a6.434,6.434,0,0,1,3.032,1.849,2.607,2.607,0,0,1,.557,3.237C-165.079,681.226-164.828,683.9-164.108,686.538Zm-4.729-18.917a5.109,5.109,0,0,0-1.757,2.615,6.887,6.887,0,0,0,1.042,5.537,1.7,1.7,0,0,1,.235,1.44,4.088,4.088,0,0,0,3.217,4.972c-1.966-1.591-3.369-3.356-2.1-6.01C-170.45,674.16-170.689,670.645-168.837,667.62Z" transform="translate(1402.478 1898.587)" /> </g> </svg>',
				sanitize_title( __("Organic", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Organic", "delicious-recipes") .'" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <defs>organic</defs> <g transform="translate(-953 -93)"> <circle cx="19" cy="19" r="19" transform="translate(953 93)" fill="#0f6b2b" /> <g transform="translate(952.53 98.416)"> <path d="M25.861,14.085a14.434,14.434,0,0,1,1.632,3.3l2.023.171A15.648,15.648,0,0,1,31.861,12.5a3.977,3.977,0,0,0,4.023-.391c1.7-1.566,1.54-6.311,1.1-9.051a.519.519,0,0,0-.943-.245c-2.368,3.229-6.529,3.669-6.46,6.776a2.156,2.156,0,0,0,.575,1.566,9.474,9.474,0,0,1,4.046-3.2.079.079,0,0,0,.046-.024,14.845,14.845,0,0,0-2.322,2.471,15.828,15.828,0,0,0-3.4,6.189A15.5,15.5,0,0,0,27.1,13.719,14.915,14.915,0,0,0,24.251,10c.023,0,.023,0,.046.024a8.355,8.355,0,0,1,3.38,2.862,1.778,1.778,0,0,0,.529-1.321C28.344,8.9,24.78,8.41,22.849,5.573a.457.457,0,0,0-.828.2c-.437,2.324-.713,6.385.713,7.779A3.33,3.33,0,0,0,25.861,14.085Z" transform="translate(-8.459)" fill="#fff" /> <path d="M26.207,68.05a21.1,21.1,0,0,1-2.174.587,2.458,2.458,0,0,1-1.781,1.125,3.1,3.1,0,0,1-.694.049,18.669,18.669,0,0,1-2.151-.171.374.374,0,0,1-.162-.024.41.41,0,0,1-.37-.391.4.4,0,0,1,.44-.44c.069,0,.116.024.185.024a11.448,11.448,0,0,0,2.637.122c1.157-.2,1.48-.93,1.434-1.566a.979.979,0,0,0-.972-.978,20.57,20.57,0,0,1-2.683-.122A17.243,17.243,0,0,0,17,66.191,10.876,10.876,0,0,0,12.837,68c-.763.538-1.619,1.15-2.128,1.541a.619.619,0,0,0-.116.856l2.174,3.107a.537.537,0,0,0,.787.122l.833-.66a.791.791,0,0,1,.694-.122,17.342,17.342,0,0,0,4.141.978c2.29.171,6.732-2.617,8.027-3.523A1.245,1.245,0,0,0,26.207,68.05Z" transform="translate(0 -49.219)" fill="#fff" /> </g> </g> </svg>',
				sanitize_title( __("Nut Free", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Nut Free", "delicious-recipes") . '" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <defs>nut-free</defs> <g transform="translate(-521 -221)"> <g transform="translate(-698 -2515)"> <circle cx="19" cy="19" r="19" transform="translate(1219 2736)" fill="#ee9060" /> <g transform="translate(1201.842 2717.144)"> <path d="M43.37,52.69c-.107,3.887-3.271,9.635-7.162,9.635s-7.052-5.748-7.158-9.635Z" transform="translate(-0.892 -13.78)" fill="#fff" /> <path d="M44.8,37.451H26.42a9.257,9.257,0,0,1,9.189-9.071,9.056,9.056,0,0,1,6.5,2.748A9.286,9.286,0,0,1,44.8,37.451Z" transform="translate(-0.262)" fill="#fff" /> </g> </g> <line x2="9.435" y2="9.435" transform="translate(544.5 243.5)" fill="none" stroke="#fff" stroke-width="2" /> <line x2="7.435" y2="7.435" transform="translate(527.065 226.065)" fill="none" stroke="#fff" stroke-width="2" /> </g> </svg>',
				sanitize_title( __("Egg Free", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Egg Free", "delicious-recipes") . '" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <defs>egg-free</defs> <g transform="translate(-751 -238)"> <g transform="translate(-468 -2498)"> <circle cx="19" cy="19" r="19" transform="translate(1219 2736)" fill="#dc9f65" /> <path d="M28.17,10.729c-3.967,0-7.181,4.462-7.181,9.966s3.214,9.967,7.181,9.967S35.349,26.2,35.349,20.7,32.135,10.729,28.17,10.729Zm3.211,10.6c.91-1.385-.593-8.228-.593-8.228,1.977,2.137,2.6,7.234,2.6,7.234Z" transform="translate(1210.011 2734.271)" fill="#fff" /> </g> <line x2="38" transform="translate(757.065 243.065) rotate(45)" fill="none" stroke="#fff" stroke-width="2" /> </g> </svg>',
				sanitize_title( __("High Protein", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("High Protein", "delicious-recipes") . '" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <defs>high-protein</defs> <g transform="translate(-953 -166)"> <circle cx="19" cy="19" r="19" transform="translate(953 166)" fill="#990216" /> <g transform="translate(964 174)"> <path d="M17.08,21.6l17.31-7.427L22.417,10.128Zm8.13-7.927a1.6,1.6,0,0,1-1.432.887,1.439,1.439,0,0,1-.429-.064,1.2,1.2,0,0,1-.75-.662,1.222,1.222,0,0,1,.021-1.014,1.6,1.6,0,0,1,1.432-.887,1.457,1.457,0,0,1,.429.064,1.2,1.2,0,0,1,.75.662A1.22,1.22,0,0,1,25.21,13.671Zm-3.953,3.412a.951.951,0,0,1,.847-.525.869.869,0,0,1,.256.038.728.728,0,0,1,.454.4.736.736,0,0,1-.013.614.95.95,0,0,1-.847.526A.863.863,0,0,1,21.7,18.1a.726.726,0,0,1-.455-.4A.737.737,0,0,1,21.257,17.083Z" transform="translate(-16.874 -10.128)" fill="#fff" /> <g transform="translate(0 4.193)"> <path d="M16.31,39.859l0,2.583v.48l.44-.189L34.078,35.3l.191-.082V25.86l-17.953,7.7,0,4.267a1.344,1.344,0,0,1,.241-.024.71.71,0,0,1,.5.17.572.572,0,0,1,.146.421A1.941,1.941,0,0,1,16.31,39.859Zm13.115-7.25a1.188,1.188,0,0,1-.867-.313,1.038,1.038,0,0,1-.283-.769,2.388,2.388,0,0,1,.571-1.454,3.2,3.2,0,0,1,1.224-.935,2.059,2.059,0,0,1,.766-.155,1.189,1.189,0,0,1,.866.313,1.041,1.041,0,0,1,.283.769,2.388,2.388,0,0,1-.571,1.454,3.2,3.2,0,0,1-1.224.935A2.059,2.059,0,0,1,29.425,32.61Zm-2.134,3.163a1.453,1.453,0,0,1,.909-1.206,1.114,1.114,0,0,1,.4-.077.646.646,0,0,1,.472.172.564.564,0,0,1,.152.418,1.454,1.454,0,0,1-.909,1.207,1.13,1.13,0,0,1-.4.077.645.645,0,0,1-.472-.172A.561.561,0,0,1,27.291,35.773Zm-7.82,3.11a1.98,1.98,0,0,1,.473-1.2,2.637,2.637,0,0,1,1.01-.769,1.724,1.724,0,0,1,.637-.128,1,1,0,0,1,.728.263.871.871,0,0,1,.237.645,1.979,1.979,0,0,1-.473,1.2,2.627,2.627,0,0,1-1.01.769,1.713,1.713,0,0,1-.637.128,1,1,0,0,1-.727-.263A.872.872,0,0,1,19.471,38.883Zm-.531-4.4a1.453,1.453,0,0,1,.909-1.207,1.126,1.126,0,0,1,.4-.077.645.645,0,0,1,.472.172.563.563,0,0,1,.153.419,1.454,1.454,0,0,1-.909,1.207,1.117,1.117,0,0,1-.4.077.643.643,0,0,1-.472-.172A.56.56,0,0,1,18.94,34.479Z" transform="translate(-16.308 -25.86)" fill="#fff" /> </g> </g> </g> </svg>',
				sanitize_title( __("Keto", "delicious-recipes") ) => '<svg class="svg-icon" title="'. __("Keto", "delicious-recipes") .'" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <defs>keto</defs> <g transform="translate(-953 -238)"> <circle cx="19" cy="19" r="19" transform="translate(953 238)" fill="#769558" /> <g transform="matrix(0.966, 0.259, -0.259, 0.966, 966.366, 243.78)"> <path d="M16.138,15.8a3.73,3.73,0,0,0-3.493,3.929,3.73,3.73,0,0,0,3.493,3.929,3.73,3.73,0,0,0,3.492-3.929A3.731,3.731,0,0,0,16.138,15.8Zm-1.751,3.183a.553.553,0,0,1-.541.433A.343.343,0,0,1,13.512,19a2.792,2.792,0,0,1,2.626-2.328,2.373,2.373,0,0,1,.436.049A2.856,2.856,0,0,0,14.387,18.985Z" transform="translate(-3.04 -3.799)" fill="#fff" /> <path d="M6.434,11.631a7.079,7.079,0,0,0,1.234-4,5.686,5.686,0,0,1,1.48-3.826c-.062-.176-.141-.346-.191-.526L8.933,3.2A4.393,4.393,0,0,0,.477,5.588l0,.006a8.4,8.4,0,0,1-.118,4.953A7.028,7.028,0,0,0,5.7,19.666a8.31,8.31,0,0,1,.732-8.035Z" transform="translate(0)" fill="#fff" /> <path d="M20.958,13.425a8.513,8.513,0,0,1-1.513-4.69V8.65a4.183,4.183,0,1,0-8.355,0v.007A8.528,8.528,0,0,1,9.5,13.391a6.989,6.989,0,1,0,11.463.034Zm-5.77,9.206a5.291,5.291,0,0,1-4.383-8.258A10.119,10.119,0,0,0,12.569,8.65a2.678,2.678,0,0,1,5.356,0v.085a10.1,10.1,0,0,0,1.7,5.663,5.236,5.236,0,0,1,.876,2.939A5.31,5.31,0,0,1,15.189,22.631Z" transform="translate(-1.975 -1.024)" fill="#fff" /> </g> </g> </svg>',
				sanitize_title( __("Vegan", "delicious-recipes" ) ) => '<svg class="svg-icon" title="'. __("Vegan", "delicious-recipes") .'" xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38"> <defs>vegan</defs> <g transform="translate(-953 -311)"> <circle cx="19" cy="19" r="19" transform="translate(953 311)" fill="#0dab20" /> <path d="M17.264,12.731S15.7,8.959,12.97,8.6c0,0,.408.447,1.047,1.263a37.325,37.325,0,0,1,7.157,14.979s1.982-16.407,10.9-20.481c0,0-7.873,8.7-9.635,26.758H19.907s-.55-10.681-5.4-16.848c0,0-3.744.33-5.065-2.2S8.455,6.9,6.8,4.362C19.063,3.334,17.264,12.731,17.264,12.731Z" transform="translate(952.197 317.781)" fill="#fff" /> </g> </svg>'
			];

			if ( empty( $recipeKey ) ) {
				$render = false;
			}
			$_terms     = $recipeKey;

			$terms = array_map(function( $term ) use ( $recipe_key_icons ) {
				$icon = isset( $recipe_key_icons[ sanitize_title( $term ) ] ) ? $recipe_key_icons[ sanitize_title( $term ) ] : '';
				$key = get_term_by( 'name', $term, 'recipe-key' );
				if ( $key ) {
					return '<a href="' . esc_url( get_term_link( $key, 'recipe-key' ) ) . '" title="' . esc_attr( $term ) . '">
						<span class="dr-svg-icon">
						' . $icon . '
						</span>
						<span class="cat-name">
							' . $term . '
						</span>
					</a>';
				}

				return '<a href="javascript:void(0);">
					<span class="dr-svg-icon">
					<span style="background-color:#2db68d">' . mb_substr( strtoupper( $term ), 0, 1 ) . '</span>
					</span>
					<span class="cat-name">
						' . $term . '
					</span>
				</a>';
			}, $_terms );

			$className = 'dr-category dr-recipe-keys';
			$label     = __( "Recipe Keys:", "delicious-recipes" );
			$svg       = '<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ).'assets/images/sprite.svg#recipe-keys"></use></svg>';
		}

		if ( $render ) {
			if ( 'recipe-key' === $taxonomy ) {
				$terms_output = sprintf( '<span class="%s"><span class="dr-meta-title">%s %s</span>%s</span>', $className, $svg, $label, implode( '', $terms ) );
			} else {
				$terms_output = sprintf( '<span class="%s"><span class="dr-meta-title">%s %s</span>%s</span>', $className, $svg, $label, implode( ',&nbsp;', $terms ) );
			}
		}

		return $terms_output;
	}

	public static function wrap_direction_text( $nodes, $type = '' ) {
		$attributes = self::$attributes;

		if ( ! is_array( $nodes ) ) {
			return $nodes;
		}

		$output = '';
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				$output .= $node;
			} else {
				$type = isset( $node['type'] ) ? $node['type'] : null;
				$children = isset( $node['props']['children'] ) ? $node['props']['children'] : null;

				$start_tag = $type ? "<$type>" : "";
				$end_tag = $type ? "</$type>" : "";

				if ( 'img' === $type ) {
					$src = isset( $node['props']['src'] ) ? $node['props']['src'] : false;
					if ( $src ) {
						$attachment_id = isset( $node['key'] ) ? $node['key'] : 0;
						$alt = isset( $node['props']['alt'] ) ? $node['props']['alt'] : '';
						$title = isset( $node['props']['title'] ) ? $node['props']['title'] : ( isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title );
						$class = ' direction-step-image';
						$img_style = isset($node['props']['style']) ? $node['props']['style'] : '';

						// Try to get attachment ID by image url if attribute `key` is not found in $node array
						if ( ! $attachment_id ) {
							$new_src = $src;

							$re = '/-\d+[Xx]\d+\./m';
							preg_match_all( $re, $src, $matches );

							// Remove image size from url to be able to get attachment id
							// e.g. .../wp-content/uploads/sites/30/2019/10/image-example-1-500x375.jpg
							// 	 => .../wp-content/uploads/sites/30/2019/10/image-example-1.jpg
							if ( isset( $matches[0][0] ) ) {
								$new_src = str_replace( $matches[0][0], '.', $new_src );
							}

							// The found post ID, or 0 on failure.
							$attachment_id = attachment_url_to_postid( $new_src );

							if ( $attachment_id ) {
								$attachment = wp_get_attachment_image( $attachment_id, 'full', false, array( 'title' => $title, 'alt' => $alt, 'class' => trim( $class ), 'style' => self::parseTagStyle( $img_style ) ) );
							}
						}
						else {
							$attachment = wp_get_attachment_image( $attachment_id, 'full', false, array( 'title' => $title, 'alt' => $alt, 'class' => trim( $class ), 'style' => self::parseTagStyle( $img_style ) ) );
						}

						if ( $attachment ) {
							$start_tag = $attachment;
						}
						else {
							$start_tag = sprintf(
								'<%s src="%s" title="%s" alt="%s" class="%s" style="%s"/>',
								$type,
								$src,
								$title,
								$alt,
								trim( $class ),
								self::parseTagStyle( $img_style )
							);
						}
					}
					else {
						$start_tag = "";
					}
					$end_tag = "";
				}
				elseif ( 'a' === $type ) {
					$rel 		= isset( $node['props']['rel'] ) ? $node['props']['rel'] : '';
					$aria_label = isset( $node['props']['aria-label'] ) ? $node['props']['aria-label'] : '';
					$href 		= isset( $node['props']['href'] ) ? $node['props']['href'] : '#';
					$target 	= isset( $node['props']['target'] ) ? $node['props']['target'] : '_blank';

					$start_tag = sprintf( '<%s rel="%s" aria-label="%s" href="%s" target="%s">', $type, $rel, $aria_label, $href, $target );
				}
				elseif ( 'br' === $type ) {
					$end_tag = "";
				}

				$output .= $start_tag . self::wrap_direction_text( $children, $type ) . $end_tag;
			}
		}

		return $output;
	}

	public static function wrap_ingredient_name( $nodes, $type = '' ) {
		$attributes = self::$attributes;

		if ( ! is_array( $nodes ) ) {
			return $nodes;
		}

		$output = '';
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				$output .= $node;
			} else {
				$type = isset( $node['type'] ) ? $node['type'] : null;
				$children = isset( $node['props']['children'] ) ? $node['props']['children'] : null;

				$start_tag = $type ? "<$type>" : "";
				$end_tag = $type ? "</$type>" : "";

				if ( 'img' === $type ) {
					$src = isset( $node['props']['src'] ) ? $node['props']['src'] : false;
					if ( $src ) {
						$alt = isset( $node['props']['alt'] ) ? $node['props']['alt'] : '';
						$title = isset( $node['props']['title'] ) ? $node['props']['title'] : ( isset( $attributes['recipeTitle'] ) ? $attributes['recipeTitle'] : self::$recipe->post_title );
						$class = ' direction-step-image';
						$img_style = isset($node['props']['style']) ? $node['props']['style'] : '';

						$start_tag = sprintf( '<%s src="%s" title="%s" alt="%s" class="%s" style="%s"/>', $type, $src, $title, $alt, trim($class), self::parseTagStyle($img_style) );
					} else {
						$start_tag = "";
					}
					$end_tag = "";
				}
				elseif ( 'a' === $type ) {
					$rel 		= isset( $node['props']['rel'] ) ? $node['props']['rel'] : '';
					$aria_label = isset( $node['props']['aria-label'] ) ? $node['props']['aria-label'] : '';
					$href 		= isset( $node['props']['href'] ) ? $node['props']['href'] : '#';
					$target 	= isset( $node['props']['target'] ) ? $node['props']['target'] : '_blank';

					$start_tag = sprintf( '<%s rel="%s" aria-label="%s" href="%s" target="%s">', $type, $rel, $aria_label, $href, $target );
				}
				elseif ( 'br' === $type ) {
					$end_tag = "";
				}

				$output .= $start_tag . self::wrap_ingredient_name( $children, $type ) . $end_tag;
			}
		}

		return $output;
	}

	/**
	 * Get HTML content for recipe video
	 * 
	 * @since 2.1.1
	 * @return void
	 */
	public static function get_video_content() {
		$attributes = self::$attributes;
		$hasVideo = isset( $attributes['hasVideo'] ) && $attributes['hasVideo'];
		$output = '';

		if ( ! $hasVideo ) {
			return '';
		}

		$video = isset( $attributes['video'] ) && ! empty( $attributes['video'] ) ? $attributes['video'] : array();
		$video_type = isset( $video['type'] ) ? $video['type'] : '';
		$video_id = isset( $video['id'] ) ? $video['id'] : 0;
		$video_url = isset( $video['url'] ) ? esc_url( $video['url'] ) : '';
		$video_poster = isset( $video['poster']['url'] ) ? esc_url( $video['poster']['url'] ) : '';
		$video_settings = isset( $video['settings'] ) ? $video['settings'] : array();

		if ( 'embed' === $video_type ) {
			$output = wp_oembed_get( $video_url );
		}
		elseif ( 'self-hosted' === $video_type ) {
			$attrs = array();
			foreach ( $video_settings as $attribute => $value ) {
				if ( $value ) {
					$attrs[] = $attribute;
				}
			}
			$attrs = implode( ' ', $attrs );

			if ( empty( $video_url ) && 0 !== $video_id ) {
 				$video_url = wp_get_attachment_url( $video_id );
 			}

			$output = sprintf(
				'<video %s src="%s" poster="%s"></video>',
				esc_attr( $attrs ),
				$video_url,
				$video_poster
			);
		}

		return sprintf( '<div class="dr-instructions-video" id="dr-video-gallery"><h3 class="video-title">%s</h3><div class="dr-vdo-thumbnail">%s</div></div>', $attributes['videoTitle'], $output );
	}

	/**
	 * Filter content when rendering recipe card block
	 * Add snippets at the top of post content
	 *
	 * @since 1.2.0
	 * @param string $content Main post content
	 * @return string HTML of post content
	 */
	public function filter_the_content( $content ) {
		if ( ! in_the_loop() ) {
			return $content;
		}

		$output = '';

		return $output . $content;
	}

	/**
	 * Parse HTML tag styles
	 *
	 * @since 2.1.0
	 * @param string|array $style Tag styles to parse
	 * @return string 			  CSS styles
	 */
	public static function parseTagStyle( $styles ) {
		$css = '';
		if ( is_array( $styles ) ) {
			foreach ( $styles as $property => $value ) {
				$css .= $property.': '.$value.';';
			}
		} elseif ( is_string( $styles ) ) {
			$css = $styles;
		}
		return $css;
	}

	/**
	 * Get HTML for print button
	 * 
	 * @since 2.2.0
	 * 
	 * @param array $media        The recipe media image array which include 'url'
	 * @param string $url         The recipe permalink url
	 * @param string $description The description to display on pinterest board
	 * @param array $attributes   Additional html attributes like ('style' => 'color: red; font-size: inherit')
	 * @return string
	 */
	public static function get_print_button( $content_id, $attributes = array() ) {
		if ( empty( $content_id ) )
			return '';

		$PrintClasses = implode( ' ', array( "dr-buttons", "dr-recipe-card-block-print" ) );

		/**
		 * Add additional attributes to print button
		 * [serving-size, recipe-id]
		 * 
		 * @since 2.6.3
		 */
		$servings = isset( self::$attributes['details'][4]['value'] ) ? self::$attributes['details'][4]['value'] : 0;
		$attributes = array_merge( $attributes, array( 'data-servings-size' => $servings ) );

		if ( self::$recipe ) {
			$attributes = array_merge( $attributes, array( 'data-recipe-id' => self::$recipe->ID ) );
		}

		$atts = self::$helpers->render_attributes( $attributes );

		$output = sprintf(
			'<div class="%s">
	            <a class="dr-print-trigger dr-btn-link dr-btn2" href="#%s" %s>
					<svg class="icon"><use xlink:href="'.esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ). 'assets/images/sprite.svg#print"></use></svg>
	                %s
	            </a>
	        </div>',
			esc_attr( $PrintClasses ),
			esc_attr( $content_id ),
			$atts,
			__( "Print Recipe", "delicious-recipes" )
		);

		return $output;
	}

	/**
	 * Get HTML for pinterest button
	 * 
	 * @since 2.2.0
	 * 
	 * @param array $media        The recipe media image array which include 'url'
	 * @param string $url         The recipe permalink url
	 * @param string $description The description to display on pinterest board
	 * @param array|string $attributes   Additional html attributes: array('style' => 'color: red; font-size: inherit') or 
	 * 									 string 'style="color: red; font-size: inherit"'
	 * @return string
	 */
	public static function get_pinterest_button( $media, $url, $description = '', $attributes = '' ) {

		if ( ! delicious_recipes_enable_pinit_btn() ) {
			return;
		}

		if ( ! isset(  $media['url'] ) )
			return '';

		$PinterestClasses = implode( ' ', array( "post-pinit-button" ) );
		$pinitURL 		  = 'https://www.pinterest.com/pin/create/button/?url=' . esc_url( $url ) .'/&media='. esc_url( $media['url'] ) .'&description='. esc_html( $description ) .'';

		$atts = self::$helpers->render_attributes( $attributes );

		$output = sprintf(
			'<span class="%s">
	            <a data-pin-do="buttonPin" href="%s" data-pin-custom="true" %s>
					<img src="%s" alt="pinit">
	            </a>
	        </span>',
	        esc_attr( $PinterestClasses ),
	        esc_url( $pinitURL ),
			$atts,
			esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ).'/assets/images/pinit-sm.png'
		);

		return $output;
	}

	/**
     * Get recipe card image size name
     * 
     * @since 2.6.3
     * 
     * @return object
     */
    public static function get_recipe_image_size( $sizes, $src ) {
    	if ( is_array( $sizes ) && ! empty( $sizes ) ) {
    		foreach ( $sizes as $size_name => $size_attrs ) {
    			if ( isset( $size_attrs['url'] ) ) {
    				if ( $size_attrs['url'] === $src ) {
    					$size = $size_name;
    				}
    			}
    			elseif ( isset( $size_attrs['source_url'] ) ) {
    				if ( $size_attrs['source_url'] === $src ) {
    					$size = $size_name;
    				}
    			}
    		}
    	}

    	if ( ! isset( $size ) ) {
    		$size = 'full';
    	}

    	return $size;
    }

    /**
     * Get image url by specified $size
     * 
     * @since 2.6.3
     * 
     * @param  string|number $image_id    	The image id to get url
     * @param  string $size        			The specific image size
     * @param  array  $image_sizes 			Available image sizes for specified image id
     * @return string              			The image url
     */
    public static function get_image_size_url( $image_id, $size = 'full', $image_sizes = array() ) {
    	if ( isset( $image_sizes[ $size ] ) ) {
    		if ( isset( $image_sizes[ $size ]['url'] ) ) {
	    		$image_url = $image_sizes[ $size ]['url'];
    		} elseif ( isset( $image_sizes[ $size ]['source_url'] ) ) {
	    		$image_url = $image_sizes[ $size ]['source_url'];
    		}
    	}

    	if ( function_exists( 'fly_get_attachment_image_src' ) ) {
    		$thumb = fly_get_attachment_image_src( $image_id, $size );

    		if ( $thumb ) {
    			$image_url = isset( $thumb[0] ) ? $thumb[0] : $thumb['src'];
    		}
    	}

    	if ( !isset( $image_url ) ) {
    		$thumb = wp_get_attachment_image_src( $image_id, $size );
    		$image_url = $thumb && isset( $thumb[0] ) ? $thumb[0] : '';
    	}

    	return $image_url;
    }

    /**
     * Check whether a url is a blob url.
     * 
     * @since 2.6.3
     *
     * @param string $url 	The URL.
     *
     * @return boolean 		Is the url a blob url?
     */
    public static function is_blob_URL( $url ) {
    	if ( ! is_string( $url ) || empty( $url ) ) {
    		return false;
    	}
		return strpos( $url, 'blob:' ) === 0;
	}
}