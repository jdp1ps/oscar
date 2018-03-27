# version 2.2.x

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)

## Type d'organisation

Les types d'organisation sont maintenant libres à la saisie et lors de la synchronisation. 

Le connector `organization` permet de fixer le type via une chaîne de caractère. Les anciennes valeurs étaient des entiers correspondant à l'index d'une valeur fixée *en dure* dans le code source de Oscar. Les valeur s'affichent telles quelles. Un script en ligne de commande permet de convertir les valeurs entières du champ type avec les valeurs du tableau : 

```bash
php public/index.php oscar patch typeOrganisationsUpdate 
```

## Notifications

Le système de notification est maintenant étendu aux Jalons/Versements. Il concerne les personnes associées à une activité/projet directement et les personnes ayant un rôle "principal" dans une organisation impliqué dans une activité/projet avec un rôle principal

 - Une notification est ajoutée automatiquement au jour J du jalon/versement
 - Lors de la création d'un jalon, les notifications des personnes sont automatiquement ajoutées
 - Lors de la suppression d'un jalon, les notifications des personnes sont automatiquement supprimées
 - Lors d'un changement d'état d'un jalon (réalisé/non-réalisé), les notifications sont recalculées
 - Lors de l'édition d'un Jalon (Type), les notifications sont purgées, puis reconstruitent
 - Les jalons en retard génèrent automatiquement une notification lors d'une régénération
 - Les administrateurs disposent d'une vue "Notification" pour voir et regénérer les notifications d'une personne (Depuis la fiche personne)
 
 Un utilitaire en ligne de commande permet de générer les notifications : 
 
 ```bash
 php public/index.php oscar notifications:generate <idactivity|all>
 ```


## Améliorations / Mise à jour

 - Lors de l'execution de la **synchronisation** des personnes/organisations en ligne de commande, le rapport de rendu préfixe les lignes du rapport avec les 3 premières lettres du status de la ligne : (err : erreur, war : alerte, inf : info, not : notice).
 - Ajout de la branche dans les informations de version ainsi qu'une commande `php public/index.php oscar version` pour visualiser la version en ligne de commande
 - Le **Connecteur Organisation** prends en charge les champs : siret, email, url et type (chaîne de charactère facultative)
 - Licence ajoutée au dépôt
 - Les selecteurs de date des versements/jalons ont maintenant des flèches latérales en vue semaine pour faire défiler les mois
 - Documentation technique : Mise à jour du document de mise à jour
 


## Administration/Maintenance/Privilège

 - Le privilège Maintenance > Peut notifier ... a été étendu à la gestion des notifications d'une personne. Elle débloque l'accès à la gestion des notifications d'une personne
 - Ajout d'un privilège dans la catégorie "Administration" pour la gestion des types d'activitès

 
## Fix

 - Les réquètes de récupération des Personnes/Organisations utilisées par les connectors sont plus précises (Fix : RM#12611)
 - Les liens d'activités dans l'UI des notifications refonctionnent
 - Erreur d'enregistrement dans le versement sans date
 - Les utilisateurs ne disposant pas des droits d'édition sur les versements ne voient plus les boutons de gestion des versements
 - Type d'activitès : Le merge fonctionne à nouveau (à documenter)
 - Liste des organisations : Les icônes ont été mis à jour

## Dev

 - Ajout des notifications au compilateur JS
 - Le calcule des privilèges - personnes - activités utilise un cache pour le calcule des notifications
 - Refactoring : Service de traitement des Jalons
 - Refactoring : Service de traitement des notifications
 - Refactoring : Service Person (affectation aux activités, projet, organisation) EN COURS
 - Refactoring : Service Organization (affectation aux activités, projet) EN COURS
 