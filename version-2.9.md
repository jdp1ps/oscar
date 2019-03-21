# version 2.9 "Matrix"

## Appliquer cette mise à jour depuis Oscar 2.8

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)



## Déclaration des feuilles de temps

Un écran récapitulatif a été ajouté lors de l'envoi des feuilles de temps. Cet écran, en plus d'afficher une synthèse des créneaux déclarés propose de préciser pour chaque ligne de la déclaration un espace commentaire qui par défaut agrège les commentaires renseignés sur les créneaux du même type.



## Demandes d'activités

La version 2.9 de Oscar introduit une nouvelle fonctionnalité : Les demandes d'activités. Elle propose d'activer via les privilèges d'autoriser des demandes d'activité. Ces demandes peuvent ensuite être validées ou refusées pour créer des activités de recherche.

Détails sur cette fonctionnalité : [Demande d'activités](doc/activity-request.md)



## Suppression de GrantSource

Le code a été purgé des références à un ancien système de classification des activités (Source). A priori, toutes les références ont été supprimées, la table **grantsource** est donc maintenant inutile. Vous pourrez la supprimer après avoir appliqué la mise à jour.



## Export des versements (config)

La version initiale de l'export des versements proposait les rôles des organisations "en dur" ainsi qu'un séparateur de données multiples '$$' imposé. Dans cette version, les rôles des organisations, et le séparateur de chaîne sont configurables dans le fichier **local.php**. Un mécanisme similaire aux organisations a également été ajouté pour exporter les personnes. 



## Numérotation libre

Dans l'écran d'édition des activités, la zone de numérotation "libre" permettait à l'utilisateur d'ajouter des numéros, cette information était composée d'un intitulé (ex : ANR) et d'une valeur (Le numéro ANR). 

La création de l'intitulé était laissée à la discretion de l'utilisateur au moment de la saisie du numéro.

A l'usage, les utilisateurs ne respectaient pas forcement un formalisme strict (Majuscule, minuscule), et le nombre des intitulés s'est amplifié sans raison (ANR, anr, N° ANR, numéro ANR, numero ANR, etc...). 

Les intitulés des numérotations doivent maintenant être configurés via l'interface d'administration. Ce système s'appuie sur le mécanisme de "paramètres éditables" ajouté récemment dans Oscar.

Le système étant rétrocompatible, une UI dans la gestion des numérotations propose la liste des activités ayant des numérotations non référencées pour pouvoir modifier les activités ou ajouter des numérotations manquantes.

La recherche par numérotation a été étendue pour augmenter les chances de trouver des résultats (ex : ANR= retourne les activités ayant un numéro ANR)
 
Les numérotations qualifiées sont disponibles dans l'export des activités

> En cas d'**erreur d'accès au fichier oscar-editable.yml**, vous pouvez créer manuellement le fichier : 
> ```bash
> touch config/autoload/oscar-editable.yml
>```
>
> Puis donner les droits d'accès en écriture : 
>
> ```bash
> chmod 777 config/autoload/oscar-editable.yml
> ```


## Feuille de temps

### Interface principale : 

L'interface pour sélectionner les types de créneaux a été adaptée pour mieux gérer l'affichage d'un grand nombre de lot.

Des options d'affichage ont été ajoutées à l'interface de saisie. Pour le moment elles permettent de modifier les couleurs d'affichage des projets pour aider à les distinguer quand une personne est associée à plusieurs projets de recherche.

### Importation

L'interface d'importation a été modifiée pour faciliter son utilisation par les déclarants. Des aides ont été ajoutées pour assister l'utilisateur dans les réglages de l'importation.

Un système de détection des fichiers ICS est en place. Il permet aux déclarants de réimporter un même fichier pour une période donnée en proposant (par défaut) une option pour actualiser les données déjà importées (ou les conserver si besoin), cela évite la création de doublon lors d'importations successives d'un même fichier.

L'interface d'importation mémorise maintenant localement (dans le navigateur) les préférences d'association des créneaux issus du calendrier.



## Documents générés (experimental)

Voir [Documents générés](doc/generated-documents.md)

## Autres améliorations
 
 - Export des activités : L'UI permet maintenant de sélectionner/déselectionner les champs à exporter par groupe.
 - Database : La fonction de numérotation automatique a été optimisée ([Numérotation automatique](doc/numerotation.md)) @JulienDary
 - Admin : Refonte de l'interface de gestion des types de documents et **ajout d'un privilège** pour les gérer
 - Admin : Certaines listes dans les écrans de configuration des nomenclatures sont maintenant affichées dans l'ordre alphabétique
 - Gestion des lots : le bouton enregistrer n'est réactif que si le code est renseigné
 - DOC : Une requète Postgresql a été ajoutée dans la documentation pour automatiser les changements de formalisme des numérotations
 - Recherche dans les activités : La mise en page des résultats de la recherche a été améliorée pour mieux distinguer l'état des activités.
 - UP : Le système d'invalidation des feuilles de temps s'applique maintenant à la période entière plutôt qu'à un type de créneau. Les messages d'erreur en page d'accueil sont donc maintenant regroupés par période.
 - Le schéma de la base a été ajouté dans la documentation technique [Schéma de la BDD](./doc/schema_bdd.png)
 - Les "paramètres éditables" sont un mécanisme qui permettra de gérer certains paramètres de configuration directement depuis l'interface. Ce dispositif sera dédié à la gestion des options "facultatives" et va créer une fichier **config/autoload/oscar-editable.yml** non versionné et absent par défaut (il arrive que l'absence du fichier déclenche une erreur).
 
## Fix

 - Synchro Organisation JSON : Suppression d'une notice sur le type si il est absent de la source
 - LOG : L'ajout, la suppression et la modification des jalons ont été ajoutés au tracelog
 - TYPO : Dans les descriptions des privilèges
 - La liste des organisations est affichée dans l'ordre alphabétique
 - Bug d'affichage des arrondis dans les versements
 - Les tables/entités/références Grantsource ont été supprimées
 - Fiche activité > Lot de travail : Les minutes ne s'affichent plus en décimale d'heure
 - Feuille de temps Excel > Reprend les commentaires envoyés lors de l'envoi de la déclaration
