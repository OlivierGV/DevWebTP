<?php
# Mes variables #
global $wpdb;

# Mes actions #
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
add_action('switch_theme', 'activer_plugin');
add_action('mon-footer', 'mon_footer');
add_shortcode('bouton_redirection', 'shortcode_bouton');

# Mes fonctions #
if(!function_exists('activer_plugin'))
{
    # Fonction pour activer mon plugin lorsqu'on active le thème #
    function activer_plugin() {
        is_wp_error(activate_plugin('mon-plugin/functions.php'));
    }
}
if(!function_exists('my_theme_enqueue_styles'))
{
    # Fonction par défaut Wordpress (style) #
    function my_theme_enqueue_styles() {
        $parenthandle = 'blossom-recipe';
        $theme        = wp_get_theme();
        wp_enqueue_style( $parenthandle,
            get_template_directory_uri() . '/style.css',
            array(),
            $theme->parent()->get( 'Version' )
        );
        wp_enqueue_style( 'child-style',
            get_stylesheet_uri(),
            array( $parenthandle )
        );
    }
}
if(!function_exists('shortcode_bouton'))
{
    function shortcode_bouton($atts, $content = null)
    {
        $att = shortcode_atts(
            array(
                'lien' => '#',
            ),
            $atts,
            'bouton_redirection'
        );

        return '<a href="' . esc_url($atts['lien']) . '" class="bouton-redirection">' . esc_html($content) . '</a>';
    }
}
if( ! function_exists( 'blossom_recipe_primary_menu_fallback' ) ) :
    # Modification de la fonction du thème parent qui affiche le menu
    function blossom_recipe_primary_menu_fallback(){
        $url_propos = trouver_lien_page(15);
        $url_rejoindre = trouver_lien_page(17);
        $mes_menus = ['Accueil', 'À propos', 'Nous rejoindre'];
        $mes_liens = [home_url(), $url_propos, $url_rejoindre];
        $iteration = 0;
            echo '<ul id="primary-menu" class="nav-menu">';
            foreach($mes_menus as $element)
            {
                echo '<li><a href="' . ($mes_liens[$iteration]) . '">' . esc_html__( '' . $element . '', 'blossom-recipe' ) . '</a></li>';
                $iteration++;
            }
            echo '</ul>';
    }
    endif;

if(!function_exists('trouver_lien_page'))
{
    function trouver_lien_page($id_page)
    {
        $poste = get_post($id_page);
        $titre = $poste->post_name;
        $url = (home_url() . "/" . $titre);
        return $url;
    }
}
?>