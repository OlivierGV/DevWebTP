<?php
if(!function_exists('supprimer_post'))
{
    # Supprimer la valeur choisie dans le post #
    function supprimer_post()
    {
        $var = mes_variables();
        $wpdb = $var[0];

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mon_nonce']) && wp_verify_nonce($_POST['mon_nonce'], 'action_name')) {
            if (isset($_POST["form_1"])) {
                $valeur = $_POST["choix"];
                supprimer_sql($valeur, $var[1], $wpdb);

            } elseif (isset($_POST["form_2"])) {
                $valeur = $_POST["choix"];
                supprimer_sql($valeur, $var[2], $wpdb);

            } elseif (isset($_POST["form_3"])) {
                $valeur = $_POST["choix"];
                supprimer_sql($valeur, $var[3], $wpdb);
            }
        }
    }
}
if(!function_exists('supprimer_sql'))
{
    # Supprimer une donnée #
    function supprimer_sql($valeur, $table, $wpdb)
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
if(!function_exists('montrer_donnees_suppression'))
{
    # Afficher les données d'une table #
    function montrer_donnees_suppression($wpdb, $table)
    {
        ?>
        <select class="select" id="choix" name="choix" >
        <?php
        $resultats = $wpdb->get_results(
            $wpdb->prepare( "SELECT id, nom FROM $table" )
            # Code modifié de : https://developer.wordpress.org/reference/classes/wpdb/get_results/ #
        );

        foreach($resultats as $donnees){
            ?>
            <option value="<?php _e(esc_attr($donnees->id)); ?>"><?php _e(esc_attr($donnees->nom )); ?></option>
            <?php
        }
        ?>
        </select>
        <?php
    }
}
if(!function_exists('style_php'))
{
    # Code CSS pour le formulaire #
    function style_php()
    {
        ?>
        <style>
            .select{
                width:150px;
                font-style: italic;
            }
            .select:hover{
                width:170px;
            }
        </style>
        <?php
    }
}
if(!function_exists('declarer_formulaire_suppression'))
{
    # Créer le formulaire #
    function declarer_formulaire_suppression($nom, $wpdb, $var, $varIndex)
    {
        ?>
        <form method="post">
            <h3><?php _e($nom) ?></h3>
            <?php wp_nonce_field('action_name', 'mon_nonce'); ?>
            <?php
            montrer_donnees_suppression($wpdb, $var);
            ?>
            <input type="submit" name="form_<?php echo $varIndex; ?>" value="Supprimer donnée">
        </form>
        <?php
    }
}
?>