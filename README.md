# wp_citationsAleatoires
Une extension Wordpress permettant d'enregistrer et d'afficher des citations aléatoires dans des articles

# Fonctionnalités
<ul>
  <li>Crée la table $prefix_quotes lors de l'activation de l'extension et supprime la table lors de la désinstallation</li>
  <li>Ajoute un menu CRUD dans le back office de Wordpress</li>
  <li>Génère un shortcode</li>
  <li>Affiche les citations enregistrées aléatoirement</li>
  <li>Utilise les constantes Wordpress et des requêtes préparées $wpdb->prepare pour la sécurité</li>
  <li>Contient un fichier CSS pour la mise en page du menu back office</li>
</ul>

# Shortcode
[maCitationAleatoire]
