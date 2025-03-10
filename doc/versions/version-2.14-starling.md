# OSCAR 2.14.x "Starling"

## Contenu de cette mise à jour

### Fonctionnel
 - **Fiche activité** : 
   - Refonte de l'interface
   - Aperçu des membres au survole
   - Copié/collé des membres d'une fiche à l'autre
   - Copié/collé des partenaires d'une fiche à l'autre
   - Ajout du champ **part gestionnaire**
   - Ajout du champ **date du début des négociations**
   - **Système de note**
   - **Système de mots-clef**
   - **Gestion des avenants** (Montant, date de fin, membre, partenaire) 
   - **Système de verrouillage de l'activité** : Vérrouille les informations générales, les membres et les partenaires. Un privilège a été ajouté pour autoriser le verrouillage/déverrouillage manuel.
   
 - **Recherche dans les activités** : 
   - Ajout d'un filtre *ayant des avenants*
   - Ajout d'un filtre *ayant le mots-clef*
   - Ajout d'un filtre *date du début des négociations*
   - Champs exportés : Ajout **date de début des négociations**
   - Champs exportés : Ajout **Avenants** (Nombre d'avenants)
   - Champs exportés : Ajout **Description** (de l'activité)
   - Champs exportés : Ajout **Mots-clefs** (de l'activité)
 
- **Système d'épingle** permettant d'épingler une activité. Les activités épinglées sont accessibles via un menu d'accès disponible depuis la fiche activité et l'accueil oscar

- **Administration**
  - Les **logs de l'application** sont disponibles depuis le menu Administration / Configuration et maintenance / Logs de l'application
  - Dans la partie **authentification**, les comptes problématiques sont affichés en rouge (cas de doublon), et en orange (compte sans personne associée).

- **Comportement général**
  - Les personnes authentifiées, mais ayant des doublons pour la personne associée, feront face à un message d'erreur (Votre compte n'est associé à aucune personne). Les administrateurs pourront aller dans le menu Administration pour identifier le problème. 

- **Mode privé** (Beta) Option permettant de flouter les informations sensibles (depuis la fiche activité pour le moment)

### Technique
 - Le script `oscar-update.sh` permet de faire une mise à jour Oscar automatique (Plus de détail dans la section suivante)
 - Commande `php bin/oscar.php check:config` enrichie
 - Ajout de l'option `--mapping` pour diagnostiquer les règles d'indexation sur 
   - Les activités `php bin/oscar.php activity:search --mapping` : 
   - Les personnes `php bin/oscar.php persons:search --mapping` : 
   - Les organisations `php bin/oscar.php organizations:search --mapping` 
 - Refonte technique de la vue **Déclaration des heures** 
 - Refonte technique de la vue **Authentification** 
 - Refonte technique de la vue **Fiche activité**
 - Ajout de nombreux logs sur divers points clefs de l'application pour faciliter l'identification de problème technique/logique
 - La commande `php bin/oscar.php infos` génère un JSON avec les informations précise de la version installée, et visible depuis le pied de page de l'application

### Problèmes connus
Cette version intègre de nombreux changements au niveau de l'interface. Certains sont visibles (comme sur la fiche activité), d'autres non. Ces changements visent à migrer les interfaces graphiques vers **Vue2**/**Vite**. Il reste encore plusieurs écrans à migrer, dont la recherche d'activité qui donnera lieu à des ajouts fonctionnels.

Lors de la mise à jour de l'application, il est possible que *l'interface ne réagisse pas*, si c'est le cas, invitez les utilisateurs à forcer le rafraichissement du cache navigateur ; En effet, Oscar est une application web, et dans ce sens, le navigateur garde en mémoire les éléments d'interface de l'ancienne version.

## Mise en place technique

Un script *Bash* est disponible dans les fichiers Oscar pour déclencher la procédure de mise à jour

```bash
. oscar-update.sh
```

Contenu du script :

```bash
#!/bin/bash

COMPOSER=composer
PHP=php

echo "############################################### COMPOSER UPDATE"
## mise à jour des dépendances PHP
${COMPOSER} install

echo "############################################### MODEL UPDATE"
## Mise à jour du modèle (si besoin)
php vendor/bin/doctrine-module orm:schema-tool:update --force --complete


echo "############################################### CHECK PRIVILEGES"
## Mise à jour des privilèges
php bin/oscar.php check:privileges

echo "############################################### UPDATE INFOS"
## écriture du JSON de version
php bin/oscar.php infos
```

