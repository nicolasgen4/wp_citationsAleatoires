<?php

/**
 * Plugin : Citation aléatoire
 * Désinstallation
 */

//Suppression de la table de données
if (!defined('WP_UNINSTALL_PLUGIN')) {
    http_response_code(403);
    die('Erreur 403 : Forbidden');
} else {
    global $wpdb;
    $table = $wpdb->prefix . 'quotes';
    $wpdb->prepare($wpdb->query("DROP TABLE IF EXISTS $table"));
}
