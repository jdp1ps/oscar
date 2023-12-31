# version 2.2.x

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](../update.md)

## Import des activités

La configuration pour la conversion CSV > JSON a changé. Les données du projet permet maintenant de spécifier l'intitulé du projet (*label*) : 

```php
<?php
//
return [
    // Acronyme / Intitulé du projet de l'activité
    0 =>    "project.acronym",
    3 =>    "project.label",
    // ...
];

```

## Paramètres personnels

Cet écran permet de :

 - voir les informations personnelles si elles sont disponibles,
 - configurer la fréquence des rappels


## Moteur de recherche Elastic Search

Dans cette version, la recherche dans les activités a été découplée et propose maintenant 2 stratégies possibles :
 
 - **Zend Lucene**, une implémentation du moteur Lucene en PHP (Technologie utilisée à l'origine)
 - **Elastic Search**, Un server dédié à la recherche *plain text** (Plus performant et rapide)
 
Cette évolution nécessite d'adapter la configuration dans Oscar, tout est détaillé dans le fichier [Configuration](../configuration.md#recherche-des-activit%C3%A9s)  

## Type d'organisation

Les types d'organisation sont maintenant gérés avec un objet `OrganizationType`.

Le types d'oganisation sont administrables via le privilège **ORGANIZATIONTYPE_MANAGE**. Une interface dédié permet de configurer les types disponibles dans Oscar.

Le connector `organization` permet de fixer le type via une chaîne de caractères. Les anciennes valeurs étaient des entiers correspondant à l'index d'une valeur fixée *en dure* dans le code source de Oscar. Les valeur s'affichent telles quelles. Un script en ligne de commande permet de convertir les valeurs entières du champ type avec les valeurs du tableau : 

```bash
php public/index.php oscar patch typeOrganisationsUpdate 
```

## Notifications

Le système de notification est maintenant étendu aux Jalons/Versements. Il concerne les personnes associées à une activité/projet directement et les personnes ayant un rôle "principal" dans une organisation impliquées dans une activité/projet avec un rôle principal

 - Une notification est ajoutée automatiquement au jour J du jalon/versement
 - Lors de la création d'un jalon, les notifications des personnes sont automatiquement ajoutées
 - Lors de la suppression d'un jalon, les notifications des personnes sont automatiquement supprimées
 - Lors d'un changement d'état d'un jalon (réalisé/non-réalisé), les notifications sont recalculées
 - Lors de l'édition d'un Jalon (Type), les notifications sont purgées, puis reconstruites
 - Les jalons en retard génèrent automatiquement une notification lors d'une régénération
 - Les administrateurs disposent d'une vue "Notification" pour voir et regénérer les notifications d'une personne (Depuis la fiche personne)
 
 Un utilitaire en ligne de commandes permet de générer les notifications : 
 
 ```bash
 php public/index.php oscar notifications:generate <idactivity|all>
```


## Améliorations / Mise à jour

 - Le moteur de recherche des organisations permet maintenant de chercher via les connectors en utilisant la syntaxe `nom_connecteur=valeur_connector`
 - Lors de l'execution de la **synchronisation** des personnes/organisations en ligne de commande, le rapport de rendu préfixe les lignes du rapport avec les 3 premières lettres du statut de la ligne : (err : erreur, war : alerte, inf : info, not : notice).
 - Ajout de la branche dans les informations de version ainsi qu'une commande `php public/index.php oscar version` pour visualiser la version en ligne de commande
 - Le **Connecteur Organisation** prends en charge les champs : siret, email, url et type (chaîne de charactère facultative)
 - Licence ajoutée au dépôt
 - Les sélecteurs de date des versements/jalons ont maintenant des flèches latérales en vue semaine pour faire défiler les mois
 - Documentation technique : Mise à jour du document de mise à jour
 

## Administration/Maintenance/Privilège

 - Le privilège Maintenance > Peut notifier ... a été étendu à la gestion des notifications d'une personne. Elle débloque l'accès à la gestion des notifications d'une personne
 - Ajout d'un privilège dans la catégorie "Administration" pour la gestion des types d'activités
 - La commande `php public/index.php oscar test:config` permet de tester la configuration du système/oscar
 - La commande `php public/index.php oscar test:mailing` permet de tester la configuration pour l'envoi de mail
 - La commande `php public/index.php oscar auth:list` permet de voir la liste des authentifications actives
 - Les dépendances PHP peuvent maintenant être géré avec [Composer](https://getcomposer.org)
  

 
## Fix

 - Les réquètes de récupération des Personnes/Organisations utilisées par les connectors sont plus précises (Fix : RM#12611)
 - Les liens d'activités dans l'UI des notifications refonctionnent
 - Erreur d'enregistrement dans le versement sans date
 - Les utilisateurs ne disposant pas des droits d'édition sur les versements ne voient plus les boutons de gestion des versements
 - Type d'activitès : Le merge fonctionne à nouveau (à documenter)
 - Liste des organisations : Les icônes ont été mises à jour

## Dev

 - MailingService : Mailer qui sera utilisé par Oscar pour distribuer des mails
 - Ajout des notifications au compilateur JS
 - Le calcule des privilèges - personnes - activités utilise un cache pour le calcul des notifications
 - Refactoring : Service de traitement des Jalons
 - Refactoring : Service de traitement des notifications
 - Refactoring : Service Person (affectation aux activités, projet, organisation) EN COURS
 - Refactoring : Service Organization (affectation aux activités, projet) EN COURS