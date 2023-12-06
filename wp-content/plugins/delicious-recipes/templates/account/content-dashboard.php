<?php
/**
 * User dashboard template.
 *
 * @package Delicious_Recipes
 */

// Print Errors / Notices.
delicious_recipes_print_notices();

// Set User.
$dr_current_user        = $args['current_user'];
$user_dashboard_menus   = delicious_recipes_sort_array_by_priority( $args['dashboard_menus'] );
$current_tab            = ! empty( $_GET['tab'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : delicious_recipes_array_key_first( $user_dashboard_menus );  // @since 1.4.4
$dr_dashboard_permalink = delicious_recipes_get_page_permalink_by_id( delicious_recipes_get_dashboard_page_id() );

?>
<section class="dr-user-dashboard-wrapper">
	<div class="dr-ud__grid">
		<div class="dr-ud__sidebar">
			<div class="dr-ud__sidebar-inner">
				<div class="dr-ud__user-wrap">
					<div class="dr-ud__user-img user-male">
						<div class="img-holder">
							<!-- imge goes here -->
							<?php echo get_avatar( $dr_current_user->user_email ); ?>
						</div>
					</div>
					<div class="dr-ud__user-text">
						<div class="dr-ud__user-name"><?php echo esc_html( $dr_current_user->display_name ); ?></div>
						<a href="<?php echo esc_url( wp_logout_url( $dr_dashboard_permalink ) ); ?>" class="user-logout"><span><?php esc_html_e( 'Log Out', 'delicious-recipes' ); ?></span></a>
					</div>
				</div>
				<div class="dr-ud__user-menu">
					<ul class="user-menu">
						<?php
						$i = 0;
						foreach ( $user_dashboard_menus as $key => $user_dashboard_menu ) :

							$classes      = $user_dashboard_menu['menu_class'];
							$active_class = ( ! $current_tab && 0 === $i ) || ( $current_tab && false !== strpos( $classes, $current_tab ) ) ? 'active' : ''; // @since 1.4.4
							?>
							<li class="<?php echo esc_attr( $classes ); ?> <?php echo esc_attr( $active_class ); ?>">
								<a href="<?php echo esc_url( add_query_arg( 'tab', $key, $dr_dashboard_permalink ) ); ?>">
									<?php
									if ( isset( $user_dashboard_menu['svg'] ) && ! empty( $user_dashboard_menu['svg'] ) ) :
										echo $user_dashboard_menu['svg'];
									endif;
									?>
									<span><?php echo esc_html( $user_dashboard_menu['menu_title'] ); ?></span>
								</a>
							</li>
							<?php
							$i++;
						endforeach;
						?>
					</ul>
				</div>
			</div>
			<div class="dr-ud__sidebar-footer">
				<button type="button" class="dr-sidebar-toggle-btn">
					<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44">
						<g id="Group_6066" data-name="Group 6066" transform="translate(-305 -896)">
							<circle id="Ellipse_104" data-name="Ellipse 104" cx="22" cy="22" r="22" transform="translate(305 896)" fill="#fff" />
							<g id="Group_5744" data-name="Group 5744" transform="translate(316.075 912.102)">
								<line id="Line_93" data-name="Line 93" x2="16.008" transform="translate(6.478 0)" fill="none" stroke="#374757" stroke-linecap="round" stroke-width="2" opacity="0.3" />
								<line id="Line_94" data-name="Line 94" x2="20.221" transform="translate(2.265 5.898)" fill="none" stroke="#374757" stroke-linecap="round" stroke-width="2" opacity="0.3" />
								<line id="Line_95" data-name="Line 95" x2="16.008" transform="translate(6.478 11.796)" fill="none" stroke="#374757" stroke-linecap="round" stroke-width="2" opacity="0.3" />
								<path id="Path_30634" data-name="Path 30634" d="M0,0,3.685,3.686,7.369,0" transform="translate(3.686 2.37) rotate(90)" fill="none" stroke="#374757" stroke-linecap="round" stroke-width="2" opacity="0.3" />
							</g>
						</g>
					</svg>
				</button>
			</div>
		</div>
		<div class="dr-ud__main dr-ud__main-wrap">
			<div class="dr-ud__main-inner dr-ud-<?php echo esc_attr( $current_tab ); ?>-content active">
				<?php
				if ( ! empty( $user_dashboard_menus[ $current_tab ]['menu_content_cb'] ) ) {
					call_user_func( $user_dashboard_menus[ $current_tab ]['menu_content_cb'], $args );
				}
				?>
			</div>
		</div>
	</div>
</section>
<?php
