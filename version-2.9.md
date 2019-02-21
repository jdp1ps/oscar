# version 2.9 "Matrix"

## Appliquer cette mise à jour depuis Oscar 2.8

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)

## Déclaration des feuilles de temps

Un écran récapitulatif a été ajouté lors de l'envoi des feuilles de temps. Cet écran, en plus d'afficher une synthèse des créneaux déclarés propose de préciser pour chaques lignes de la déclarations un espace commentaire qui par défaut aggrège les commentaires renseigné sur les créneaux du même type.

## Demandes d'activités

La version 2.9 de Oscar introduit une nouvelle fonctionnalité : Les demandes d'activités. Elle propose d'activer via les privilèges d'autoriser des demandes d'activité. Ces demandes peuvent ensuite être validées ou refusées pour créer des activités de recherche.

Détails sur cette fonctionnalité : [Demande d'activités](doc/activity-request.md)

## Suppression de GrantSource

Le code a été purgé des références à un ancien système de classification des activités (Source). A priori, toutes les références ont été supprimées, la table **grantsource** est donc maintenant inutile. Vous pourrez la supprimer après avoir appliqué la mise à jour.

## Export des versements (config)

La version initiale de l'export des versements proposait les rôles de organisations "en dur" ainsi qu'un séparateur de données multiples '$$' imposé. Dans cette version, les rôles des organisations, et le séparateur de chaîne sont configurable dans le fichier **local.php**. Un méchanisme similaire aux organisations a également été ajouté pour exporter les personnes. 

## Améliorations
 
 - Export des activités : L'UI permet maintenant de délectionner/déselectionner les champs à exporter par groupe.
 - Database : La fonction de numérotation automatique a été optimisée ([Numérotation automatique](doc/numerotation.md)) @JulienDary
 - Admin : Refonte de l'interface de gestion des types de documents et **ajout d'un privilège** pour les gérer
 - Feuille de temps > déclaration : L'interface pour sélectionner les types de créneaux a été adaptée pour mieux gérer l'affichage d'un grand nombre de lot.
 - Admin : Certaines listes dans les écrans de configuration des nomenclatures sont maintenant affichées dans l'ordre alphabétique
 - Gestion des lots : le bouton enregistrer n'est réctif que si le code est renseigné
 - DOC : Une requète Postgresql a été ajoutée dans la documentation pour automatiser les changements de formalisme des numérotations
 - Recherche dans les activités : La mise en page des résultats de la recherche a été améliorée pour mieux distinguer l'état des activités.
 
## Fix

 - Synchro Organisation JSON : Suppression d'une notice sur le type si il est absent de la source
 - LOG : L'ajout, la suppression et la modification des jalons ont été ajoutés au tracelog
 - TYPO : Dans les descriptions des privilèges
 - La liste des organisations est affichée dans l'ordre alphabétique
 - Bug d'affichage des arrondis dans les versements
 - La table/entités/références Grantsource ont été supprimées
 - Fiche activité > Lot de travail : Les minutes ne s'affichent plus en décimale d'heure
 - Feuille de temps Excel > Reprends les commentaires envoyés lors de l'envoi de la déclaration
