# version 2.11 "MacClane"

## Upgrade : Zend Framework 3

La version *MacClane* fait suite à la mise à jour précédente (Passage à PHP 7.3). Oscar est maintenant basé sur la version 3 du framework **Zend**. Malgrès de nombreuses vérifications, pensez à prévoir une phase de test pour vérifier que toutes les fonctionnalités sont bien opérationnelles sur votre système.

Cette mise à niveau permet de bénéficier des dernières mises à jour de sécurité des systèmes linux, et rend Oscar compatible avec Postgresql 10.

## Nouveautés

 - **Feuille de temps > déclarant** :  Modification du système de saisie des commentaires. Le déclarant peut maintenant saisir son commentaire au fil de l'eau depuis la vue mois. Cette modification permet de simplifier la gestion des commentaires, et de ne pas perdre les commentaires en cas d'annulation d'une déclaration.

 - **Export des activités > Champs calculés** : L'administrateur peut maintenant configurer des champs calculés pour les sorties Excel/CSV. Exemple disponible dans [Configurer les champs calculés dans les export OSCAR](doc/activities-export.md)

 - **Utilitaire en ligne de commande** : Les commandes Oscar (php public/index.php) ont commencé à être migrées progressivement vers le nouveau système de commande (basé sur le composant "Console" de Symfony, tel que recommandé par l'équipe Zend). La liste des commandes disponibles est accessible via la commande (php bin/oscar.php).

 - **Nouveau projet / nouvelle activité** : Le privilège permettant de créer des activités/projets était initialement réservé au niveau application (et donc effectif uniquement pour les rôles déterminés au sein de l'application). Ce privilège a maintenant été étendu aux rôles liès aux organisations afin de permettre une meilleure décentralisation de l'ajout de projet/activité. Pour les droits obtenus via l'organisation, une zone de saisie supplémentaire sera visible dans les formulaires pour y renseigner le rôle de l'organisation de rattachement (la liste des rôles sera limitée aux rôles donnant un accès NB: c'est cette affectation qui par la suite donnera accès à l'activié/projet créé).
 
 - **Gearman** Utilisation d'un serveur de gestion des tâches (**Gearman**) afin d'optimiser certaines opérations : Génération des notifications, planification de la reconstruction/optimisation des indices de recherche.
 
 - **Dépense** (expérimental) Les écrans et la connexion SIFAC sont toujours en cours, les accès aux écrans de synchronisation des dépenses basé sur le journal des pièces sont disponibles (Avec les privilèges associés). Propose pour le moment des données brute
 
 - **Écran de budget** (UI uniquement) Sera à préciser quand la partie dépense sera plus avancée
 
 - **Plan comptable** Écran de gestion du plan comptable à utiliser dans l'écran du budget
 
## Correctifs

 - Augmentation des résultats des recherches pour les organisations
 - Augmentation des résultats des recherches pour les personnes
