<?php
/**
 * Recipe main ingredients section.
 */
global $recipe;
$global_settings       = delicious_recipes_get_global_settings();
$showAdjustableServing = ! empty( $global_settings['showAdjustableServing'][0] ) && 'yes' === $global_settings['showAdjustableServing'][0];
$ingredientTitle       = isset( $recipe->ingredient_title ) ? $recipe->ingredient_title : __( 'Ingredients', 'delicious-recipes' );

if ( isset( $recipe->ingredients ) && ! empty( $recipe->ingredients ) ) :
	$servings_value = ! empty( $recipe->no_of_servings ) ? esc_attr( $recipe->no_of_servings ) : 1;
	?>
	<div class="dr-ingredients-list">
		<div class="dr-ingrd-title-wrap">
			<h3 class="dr-title"><?php echo esc_html( $ingredientTitle ); ?></h3>

			<?php if ( $showAdjustableServing ) { ?>
				<div class="dr-ingredients-scale" data-serving-value="<?php echo esc_attr( $servings_value ); ?>">
					<?php if ( ! empty( $global_settings['adjustableServingType'] ) && 'increment' === $global_settings['adjustableServingType'] ) { ?>
						<label for="select"><?php esc_html_e( 'Servings', 'delicious-recipes' ); ?></label>
						<input type="number" data-original="<?php echo esc_attr( $servings_value ); ?>" data-recipe="<?php echo esc_attr( $recipe->ID ); ?>" value="<?php echo esc_attr( $servings_value ); ?>" step="1" min="1" class="dr-scale-ingredients">
					<?php } else { ?>
						<label for="select"><?php esc_html_e( 'Scale', 'delicious-recipes' ); ?></label>
						<div class="scale-btn-wrapper">

							<button
								class=""
								data-scale="0.5"
								data-recipe="<?php echo esc_attr( $recipe->ID ); ?>"
								type="button">1/2x</button>
							<?php
							for ( $i = 1; $i < 4; $i++ ) {
								?>
								<button
									class="<?php echo 1 === $i ? 'active' : ''; ?>"
									data-scale="<?php echo esc_attr( $i ); ?>"
									data-recipe="<?php echo esc_attr( $recipe->ID ); ?>"
									type="button"><?php echo esc_html( "{$i}x" ); ?></button>
								<?php
							}
							?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>

		</div>
		<?php
		$ingredient_string_format = isset( $global_settings['ingredientStringFormat'] ) ? $global_settings['ingredientStringFormat'] : '{qty} {unit} {ingredient} {notes}';
		foreach ( $recipe->ingredients as $key => $ingre_section ) :
			$section_title = isset( $ingre_section['sectionTitle'] ) ? $ingre_section['sectionTitle'] : '';
			$ingre         = isset( $ingre_section['ingredients'] ) ? $ingre_section['ingredients'] : array();
			?>
			<h4 class="dr-title"><?php echo esc_html( $section_title ); ?></h4>
			<ul class="dr-unordered-list">
				<?php
				foreach ( $ingre as $ingre_key => $ingredient ) :

					$rand_key       = rand( 10, 10000 );
					$ingredient_qty = isset( $ingredient['quantity'] ) ? $ingredient['quantity'] : 0;
					$ingredient_qty = is_numeric( $ingredient_qty ) ? round( $ingredient_qty, 2 ) : $ingredient_qty;

					$ingredient_unit = isset( $ingredient['unit'] ) ? $ingredient['unit'] : '';
					$unit_text       = ! empty( $ingredient_unit ) ? delicious_recipes_get_unit_text( $ingredient_unit, $ingredient_qty ) : '';

					$ingredient_keys = array(
						'{qty}'        => isset( $ingredient['quantity'] ) ? '<span class="ingredient_quantity" data-original="' . $ingredient_qty . '" data-recipe="' . $recipe->ID . '">' . delicious_recipes_decorate_fraction( $ingredient_qty ) . '</span>' : '',
						'{unit}'       => $unit_text,
						'{ingredient}' => isset( $ingredient['ingredient'] ) ? $ingredient['ingredient'] : '',
						'{notes}'      => isset( $ingredient['notes'] ) && ! empty( $ingredient['notes'] ) ? '<span class="ingredient-notes" >(' . $ingredient['notes'] . ')</span>' : '',
					);
					$ingre_string    = str_replace( array_keys( $ingredient_keys ), $ingredient_keys, $ingredient_string_format );
					?>
					<li>
						<input type="checkbox" name="" value="" id="dr-ing-<?php echo esc_attr( $ingre_key ); ?>-<?php echo esc_attr( $rand_key ); ?>">
						<label for="dr-ing-<?php echo esc_attr( $ingre_key ); ?>-<?php echo esc_attr( $rand_key ); ?>"><?php echo wp_kses_post( $ingre_string ); ?></label>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
		endforeach;
		?>
	</div>
	<?php
endif;
