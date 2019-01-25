# version 2.9 "Matrix"

## Appliquer cette mise à jour depuis Oscar 2.8

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)


## Demandes d'activités

La version 2.9 de Oscar introduit une nouvelle fonctionnalité : Les demandes d'activités. Elle propose d'activier via les privilèges d'autoriser des demandes d'activité. Ces demandes peuvent ensuite être validées ou refusées pour créer des activités de recherche.

Détails sur cette fonctionnalité : [Demande d'activités](doc/activity-request.md)

## Suppression de GrantSource

Le code a été purgé des références à un ancien système de classification des activités (Source). A priori, toutes les références ont été supprimées, la table **grantsource** est donc maintenant inutile. Vous pourrez la supprimer après avoir appliqué la mise à jour.


## UP / FIX

 - Fix : Bug d'affichage des arrondis dans les versements
 - Fix : La table/entités/références Grantsource ont été supprimées
 





