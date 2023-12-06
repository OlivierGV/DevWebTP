<?php
function test123()
{
    print("debut");
    require_once(get_template_directory() . '/functions.php');

    $var = mes_variables();

    # Les variables #
    $url_perso = trouver_lien_page(13);
    $url_guerre = trouver_lien_page(0); //à changer
    $url_periode = trouver_lien_page(19);
    $liens_possibles = [$url_perso, $url_periode];
    $trouver_table = [
        $url_periode => $var[1],
        $url_guerre => $var[2],
        $url_perso => $var[3]
    ];
    $iteration = 0;

    # Connaitre le URL courant #
    $wpdb = $var[0];
    $url_courrant = home_url(add_query_arg(array(), $wpdb->request));

    # Connaître la table #
    if (array_key_exists($url_courrant, $trouver_table)) {
        print($trouver_table[$url_courrant]);
        print("test");
    } else {
        print("Aucune table trouvée");
    }

}
add_action('montrer', 'test123');
