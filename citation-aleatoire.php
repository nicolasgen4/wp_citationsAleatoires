<?php

/**
 * Plugin Name: Citation aléatoire
 * Description: Enregistrez des citations et leurs auteurs. Affichez-les en début d'article grâce au code court [maCitationAleatoire].
 * Requires PHP: 7.4
 * Author: Nicolas Loizeau
 * Author URI: https://nicolas.cciopenlab.fr
 * Version: 1.0.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

//Accès direct interdit
if (!defined('ABSPATH')) {
    http_response_code(403);
    die('Erreur 403 : Forbidden');
}

//Activation du plugin
register_activation_hook(__FILE__, 'creerTable');

//Ajouter le menu admin
add_action('admin_menu', 'adminCitations');

//Menu admin et ses sous-menus
function adminCitations()
{
    add_menu_page('Citations', 'Mes citations', 'manage_options', 'citation_plugin', 'listerCitations');
    add_submenu_page('citation_plugin', 'Ajouter une citation', 'Ajouter une citation', 'manage_options', 'ajouter_citation', 'ajouterCitation');
    wp_enqueue_style('style', '/wp-content/plugins/citation-aleatoire/styles/style.css');
}


//Création de la table dans la bdd
function creerTable()
{
    global $wpdb;
    $table = $wpdb->prefix . 'quotes';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
                quote_id int(11) NOT NULL AUTO_INCREMENT,
                quote_text text NOT NULL,
                quote_author varchar(255) NOT NULL,
                quote_modif datetime NULL,
                quote_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (quote_id))               
                $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


//Lister, modifier, supprimer en admin
function listerCitations()
{
    global $wpdb;
    $table = $wpdb->prefix . 'quotes';
    echo <<< ENTETE
    <section class="en-tete">
        <h1>Vos citations</h1>
    </section>
    ENTETE;

    //Supprimer une citation
    if (
        isset(
            $_GET['action'],
            $_GET['id']
        ) && $_GET['action'] == 'supprimer'
    ) {
        //Suppression en bdd
        $wpdb->prepare($wpdb->delete(
            $table,
            array('quote_id' => $_GET['id'])
        ));
        echo <<< MESSAGE
            <div class="resultat-action">
                <p>Citation supprimée avec succès</p>
            </div>
            MESSAGE;
    }

    //Modifier une citation
    if (
        isset(
            $_GET['action'],
            $_GET['id']
        ) && $_GET['action'] == 'modifier'
    ) {
        //Affichage du formulaire de modification
        $id = $_GET['id'];
        $citation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE quote_id = $id"));
        echo
        '<form action="admin.php?page=citation_plugin" method="post" class="modif-citation">
            <div class="champ">
                <label for="texteModif">Texte</label>
                <textarea id="texteModif" name="texteModif" required>' . htmlspecialchars($citation->quote_text) . '</textarea>
            </div>
            <div class="champ">
                <label for="auteurModif">Auteur</label>
                <input type="text" name="auteurModif" id="auteurModif" value="' . htmlspecialchars($citation->quote_author) . '" required>
            </div>
            <input type="hidden" name="idCitation" id="idCitation" value="' . htmlspecialchars($id) . '">
            <div class="boutons">
                <button type="submit" name="submit" class="button button-primary">
                    Enregistrer
                </button>
            </div>
        </form>';
    } elseif (
        //Vérification des données
        isset(
            $_POST['texteModif'],
            $_POST['auteurModif'],
            $_POST['idCitation']
        )
        && !empty($_POST['texteModif'])
        && !empty($_POST['auteurModif'])
        && !empty($_POST['idCitation'])
    ) {
        //Envoi en bdd
        date_default_timezone_set('Europe/Paris');
        $wpdb->prepare($wpdb->update(
            $table,
            array(
                'quote_text' => strip_tags(trim(stripslashes_deep($_POST['texteModif']))),
                'quote_author' => strip_tags(trim(stripslashes_deep($_POST['auteurModif']))),
                'quote_modif' => date("Y-m-d H:i:s")
            ),
            array('quote_id' => $_POST['idCitation'])
        ));
        echo <<< MESSAGE
            <div class="resultat-action">
                <p>Citation modifiée avec succès</p>
            </div>
            MESSAGE;
    }

    //Retourne les citations depuis la bdd
    $citations = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT $table.quote_id,
            $table.quote_text,
            $table.quote_author 
            FROM $table",
        )
    );
    //Affiche les citations dans l'admin
    echo '<div class="liste-citations">';
    foreach ($citations as $citation) {
        echo '<div class="citation">
                <figure>
                    <blockquote>
                        <q>"' . htmlspecialchars($citation->quote_text) . '"</q>
                    </blockquote>
                    <figcaption>' . htmlspecialchars($citation->quote_author) . '</figcaption>
                </figure>
                <div class="actions-citation">
                    <a href="admin.php?page=citation_plugin&action=modifier&id=' . htmlspecialchars($citation->quote_id) . '">Modifier</a>
                    <a href="admin.php?page=citation_plugin&action=supprimer&id=' . htmlspecialchars($citation->quote_id) . '">Supprimer</a>
                </div>
            </div>';
    }
    echo '</div>';
}

//Ajout en bdd
function ajouterCitation()
{
    global $wpdb;
    $table = $wpdb->prefix . 'quotes';
    echo <<< ENTETE
    <section class="en-tete">
        <h1>Ajoutez des citations aléatoires à vos articles</h1>
    </section>
    ENTETE;

    if (
        //Vérification du form
        isset(
            $_POST['texte'],
            $_POST['auteur']
        )
    ) {
        if (
            //Vérification champs
            !empty($_POST['texte'])
            && !empty($_POST['auteur'])
        ) {
            //Envoi en bdd
            $wpdb->prepare($wpdb->insert($table, array(
                'quote_text' => strip_tags(trim(stripslashes_deep($_POST['texte']))),
                'quote_author' => strip_tags(trim(stripslashes_deep($_POST['auteur']))),
            )));
            echo <<< MESSAGE
            <div id="message-citation">
                <p>Citation enregistrée avec succès</p>
            </div>
            MESSAGE;
        } else {
            //Erreur
            echo <<< MESSAGE
            <div id="message-citation">
                <p>Merci de compléter tous les champs.</p>
            </div>
            MESSAGE;
        }
    } else {
        echo <<< MESSAGE
        <div id="message-citation">
            <p>Choisissez une citation et son auteur
            </p>
        </div>
        MESSAGE;
    }
    //Formulaire d'ajout
    echo <<< FORM
    <form action="admin.php?page=ajouter_citation" method="post" class="form-citation">
        <div class="champ">
            <label for="texte">Texte</label>
            <textarea id="texte" name="texte" required></textarea>
        </div>
        <div class="champ">
            <label for="auteur">Auteur</label>
            <input type="text" name="auteur" id="auteur" required>
        </div>
        <div class="boutons">
            <button type="submit" name="submit" class="button button-primary">
                Enregistrer
            </button>
        </div>
    </form>
    FORM;
}

//Afficher les citations dans les articles
//Code court [maCitationAleatoire]
add_shortcode('maCitationAleatoire', 'citationAleatoire');

function citationAleatoire($content)
{
    add_filter('the_content', 'citationAleatoire');

    //Retourne les citations depuis la bdd
    global $wpdb;
    $table = $wpdb->prefix . 'quotes';
    $citations = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT $table.quote_id,
        $table.quote_text,
        $table.quote_author 
        FROM $table",
        )
    );
    //Affichage dans l'article
    if ($citations == true) {
        shuffle($citations);
        $chapeau =
            '<figure>
            <blockquote>
                <q>' . htmlspecialchars($citations[0]->quote_text) . '</q>
            </blockquote>
            <figcaption>' . htmlspecialchars($citations[0]->quote_author) . '</figcaption>
            </figure>';
        return $chapeau . $content;
    } else {
        return $content;
    }
}
