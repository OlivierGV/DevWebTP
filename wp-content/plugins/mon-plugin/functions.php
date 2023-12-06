<?php
/*
 * Plugin Name: mon-plugin
 * Description: Pour le travail long en Développement Web 2
 * Version: 0.0.1
 * Author: Olivier Godon-Vandal
 * Author URI: https://github.com/OlivierGV/DevWebTP.git
 */
require_once(plugin_dir_path(__FILE__) . 'fonctions_menu_admin.php');
# Source du code : https://www.php.net/manual/en/function.require-once.php #

###################################################################################################
## SÉCURITÉ #######################################################################################
###################################################################################################

if(!defined('ABSPATH'))
{
	exit;
}

###################################################################################################
## VARIABLES ######################################################################################
###################################################################################################

if(!function_exists('mes_variables'))
{
    function mes_variables()
    {
        global $wpdb;
        $prefix = $wpdb->prefix . "mon_plugin_";
        $table_periode = $prefix . "periode_historique";
        $table_guerre = $prefix . "guerre_historique";
        $table_personnage = $prefix . "personnage_historique";
        $charset_collate = $wpdb->get_charset_collate();

        $mes_variables = [$wpdb, $table_periode, $table_guerre, $table_personnage, $charset_collate];
        return $mes_variables;
    }
}

###################################################################################################
## LES FONCTIONS DU PLUGIN ########################################################################
###################################################################################################

if(!function_exists('mon_plugin_creer_table'))
{
    # Créer les tables de ma base de données #
    function mon_plugin_creer_table()
    {
        # Variables #
        $var = mes_variables();
        # Créer mes tables #
        mon_plugin_creer_table_periode($var[0], $var[1], $var[4]);
        mon_plugin_creer_table_guerre($var[0], $var[2], $var[1], $var[4]);
        mon_plugin_creer_table_personnage($var[0], $var[3], $var[1], $var[4]);
    }
}
if(!function_exists('mon_plugin_supprimer_table'))
{
    # Supprimer les tables de ma base de données #
    function mon_plugin_supprimer_table()
    {
        # Variables #
        $var = mes_variables();
        # Requête #
        $requete = $var[0]->query("DROP TABLE IF EXISTS $var[2], $var[3]");
        # Suite requête #
        if($requete)
        {
            $var[0]->query("DROP TABLE IF EXISTS $var[1]");
        }
    }
}
# Mes autres fonctions #
if(!function_exists('mon_plugin_creer_table_periode'))
{
    # Créer la table pour les périodes #
    function mon_plugin_creer_table_periode($wpdb, $table_periode, $charset_collate)
    {
        # Requête #
        $sql = "CREATE TABLE IF NOT EXISTS $table_periode (
            id INT AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            annee_debut VARCHAR(20) NOT NULL,
            annee_fin VARCHAR(20) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        # Commit #
        mon_plugin_commit_sql($sql);

        # Remplir #
        mon_plugin_remplir_table_periode($wpdb, $table_periode);
    }
}
if(!function_exists('mon_plugin_remplir_table_periode'))
{
    # Remplir la table pour les périodes #
    function mon_plugin_remplir_table_periode($wpdb, $table_periode)
    {
        $mes_insert = [
            ['Rome Antique', '-753', '476'],
            ['Le Moyen Âge', '476', '1492'],
            ['Époque Moderne', '1492', '1792']
        ];
        foreach ($mes_insert as $insert) {
            $wpdb->insert($table_periode, array(
                'nom' => $insert[0],
                'annee_debut' => $insert[1],
                'annee_fin' => $insert[2],
            ));
        }
    }
}
if(!function_exists('mon_plugin_creer_table_guerre'))
{
    # Créer la table pour les guerres #
    function mon_plugin_creer_table_guerre($wpdb, $table_guerre, $table_periode, $charset_collate)
    {
        # Requête #
        $sql = "CREATE TABLE IF NOT EXISTS $table_guerre (
            id INT AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            periode_historique INT NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (periode_historique) REFERENCES $table_periode(id) ON DELETE CASCADE
        ) $charset_collate;";

        # Commit #
        mon_plugin_commit_sql($sql);

        # Remplir #
        mon_plugin_remplir_table_guerre($wpdb, $table_guerre);
    }
}
if(!function_exists('mon_plugin_remplir_table_guerre'))
{
    # Remplir la table pour les guerres #
    function mon_plugin_remplir_table_guerre($wpdb, $table_guerre)
    {
        $mes_insert = [
            ['Guerre des Gaules', 'Cette guerre a mené la conquête de la Gaule par les Romains et  renforcé la position de César à Rome.', 1],
            ['Guerre des Samnites', 'Une série de guerre entre Rome et les Samnites, une ancienne population italienne.', 1],
            ['Première croisade', 'Guerre visant à libérer Jérusalem et la Terre sainte du contrôle musulman.', 2],
            ['Guerre de Cent Ans', 'La Guerre de Cent Ans a été un conflit prolongé entre l\'Anglette et la France', 2],
            ['Guerre de Trente Ans', 'Le conflit le plus dévastateur de l\'Europe, impliquant de nombreuses puissances européennes.', 3],
            ['Guerre de Sept Ans', 'La Guerre de Sept Ans a été un conflit mondial impliquant les principales puissances européennes et leurs colonies.', 3]
        ];
        foreach ($mes_insert as $insert) {
            $wpdb->insert($table_guerre, array(
                'nom' => $insert[0],
                'description' => $insert[1],
                'periode_historique' => $insert[2]
            ));
        }
    }
}
if(!function_exists('mon_plugin_creer_table_personnage'))
{
    # Créer la table personnage #
    function mon_plugin_creer_table_personnage($wpdb, $table_personnage, $table_periode, $charset_collate)
    {
        # Requête #
        $sql = "CREATE TABLE IF NOT EXISTS $table_personnage (
            id INT AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            prenom VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            periode_historique INT NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (periode_historique) REFERENCES $table_periode(id) ON DELETE CASCADE
        ) $charset_collate;";

        # Commit #
        mon_plugin_commit_sql($sql);

        # Remplir #
        mon_plugin_remplir_table_personnage($wpdb, $table_personnage);
    }
}
if(!function_exists('mon_plugin_remplir_table_personnage'))
{
    # Remplir la table pour les personnages #
    function mon_plugin_remplir_table_personnage($wpdb, $table_personnage)
    {
        $mes_insert = [
            ['César', 'Jules', 'Général militaire et homme politique romain, il a joué un rôle clé dans la transition de la République romaine.', 1],
            ['Barca', 'Hannibal', 'Général carthaginois célèbre pour ses tactiques audacieuses pendant la Deuxième Guerre punique, notamment la traversée des Alpes avec des éléphants.', 1],
            ['Coeur de Lion', 'Richard', 'Roi d\'Angleterre et chef de la Troisième croisade, il est devenu une figure légendaire de la chevalerie.', 2],
            ['D\'Arc', 'Jeanne', 'Héroïne de la guerre de Cent Ans, elle a joué un rôle crucial dans la défense d\'Orléans et l\'élévation de Charles VII au trône de France.', 2],
            ['De Richelieu', 'Cardinal', 'Homme d\'État français, il a été le principal conseiller du roi Louis XIII, jouant un rôle central dans la politique.', 3],
            ['de Prusse', 'Frédéric II', 'Roi de Prusse, également connu sous le nom de Frédéric le Grand, il a été un leader militaire brillant et a contribué à transformer la Prusse en une grande puissance européenne.', 3]
        ];
        foreach ($mes_insert as $insert) {
            $wpdb->insert($table_personnage, array(
                'nom' => $insert[0],
                'prenom' => $insert[1],
                'description' => $insert[2],
                'periode_historique' => $insert[3]
            ));
        }
    }
}
if(!function_exists('mon_plugin_probleme_insertion'))
{
    # Notifier l'administrateur en cas de problème #
    function mon_plugin_probleme_insertion()
    {
        $info_plugin = get_plugin_data( __FILE__ );
        $nom_plugin = $info_plugin['Name'];
        ?>
        <div class="notice notice-error">
            <p><?php _e( $nom_plugin . ': Problème à l\'insertion des tables.' ); ?></p>
        </div>
        <?php
    }
}
if(!function_exists('ajout_menu_dashboard'))
{
    # Ajouter un menu dans le dashboard #
    function ajouter_menu()
    {
        add_menu_page(
            __( 'Custom Menu Title', 'textdomain' ),
            'Gérer ma base de données',
            'manage_options',
            'lien_vers_ma_page',
            'lien_vers_ma_page',
            'dashicons-database',
            6
        );
        ajout_sub_menu();
        modification_sub_menu();
        supprimer_sub_menu();
    }
}
if(!function_exists('ajout_sub_menu'))
{
    # Ajouter les sous-menus #
    function ajout_sub_menu()
    {
        add_submenu_page(
            'lien_vers_ma_page',
            __( 'Ajouter des données', 'textdomain' ),
            __( 'Ajouter des données', 'textdomain' ),
            'manage_options',
            'ajouter-des-donnees',
            'ajouter_php',
            '2'
        );
    }
}
if(!function_exists('modification_sub_menu'))
{
    # Ajouter les sous-menus #
    function modification_sub_menu()
    {
        add_submenu_page(
            'lien_vers_ma_page',
            __( 'Modifier mes données', 'textdomain' ),
            __( 'Modifier mes données', 'textdomain' ),
            'manage_options',
            'modifier-des-donnees',
            'modifier_php',
            '3'
        );
    }
}
if(!function_exists('supprimer_sub_menu'))
{
    # Ajouter les sous-menus #
    function supprimer_sub_menu()
    {
        add_submenu_page(
            'lien_vers_ma_page',
            __( 'Supprimer des données', 'textdomain' ),
            __( 'Supprimer des données', 'textdomain' ),
            'manage_options',
            'supprimer-des-donnees',
            'supprimer_php',
            '4'
        );
    }
}
if(!function_exists('lien_vers_ma_page'))
{
    function lien_vers_ma_page()
    {
        $url_admin = "admin.php?page="
        ?>
        <style>
            .nos_choix, .choix{
                display: flex;
                flex-direction: column;
                max-width: 300px;
            }
            .choix {
                background-color: white;
                border-color: lightgray;
                border-style: solid;
                border-width: 1px;
                padding: 10px;
            }
            .choix a {
                text-align: center;
                text-transform: uppercase;
                text-decoration: none;
                color: white;
                border-style: solid;
                border-width: 1px;
                padding: 10px;
                margin: 10px;
                background-color: black;
            }
            .choix a:hover{
                background-color: white;
                color: black;
                font-weight: bold;
            }
        </style>
        <div class="nos_choix">
            <h3>Gérer votre base de données.</h3>
            <div class="choix">
                <a href="<?php echo($url_admin) ?>ajouter-des-donnees">Ajouter des données</a>
                <a href="<?php echo($url_admin) ?>modifier-des-donnees">Modifier des données</a>
                <a href="<?php echo($url_admin) ?>supprimer-des-donnees">Supprimer des données</a>
            </div>
        <div>
        <?php
    }
}
if(!function_exists('supprimer_php'))
{
    # La page de suppression #
    function supprimer_php()
    {
        # Variables #
        $var = mes_variables();
        # Pour des raisons de lisibilité, je redéclare un wpdb #
        $wpdb = $var[0];
        $tables = ["Périodes historiques", "Guerres historiques", "Personnages historiques"];
        $varIndex = 1;

        # Formulaires #
        style_php();
        foreach($tables as $table)
        {
            declarer_formulaire_suppression($table, $wpdb, $var[$varIndex], $varIndex);
            $varIndex++;
        }
        //Réagir en fonction des POST
        supprimer_post();
    }
}

if(!function_exists('css_notice'))
{
    # Code CSS pour les notifications dans le menu admin #
    function css_notice()
    {
        ?>
        <style>
            .notice {
                height: auto;
                margin: 10px;
            }
        </style>
        <?php
    }
}
if(!function_exists('mon_plugin_commit_sql'))
{
    # Faire requête SQL #
    function mon_plugin_commit_sql($sql)
    {
        require_once(ABSPATH ."wp-admin/includes/upgrade.php");
        dbDelta($sql);
    }
}
if(!function_exists('montrer_personnage'))
{
    # Afficher les personnages dans des cellules #
    function montrer_personnage()
    {
        # Variables #
        $var = mes_variables();
        $wpdb = $var[0];
        $iteration = 0;
        # Chercher contenu table #
        $resultats_perso = select_table($var[3], $wpdb);

        # Montrer contenu #
        ?>
        <div class="mes-cellules">
            <?php
            foreach($resultats_perso as $perso){
            $image = trouver_image($perso->nom);
            ?>
            <div class="cellule">
                <div class="mon-image-contenu">
                    <!-- une image -->
                    <img class="mon-image-contenu" src="<?php echo($image) ?>">
                </div>
                <div class="epoque">
                    <?php
                        _e($perso->prenom . " " . $perso->nom);
                    ?>
                </div>
                <div class="description"">
                    <?php
                        _e($perso->description . "; ");
                    ?>
                </div>
            </div>
        <?php
        $iteration++;
        }
        ?>
        </div>
        <?php
    }
}
if(!function_exists('montrer_periode'))
{
    # Afficher les personnages dans des cellules #
    function montrer_periode()
    {
        // Trouver l'URL
        // en fonction de l'URL, changer table, et champs div

        # Variables #
        $var = mes_variables();
        $wpdb = $var[0];
        # Chercher contenu table #
        $resultat_periode = select_table($var[1], $wpdb);

        # Montrer contenu #
        ?>
        <div class="mes-cellules">
            <?php
            foreach($resultat_periode as $periode){
            $image = trouver_image($periode->nom);
            ?>
            <div class="cellule">
                <div class="mon-image-contenu">
                    <!-- une image -->
                    <img class="mon-image-contenu" src="<?php echo($image) ?>">
                </div>
                <div class="epoque">
                    <?php
                        _e($periode->nom);
                    ?>
                </div>
                <div class="description"">
                    <?php
                        _e($periode->annee_debut . " à " . $periode->annee_fin);
                    ?>
                </div>
            </div>
        <?php
        }
        ?>
        </div>
        <?php
    }
}
if(!function_exists('trouver_image'))
{
    # Fonction pour associer une image à un nom #
    function trouver_image($nom)
    {
        $image = [
            # Code : https://www.php.net/manual/en/function.array-keys.php#:~:text=array_keys()%20returns%20the%20keys,from%20the%20array%20are%20returned. #
            'César' => '/mon-plugin/images/personnages/cesar.jpg',
            'Barca' => '/mon-plugin/images/personnages/hannibal.jpg',
            'Coeur de Lion' => '/mon-plugin/images/personnages/richard.png',
            'D\'Arc' => '/mon-plugin/images/personnages/jeanne.jpg',
            'De Richelieu' => '/mon-plugin/images/personnages/cardinal.jpg',
            'de Prusse' => '/mon-plugin/images/personnages/frederic.jpg',
            'Rome Antique' => '/mon-plugin/images/periodes/rome_antique.jpg',
            'Le Moyen Âge' => '/mon-plugin/images/periodes/moyen_age.jpg',
            'Époque Moderne' => '/mon-plugin/images/periodes/epoque_contemporaine.jpg',
            'Guerre des Gaules' => '/mon-plugin/images/guerres/gaules.jpg',
            'Guerre des Samnites' => '/mon-plugin/images/guerres/samnites.jpg',
            'Première croisade' => '/mon-plugin/images/guerres/premiere_croisade.png',
            'Guerre de Cent Ans' => '/mon-plugin/images/guerres/30ans.jpg',
            'Guerre de Trente Ans' => '/mon-plugin/images/guerres/30ans.jpg',
            'Guerre de Sept Ans' => '/mon-plugin/images/guerres/7ans.jpg'
        ];

        if (array_key_exists($nom, $image)) {
            return plugins_url($image[$nom]);
        }
        else {
            return plugins_url('/mon-plugin/images/personnages/default.jpg');
        }
    }
}
if(!function_exists('select_table'))
{
    # Retourner l'information de la table saisie #
    function select_table($table, $wpdb)
    {
        $var = mes_variables();
        if($table == $var[1])
        {
            $resultat = $wpdb->get_results(
                $wpdb->prepare( "SELECT nom, annee_debut, annee_fin FROM " . $table )
                # Code modifié : https://developer.wordpress.org/reference/classes/wpdb/get_results/ #
            );
        }
        if($table == $var[2])
        {
            $resultat = $wpdb->get_results(
                $wpdb->prepare( "SELECT nom, description description FROM " . $table )
                # Code modifié : https://developer.wordpress.org/reference/classes/wpdb/get_results/ #
            );
        }
        if($table == $var[3])
        {
            $resultat = $wpdb->get_results(
                $wpdb->prepare( "SELECT nom, prenom, description FROM " . $table )
                # Code modifié : https://developer.wordpress.org/reference/classes/wpdb/get_results/ #
            );
        }
        return $resultat;
    }
}
if(!function_exists('modifier_php'))
{
    # La page de suppression #
    function modifier_php()
    {
        # Variables #
        $var = mes_variables();
        # Pour des raisons de lisibilité, je redéclare un wpdb #
        $wpdb = $var[0];
        $tables = ["Périodes historiques", "Guerres historiques", "Personnages historiques"];
        $varIndex = 1;

        # Formulaires #
        style_php();
        css_table();

        # Quand on reload la page, on vérifie si le précédent "form" a été submit #
        $periode_submit = isset($_POST['submit']);

        # Si oui, afficher le vrai formulaire #
        if ($periode_submit) {
            formulaire_modification();
        }
        else {
            ?>
            <form method="post">
            <?php
            # Afficher les données #
            foreach ($tables as $table) {
                declarer_donnees($table, $wpdb, $var[$varIndex]);
                $varIndex++;
            }
            ?>
            <br>
                <input type="submit" name="submit" value="Modifier une donnée"/>
            </form>
            <?php
        }
    }
}
if(!function_exists('declarer_donnees'))
{
    function declarer_donnees($nom, $wpdb, $var)
    {
        ?>
        <h3><?php _e($nom) ?></h3>
        <?php
        montrer_donnees_modif($wpdb, $var);
        ?>
        <?php
    }
}
if(!function_exists('modifier_sql'))
{
    # Supprimer une donnée #
    function modifier_sql($valeur, $table, $wpdb)
    {
        # Importer #
        css_notice();
        # SQL #
        $sql = $wpdb->delete( $table, array( 'id' => $valeur ) );
        if($sql){
            ?>
            <div class="notice notice-success">
                <b>Vous avez supprimé une donnée avec succès.</b>
                <br>
                Assurez-vous de rafraîchir la page pour mettre à jour les données affichées.
            </div>
            <?php
            # CODE : https://www.w3schools.com/howto/howto_js_alert.asp #
            header("Location: /wp-admin/admin.php?page=books-shortcode-ref");
        }
        else
        {
            ?>
            <br>
            <div class="notice notice-error">
                <b>La valeur transmise ne répond pas à nos critères.</b>
                <br>
                - Assurez-vous que la donnée choisie figure encore dans la base de données.
                <br>
                - Rafraîchissez la page si vous voyez des anciennes données.
            </div>
            <?php
        }
    }
}
if (!function_exists('montrer_donnees_modif')) {
    # Afficher les données d'une table #
    function montrer_donnees_modif($wpdb, $table)
    {
        # Mes variables #
        $resultats = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table"));
        $noms_colonne = $wpdb->get_results("DESCRIBE $table");
        ?>
        <table>
            <tr>
                <?php
                foreach ($noms_colonne as $colonne){
                    ?>
                    <th>
                        <?php echo esc_html($colonne->Field); ?>
                    </th>
                    <?php
                }
                ?>
            </tr>
            <?php
            foreach ($resultats as $rangee){
            ?>
                <tr>
                    <?php
                    foreach ($noms_colonne as $colonne) {
                        ?>
                        <td><?php echo esc_html($rangee->{$colonne->Field}); ?></td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
}
if(!function_exists('css_table'))
{
    function css_table()
    {
        ?>
            <style>
                table {
                    color: black;
                    border-color: lightgray;
                    border-width: 1px;
                    border-style: solid;
                    text-align: justify;
                    min-width: 760px;
                }
                th {
                    background-color: white;
                    padding: 10px;
                    width: 200px;
                    max-width: 200px;
                }
                td {
                    border-color: white;
                    border-width: 1px;
                    border-style: solid;
                    max-width: 200px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                tr:hover {
                    background-color: lightgray;
                    cursor: pointer;
                }
            </style>
        <?php
    }
}
if(!function_exists('formulaire_modification'))
{
    function formulaire_modification()
    {
        # Mes variables #
        $var = mes_variables();
        $tables = [$var[1], $var[2], $var[3]];
        $iteration = 0;
        ?>
        <h3>Modifier les données</h3>
        <form class="form-modif" method="post">
            <label for="table">Choisir la table:</label>
            <select class ="mon-input-form" name="select" id="select">
                <?php
                foreach($tables as $table)
                {
                    ?>
                    <option value="<?php echo($iteration) ?>" required><?php echo ($table) ?></option>
                    <?php
                    $iteration++;
                }
                ?>
            </select>
            <br>
            <label for="champ">Indiquez le champ:</label>
            <input class ="mon-input-form" type="text" name="champ" id="champ" required>
            <br>
            <label for="champ">Indiquez l'ID:</label>
            <input class ="mon-input-form" type="number" name="id" id="id" required>
            <br>
            <label for="champ">Indiquez la nouvelle valeur:</label>
            <input class ="mon-input-form" type="text" name="valeur" id="valeur" required>
            <br>
            <input class ="mon-input-form" type="submit" name="form" value="Modifier la donnée">
        </form>
        <?php
    }
}

###################################################################################################
## ACTIONS ########################################################################################
###################################################################################################
register_activation_hook(__FILE__,'mon_plugin_creer_table');
register_deactivation_hook( __FILE__,'mon_plugin_supprimer_table');
add_action( 'admin_menu', 'ajouter_menu' );
add_action('montrer_personnage', 'montrer_personnage');
add_action('montrer_periode', 'montrer_periode');


function test123()
{
    # Importer
    $var = mes_variables();

    # Les variables #
    $url_periode = trouver_lien_page(19);
    $url_guerre = trouver_lien_page(21);
    $url_perso = trouver_lien_page(13);
    $wpdb = $var[0];
    $trouver_table = [
        $url_periode => $var[1],
        $url_guerre => $var[2],
        $url_perso => $var[3]
    ];
    $iteration_epoque = 1;
    $iteration_desc = 1;
    $trouver = false;
    # Connaitre le URL courant #
    global $wp;
    $url_courrant = home_url( $wp->request );
    # Connaître la table #
    if (array_key_exists($url_courrant, $trouver_table)) {
        $table_actuelle = ($trouver_table[$url_courrant]);
    }
    # Trouver le contenu de la table #
    $resultat_table = select_table($table_actuelle, $wpdb);
        # Montrer contenu #
        ?>
        <div class="mes-cellules">
            <?php
            foreach($resultat_table as $resultat){
                # Associer les résultats à des balises #
                $mes_balises_epoque = [
                    $resultat->nom,
                    $resultat->nom,
                    $resultat->prenom . " " . $resultat->nom
                ];
                $mes_balises_desc = [
                    $resultat->annee_debut . " à " . $resultat->annee_fin,
                    $resultat->description,
                    $resultat->description
                ];
                # Trouver les images en fonction du nom donné #
                $image = trouver_image($resultat->nom);
                ?>
                <div class="cellule">
                    <div class="mon-image-contenu">
                        <img class="mon-image-contenu" src="<?php echo($image) ?>">
                    </div>
                    <div class="epoque">
                        <?php
                        afficher_info($mes_balises_epoque, $iteration_epoque, $trouver, $table_actuelle, $var);
                        ?>
                    </div>
                    <div class="description"">
                        <?php
                        afficher_info($mes_balises_desc, $iteration_desc, $trouver, $table_actuelle, $var);
                        ?>
                    </div>
                </div>
            <?php
        }
        ?>
        </div>
        <?php
}
if(!function_exists('afficher_info')){
    # Boucle qui permet d'afficher des données #
    function afficher_info($string, $iteration, $trouver, $table, $var){
        while(!$trouver){
            if($table == $var[$iteration]){
                echo($string[$iteration - 1]);
                $trouver = true;
            }
            else{
                $iteration++;
            }
        }
        $trouver = false;
    }
}
add_action('montrer', 'test123');

?>