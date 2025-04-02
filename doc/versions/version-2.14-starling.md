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
 
 - **Feuille de temps**
   - Refonte de l'interface de saisie (migration technique)
   - L'écran de synthèse personnel a été enrichi avec différents sous-totaux 
- **Système d'épingle** permettant d'épingler une activité. Les activités épinglées sont accessibles via un menu d'accès disponible depuis la fiche activité et l'accueil oscar

- **Administration**
  - Les **logs de l'application** sont disponibles depuis le menu Administration / Configuration et maintenance / Logs de l'application
  - Dans la partie **authentification**, les comptes problématiques sont affichés en rouge (cas de doublon), et en orange (compte sans personne associée).

- **Comportement général**
  - Les personnes authentifiées, mais ayant des doublons pour la personne associée, feront face à un message d'erreur (Votre compte n'est associé à aucune personne). Les administrateurs pourront aller dans le menu Administration pour identifier le problème. 

- **Mode privé** (Beta) Option permettant de flouter les informations sensibles (depuis la fiche activité pour le moment)

### Technique
 - **Version 7.x** de Elasticsearch requise (Le passage en version 7 depuis 6 est documenté plus bas)
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
 - Une version docker est en cours de développement (On préviendra)

### Problèmes connus

#### Fiche activité (écran vide)
Cette version intègre de nombreux changements au niveau de l'interface. Certains sont visibles (comme sur la fiche activité), d'autres non. Ces changements visent à migrer les interfaces graphiques vers **Vue2**/**Vite**. Il reste encore plusieurs écrans à migrer, dont la recherche d'activité qui donnera lieu à des ajouts fonctionnels.

Lors de la mise à jour de l'application, il est possible que *l'interface ne réagisse pas*, si c'est le cas, invitez les utilisateurs à forcer le rafraichissement du cache navigateur ; En effet, Oscar est une application web, et dans ce sens, le navigateur garde en mémoire les éléments d'interface de l'ancienne version.

#### Recherche des personnes/organisations en erreur

Vérifier la version de Elasticsearch (Voir plus bas)



## Mise en place technique

### Passage en STARLING

> Vérifier que vote elasticsearch est bien en version 7.x
> ```bash
> curl http://localhost:9200
> ```
> Si ça n'est pas le cas, passez Oscar en maintenance le temps de mettre à niveau elasticsearch

Basculez sur la branche "starling"

```bash
# Actualisation du dépôt
git fetch

# Bascule sur la branche "starling"
git checkout starling
git fetch
```

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

#### Mise à niveau Elasticsearch (6 > 7)

Vérifié la version de elasticsearch installée : 

> le numéro de version dans la champ **version.number** doit être **7.xx.xx**

```bash
curl http://localhost:9200

>
{
  "name" : "woscar-pp",
  "cluster_name" : "elasticsearch",
  "cluster_uuid" : "acnHEqDORMCb8JbHoZexIg",
  "version" : {
    "number" : "7.17.28", <<<<<<<<<<<<<<<<< ICI
    "build_flavor" : "default",
    "build_type" : "deb",
    "build_hash" : "139cb5a961d8de68b8e02c45cc47f5289a3623af",
    "build_date" : "2025-02-20T09:05:31.349013687Z",
    "build_snapshot" : false,
    "lucene_version" : "8.11.3",
    "minimum_wire_compatibility_version" : "6.8.0",
    "minimum_index_compatibility_version" : "6.0.0-beta1"
  },
  "tagline" : "You Know, for Search"
}
```

Si vous êtes par exemple en version 6.xxxxx

Commencez par désinstaller l'ancienne version : 

```bash
# On coupe le service
service elasticsearch stop
apt remove elasticsearch --purge

# Supprimer l'ancien Elasticsearch des sources
# !!! A adapter à votre façon d'administrer vous sources
rm /etc/apt/source.list.d/elastic-6.x.list

# Supprimer l'index de recherche (la version 6 n'est pas compatible avec la 7)
rm -Rf /var/lib/elasticsearch/nodes

# Suppression ok
apt update
```

Puis installer la nouvelle version

```bash
# Source : documentation officielle
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elasticsearch-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/elasticsearch-keyring.gpg] https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-7.x.list
apt update
apt install elasticsearch

# Réactivation du service
systemctl enable elasticsearch.service
systemctl start elasticsearch
```

Enfin, relancez la construction des index

```bash
# depuis la dossier d'installation oscar
php bin/oscar.php activity:search-rebuild
php bin/oscar.php organizations:search-rebuild
php bin/oscar.php persons:search-rebuild
```