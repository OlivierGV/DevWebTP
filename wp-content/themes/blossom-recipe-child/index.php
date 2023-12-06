<!DOCTYPE html>
<?php
# Mes variables #
$mon_texte = __("En apprendre davantage");
$url_perso = trouver_lien_page(13);
$url_guerre = trouver_lien_page(21);
$url_periode = trouver_lien_page(19);

/* Appeler les différentes parties */
get_header();
?>
<div class="contenu_principal">
    <div class="separateur"></div>
    <div class="periode contenu">
        <div class="image-periode"></div>
        <div class="texte" id="periode">
            <h1><?php _e("Les événements historiques"); ?></h1>
            <p><?php _e("Les périodes historiques, comme les pages d'un livre immuable, racontent l'histoire évolutive de l'humanité.
            Chacune d'entre elles est une tranche temporelle empreinte de particularités, de bouleversements et de caractéristiques
            uniques. L'Antiquité, berceau des grandes civilisations méditerranéennes, a vu naître des empires majestueux tels que
            celui des Romains, dont l'héritage résonne encore dans notre société moderne."); ?></p>
        </div>
    </div>
    <?php
        $mon_texte = __("En apprendre davantage");
        echo do_shortcode('[bouton_redirection lien="' . $url_periode . '"]' . $mon_texte . '[/bouton_redirection]');
    ?>
    <br>
    <div class="separateur"></div>
    <div class="personnage contenu">
        <div class="texte" id="perso">
            <h1><?php _e("Les personnages historiques"); ?></h1>
            <p><?php _e("Les personnages historiques sont les artisans de la trame temporelle qui tisse le récit de l'humanité. Leurs vies,
            marquées par des exploits, des choix et des idéaux, transcendent le simple cours du temps pour devenir des empreintes
            indélébiles dans le livre de l'histoire. Certains, tels que Jules César, Cléopâtre, ou encore Napoléon Bonaparte, ont façonné
            des empires et ont laissé une empreinte indéniable sur le monde."); ?></p>
        </div>
        <div class="image-personnage"></div>
    </div>
    <?php
        echo do_shortcode('[bouton_redirection lien="' . $url_perso . '"]' . $mon_texte . '[/bouton_redirection]');
    ?>
    <br>
    <div class="separateur"></div>
    <div class="guerre contenu">
        <div class="image-guerre"></div>
        <div class="texte" id="guerre">
            <h1><?php _e("Les guerres historiques"); ?> </h1>
            <p><?php _e("Les guerres historiques jalonnent le parcours tumultueux de l'humanité, souvent laissant derrière elles des cicatrices
            profondes et des leçons amères. Chaque conflit, qu'il s'agisse des guerres antiques, médiévales, modernes ou contemporaines,
            a façonné le cours de l'histoire, redessinant les frontières, influençant les sociétés et remodelant la géopolitique mondiale."); ?></p>
        </div>
    </div>
    <?php
        echo do_shortcode('[bouton_redirection lien="' . $url_guerre . '"]' . $mon_texte . '[/bouton_redirection]');
    ?>
    <br>
</div>
<?php
wp_footer();
get_footer();
?>
