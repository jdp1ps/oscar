# version 2.5.x (Juin 2018)

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)


## Générale

 - L'interface des paramètres utilisateur a été améliorée, elle affiche les créneaux automatiques, le bouton enregistrement se verrouille automatiquement si besoin et les messages d'informations ont été mis à jour.
 - La constitution des corpus pour l'indexeur prend maintenant en compte les disciplines des activités
 - Les notifications peuvent maintenant être envoyées par emails [Documentation des notifications](doc/notifications.md)


## Administration 

 - La documentation technique (précédure d'installation, configuration ElasticSearch sous Debian) a été mise à jour
 - L'interface d'administration des types de jalon a été mise à jour, une option permet maintenant de supprimer les types de jalon non-utilisés
 - Les privilèges ont été mis à jour
 - Le fichier de configuration d'exemple a été mis à jour
 - Par défaut, la configuration des connecteurs est vide pour éviter les erreurs d'affichage si rien n'est configuré dans la configuration local.php
 - Une interface d'administration a été ajoutée pour gérer les disciplines (ainsi que des privilèges d'accès)
 - Un paramètre a été ajouté pour forcer l'envoi de notification par mail [Documentation des notifications](doc/notifications.md)
 - La liste des commandes de *Oscar Command Line* a été mise à jour
 - Mise à jour de la commande `test:mailer` [voir documentation du mailer](doc/mailer.md)
 - Ajout d'un paramètre **urlAbsolute** pour la génération des mails (requis pour générer des mails avec des liens vers l'application)
 - Ajout d'un système de distribution de mail **FILE**, il permet de générer des mails physiques pour tester la mise en forme des mails à partir de fichier EML standards, permettra par la suite de concevoir un système de *spool*
 - Le mailer dispose maintenant d'un paramètre d'exclusion. Quand l'envoi des mails est désactivée, ce paramètre permet de configurer des mails d'exception vers lesquels seront distribués les courriels lors des phases de test ou pour les versions en préprod.
 - Une commande a été ajoutée pour déclencher la distribution des notifications par email si besoin
 

## Importation des données
 
 - Le convertisseur CSV > JSON pour les activités de recherche autorise maintenant une configuration "avancée" pour extraire des données multiples séparées par un séparateur (Voir documentation)
 - Le test d'existence des organisations test maintenant UNIQUEMENT le nom complet pour éviter le rejet d'organisation aux noms courts proches.


## Developpement

 - Mise à jour des outils de compilations JS/CSS
 - Fix : Bug des tests unitaires sur certaines dates en raison d'un décalage d'une heure entre la version de développement et les tests automatiques côté Gitlab
 - Factorisation du code de l'interface calendrier pour les déclarants
 - Mise à jour des dépendances Node/Js  


## Fix/Up : 
 - L'option soumettre la semaine fonctionne à nouveau
 - Cliquer sur une notification contenant un lien marque la notification comme lue puis redirige vers le lien
 - Erreur de requête lors de la récupération des projets d'une personne
 - Le fichier de route a été nettoyé de certaines routes inutilisées
 - La clef de configuration des connecteurs est maintenant correctement reprise dans les données des personnes/organisations synchronisées
 - Suppression de la jointure PROJET-DISCIPLINE toujours présente en BDD mais supprimée dans l'usage (Les disciplines sont gérées côté activité)
 - Le système de regroupement des piles de notifications est plus efficace
 - Fix : Notifications dupliquées
 - Fix : Le message d'erreur de conversion CSV>JSON lorsque une clef est manquante est plus clair
 - Fix : Erreur lors de la génération d'index pour LUCENE
 
 
 
## En cours / Labs
 - Système de génération de document Word
 - API de recherche dans les activités ouvert
 - Mise à niveau du moteur de recherche des personnes
 - Mise à niveau du moteur de recherche des organisations