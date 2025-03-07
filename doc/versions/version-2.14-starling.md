# OSCAR 2.14.x "Starling"

## Contenu de cette mise à jour

### Fonctionnel
 - **Fiche activité** : 
   - Refonte de l'interface
   - Aperçu des membres au survole
   - Copié/collé des membres d'une fiche à l'autre
   - Copié/collé des partenaires d'une fiche à l'autre
   - Ajout du champ **date du début des négociations**
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
 
- **Système d'épingle** permettant d'épingler une activité. Les activités épinglées sont accessibles via un menu d'accès disponible depuis la fiche activité et l'accueil oscar

- **Mode privé** (Beta) Option permettant de flouter les informations sensibles (depuis la fiche activité pour le moment)

### Technique
 - Commande `php bin/oscar.php check:config` enrichie
 - Ajout de l'option `--mapping` pour diagnostiquer les règles d'indexation sur 
   - Les activités `php bin/oscar.php activity:search --mapping` : 
   - Les personnes `php bin/oscar.php persons:search --mapping` : 
   - Les organisations `php bin/oscar.php organizations:search --mapping` : 

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

