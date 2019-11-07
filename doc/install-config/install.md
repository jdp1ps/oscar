# INSTALLATION OSCAR

> L'installation Oscar a été testée sous système **Debian** et les différentes mise à jour ne sont testées que sur ce système.

---

## Prérequis

 - Système linux (Debian 10 "Buster")
 - Serveur web (Apache2)
 - PHP 7.3+ (support LDAP, Postgresql, mcrypt, intl, DOM/XML, mbstring, gd, zip)
 - Postgresql 10+ (version 9 supportée)
 - Annuaire LDAP (supann)
 - Elasticsearch

Matériel (Recommandation)
 - CPU 2 Core 2.4 Ghz
 - RAM 4 Go
 - Espace disque 20G (Application seule, hors documents/base de données)
 
---
 
## Installation du système

### Mise à jour du système

On commence par mettre le système à jour.

```bash
# Mise à jour des paquets
apt-get update

## Mise à jour
apt-get upgrade
```

### Proxy (Si besoin)

Si besoin, configurer le proxy :

```bash
export http_proxy=http://proxy.unicaen.fr:3128
export https_proxy=http://proxy.unicaen.fr:3128
```


### Installation des logiciels


#### GIT

Les sources étant gérées sous **GIT**, git doit être installé pour permettre de récupérer le dépôt.

```bash
# Installation de GIT
apt-get install git-core wget
```

#### Serveur web (Apache2)

> Pour uniquement tester Oscar en **local** (Développement ou test), vous pouvez ne pas installer Apache et utiliser directement PHP-CLI

Serveur web (Apache2) et PHP7.3 :

```bash
# Installation de APACHE2
apt-get install apache2

# PHP + Modules PHP
apt install \
    php7.3-bcmath \
    php7.3-bz2 \
    php7.3-curl \
    php7.3-dom \
    php7.3-gd \
    php7.3-intl \
    php7.3-ldap \
    php7.3-mbstring \
    php7.3-pdo-pgsql \
    php7.3-xml \ 
    php7.3-zip
```

#### Installation de la base de donnée

Installez également le client **postgresql** qui sera necessaire pour importer la structure initale de la base de donnée :

```bash
# Postgresql (ou autre selon le client de BDD utilisé)
apt-get install postgresql postgresql-client postgresql-client-common
```

Si la base de données est sur la même machine, installation du serveur **Postgresql** :

```bash
# Postgresql (ou autre selon le client de BDD utilisé)
apt-get install postgresql-server
```
---

#### Installation de ElasticSearch

**ElasticSearch** est l'application utilisée par Oscar pour indéxer des données pour les moteurs de recherche.

[Procédure d'installation de ElasticSearch](extras/install-elasticsearch.md)


#### Installation de composer

*Oscar* utilise des libraires PHP tiers (vendor). Les librairies tiers sont gérées via [Composer](https://getcomposer.org/).

Pour installer [Composer](https://getcomposer.org/) :

```bash
# Récupération de la dernière version de composer
wget https://getcomposer.org/composer.phar

# On le place dans /bin
mv composer.phar /bin/composer

#On donne les droit d'accès
chmod +x /bin/composer
```

Vous pouvez tester le bon déroulement de l'installation de **composer** en saisissant la commande `composer`, vous devriez obtenir l'invite en ligne de commande :

```bash
   ______
  / ____/___  ____ ___  ____  ____  ________  _____
 / /   / __ \/ __ `__ \/ __ \/ __ \/ ___/ _ \/ ___/
/ /___/ /_/ / / / / / / /_/ / /_/ (__  )  __/ /
\____/\____/_/ /_/ /_/ .___/\____/____/\___/_/
                    /_/
Composer version 1.7-dev (837ad7c14e8ce364296e0d0600d04c415b6e359d) 2018-06-07 09:15:18

Usage:
  command [options] [arguments]

Options:
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
      --profile                  Display timing and memory usage information
      --no-plugins               Whether to disable plugins.
  -d, --working-dir=WORKING-DIR  If specified, use the given directory as working directory.
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

etc...
```


---

## Installation de la copie de Oscar

### Emplacement

Il est recommandé d'installer Oscar dans le dossier **/var** du système :

```bash
mkdir -p /var/OscarApp
cd !$
```

Faire un *checkout* de la copie de travail,

```bash
git clone https://git.unicaen.fr/open-source/oscar.git
```

> L'accès au dépôt sur le Gitlab Unicaen nécessite la création d'un compte nominatif. Un fois le compte activé, vous aurez accès aux dépôts complets (inculant cette documentation technique)


### Installation des dépendances avec composer

L'installation des dépendances se fait avec la commande :  

```bash
composer install --prefer-dist
```

Composer se chargera d'installer les dépendances PHP tel de définies dans le fichier `composer.json`.

> Si certaines librairies vous signale des dépendances PHP manquantes, installez les et signaler le nous afin de compléter cette documentation.


### Installation de la base de données

#### Création de la base de données vide

Création de l'utilisateur/bdd locale si besoin :

```bash
su - postgres
psql
```

### Création de l'utilisateur

Puis création de l'utilisateur/bdd :

```sql
CREATE USER oscar WITH PASSWORD 'azerty';
CREATE DATABASE oscar_dev;
GRANT ALL PRIVILEGES ON DATABASE oscar_dev to oscar;
\q
```

### Structure de données initiales

Les données "de base" sont à disposition dans
le dépôt dans le fichier : `install/oscar-install.sql`.

```bash
psql -h localhost -U oscar oscar_dev < install/oscar-install.sql
```

> La structure initiale n'est pas forcement à jour, vous devez donc procéder à la **Mise à jour du modèle** présenté dans le point suivant.



## Configuration d'oscar



### Configuration éditable 

création du fichier **config/autoload/oscar-editable.yml** ce fichier est utilisé par Oscar pour stoquer des paramètres modifiables depuis l'interface. **Assurez vous qu'il est accessible en écriture par le serveur web**

```bash
touch config/autoload/oscar-editable.yml
```


### Base de données

Oscar est conçu pour fonctionner avec une base de données *Postgresql*.

La configuration de l'accès à la BDD est renseignée dans le fichier
`./config/autoload/local.php`.

Si le fichier n'existe pas, un modèle existe dans le dépôt :

```bash
cp config/autoload/local.php.dist config/autoload/local.php
vi !$
```

Dans un premier temps, configurez simplement l'accès à la base de donnée : 

```php
<?php
// config/autoload.local.php
// ...
return array(
    // ...
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                // ...
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '5432',
                    'user'     => 'oscar',
                    'password' => 'azerty',
                    'dbname'   => 'oscar_dev',
                    'charset'  => 'utf8'
                ),
            ),
        ),
    ),
);
```

Une fois oscar configuré pour accéder à la base de données, il **faut mettre à jour le modèle** (même si vous venez d'installer la structure initiale).


#### Mise à jour du modèle

Oscar est basé sur l'ORM **Doctrine**, la mise à jour du modèle s'effectue en ligne de commande avec la commande : 

```bash
php vendor/bin/doctrine-module orm:schema-tool:update --force
```


### Mise à jour des privilèges de l'application

Les droits d'accès au fonctionnalités sont gérés en base de données via des **privilèges**. Au cour du développement, des fonctionnalités sont ajoutées régulièrement, donnant lieu à la création à de nouveaux privilèges pour réguler l'accès à ces fonctionnalités.

Il faut donc à chaque mise à jour mettre à jour ces privilèges en base de données.

Pour **mettre à jour les privilèges**, executez la commande : 

```bash
php public/index.php oscar patch checkPrivilegesJSON
```

> Executer cette commande jusqu'à obtenir un message "Les privilèges sont à jour" (sera prochainement corrigé).


### Tester la configuration

Vous pouvez tester la configuration avec la commande :

```bash
$ php bin/oscar.php oscar:check:config
```

Vous devrier obtenir un rapport sur la configuration trouvée et des éventuels problèmes détéctés : 

```
Vérification de la configuration
================================

N°Version : v2.11.1-test-zf3#127ca71b "Macclaine" (2019-11-07 14:11:51)
Configuration : /home/bouvry/Projects/Unicaen/oscar_zendframework3_merge/config/autoload/local.php
System : Linux bouvry-Precision-7520 4.15.0-64-generic #73-Ubuntu SMP Thu Sep 12 13:16:13 UTC 2019 x86_64

PHP Requirements : 
-------------------

 - PHP version : 7.3.9 (7.3.9-1+ubuntu18.04.1+deb.sury.org+1)OK

 - php.ini 
/etc/php/7.3/cli/php.ini

Modules
-------

 ------------- -------------------------------------- ----------- 
  Modules PHP   Version                                Statut     
 ------------- -------------------------------------- ----------- 
  bz2           7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  curl          7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  fileinfo      7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  gd            7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  iconv         7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  json          1.7.0                                  Installed  
  ldap          7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  mbstring      7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  openssl       7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  pdo_pgsql     7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  posix         7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  Reflection    7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  session       7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  xml           7.3.9-1+ubuntu18.04.1+deb.sury.org+1   Installed  
  zip           1.15.4                                 Installed  
 ------------- -------------------------------------- ----------- 

 OSCAR configuration : 
-----------------------

 - Configuration LOCAL config/autoload/local.php : OK
 - Configuration éditable (config/autoload/oscar-editable.yml) :  OK


ETC...
```

Assurez vous que les modules PHP requis sont bien détectés avec la mention "Installed" et que la base de données réponds. A cette étape, Oscar est fonctionnel mais il reste encore quelques paramètres à configurer.


## Étape suivante

 - [Configurer Le serveur Apache](extras/install-apache.md)
 - Configurer l'authentification (Ldap)
 - [Configuration métier](./configuration.md)
 - Synchroniser les données depuis le système d'information

------------------------------------------------------




### Configurer les mails

[Documentation du mailer](./mailer.md)



## Première connexion

### Compte administrateur

Pour l'administration de l'application, vous pouvez créer un compte administrateur
dédié en utilisant l'utilitaire en ligne de commande.

Rendez-vous à la racine de l'application :

```bash
cd /var/oscar_path
```

Puis on commence par créer un compte d'autentification :

```bash
php public/index.php oscar auth:add
```

Puis on lui attribue le rôle "Administrateur" :

```bash
php public/index.php oscar auth:promote <USER> Administrateur
```

Utiliser ensuite le navigateur pour vous rendre sur oscar et utiliser l'identifiant **admin** avec la mot de passe **password** pour vous connecter en tant qu'administrateur.


**UnicaenAuth** va permettre de configurer l'accès à Oscar en utilisant le *Cas*.
Pour les copies de développement/préprod, l'option `usurpation_allowed_usernames`
permet de s'identifier à la place d'un utilisateur.
