# Installation en production/pré-production

L'installation a été testée sous Debian et Ubuntu Server

## Prérequis

 - Système linux (Debian, Ubuntu)
 - Serveur web (Apache2)
 - PHP 7.3+ (support LDAP, Postgresql, mcrypt, intl, DOM/XML, mbstring, gd, zip)
 - Postgresql 9.4+ (version 10 supportée)
 - Annuaire LDAP (supann)

Matériel (Recommandation)
 - CPU 2 Core 2.4 Ghz
 - RAM 4 Go
 - Espace disque 20G (Application seule, hors documents)


> Prévoyez plus d'espace si vous stoquez des documents directement sur la machine hébergeant Oscar.  


## Installation du système

### Mise à jour du système

On commence par mettre le système à jour.

```bash
apt-get update
```

```bash
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

GIT est est le système de versionnage utilisé pour Oscar

```bash
# Installation de GIT
apt-get install git-core wget
```

#### Serveur web (Apache) et PHP7.3

Commencez par ajouter les dépôts PHP 7.3 (Merci à Damien pour les informations) :

```bash
apt -y install lsb-release apt-transport-https ca-certificates 
wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php7.3.list
```
Sous ubuntu il sera certainement nécessaire d'ajouter ces éléments
```
apt install software-properties-common
add-apt-repository ppa:ondrej/php
apt update
apt install php7.3
php -v
```

Mettre à jour les sources  : 

```bash
# Installation de APACHE2
apt update
```

```bash
# Installation de APACHE2
apt-get install apache2

# PHP + Modules PHP
apt-get install php7.3
apt-get install php7.3-bcmath
apt-get install php7.3-bz2
apt-get install php7.3-cli
apt-get install php7.3-curl
apt-get install php7.3-dom
apt-get install php7.3-gd
apt-get install php7.3-gearman
apt-get install php7.3-intl
apt-get install php7.3-ldap
apt-get install php7.3-mbstring
apt-get install php-mcrypt
apt-get install php7.3-pdo-pgsql
apt-get install php7.3-xml 
apt-get install php7.3-zip
```

Installez également le client postgresql qui sera necessaire pour importer la structure initale de la base de donnée :

```bash
# Postgresql (ou autre selon le client de BDD utilisé)
apt-get install postgresql postgresql-client postgresql-client-common
```


### Installation de la base de donnée

Si la base de données est sur la même machine, installation du serveur **Postgresql** :

```bash
# Postgresql (ou autre selon le client de BDD utilisé)
apt-get install postgresql-server
```
En cas de soucis sur Ubuntu il est possible de procéder ainsi :
```
wget -q https://www.postgresql.org/media/keys/ACCC4CF8.asc -O - | sudo apt-key add -
sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt/ stretch-pgdg main" >> /etc/apt/sources.list.d/pgdg.list'
apt update
apt-get install postgresql postgresql-contrib

```
Une fois connecté :
```
psql
postgres-# \conninfo
résultat :
Vous êtes connecté à la base de données « postgres » en tant qu'utilisateur « postgres » via le socket dans « /var/run/postgresql » via le port « 5432 ».
```
CTRL D (deux fois)
```
postgres=# \q
xxx@zzzz:~$ déconnexion
```

Vérification du bon fonctionnement
```
sudo -i -u postgres
```
CTRL D (pour quitter)

## Installation de la copie de Oscar

### Emplacement

Il est recommandé d'installer oscar dans le dossier **/var** du système :

```bash
mkdir -p /var/OscarApp
cd !$
```

Faire un *checkout* de la copie de travail,

```bash
git clone https://git.unicaen.fr/open-source/oscar.git
```

> L'accès au dépôt sur le Gitlab Unicaen nécessite la création d'un compte nominatif. Un fois le compte activé, vous aurez accès aux dépôts complets (inculant cette documentation technique)


### Dépendances PHP

*Oscar* utilise des libraires PHP tiers (vendor). Les librairies tiers sont gérées via [Composer](https://getcomposer.org/).

> Si certaines librairies vous signale des dépendances PHP manquantes, installez les et signaler le nous.


#### Installation de composer

> Novembre 2020 : Les dépendances Oscar **ne gère PAS composer 2**, il faut donc utiliser uniquement composer 1.

Commencez par installer [Composer](https://getcomposer.org/) :

```bash
# Récupération de la dernière version 1 de composer
wget https://getcomposer.org/composer-1.phar

# On le place dans /bin
mv composer-1.phar /bin/composer

#On donne les droit d'accès
chmod +x /bin/composer
```
Il est aussi possible aussi d'utiliser cette procédure pour composer :
```
apt-get install composer
```
Version officielle supportée donc pas forcément la dernière
NB : Faire attention au groupe (user) auquel appartient composer,
sinon il faudra le déplacer dans le dossier user local pour éviter de le lancer en root
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

#### Installation des dépendances avec composer

L'installation des dépendances se fait avec la commande :

```bash
composer install --prefer-dist
```

Composer se chargera d'installer les dépendances PHP tel de définies dans le fichier `composer.json`.

Sous Ubuntu il est possible que ce paquet bcmath bloque, dans ce cas, ajouter cette commande
```
apt install php7.*-bcmath
```


## Gestionnaire de tâche (via Gearman)

Gearman est un *daemon* qui se chargera de gérer les tâches Oscar.


```bash
# Installation de Gearman
apt install gearman-job-server

# Status du deamon
systemctl status gearman-job-server.service
# ou (selon que vous utilisiez systemd ou non selon votre version debian)
service gearman-job-server status

# Surveiller les tâches en attentes
watch "gearadmin --status | sort -n | column -t"
# Dans le cas ou vous avez un message "gearadmin not found", installez le nécessaire
apt-get install gearman-tools
```

Par défaut, l'extension *Gearman* n'est pas activée dans le `php.ini`. Éditez les fichier **/etc/php/7.3/cli/php.ini** et **/etc/php/7.3/apache2/php.ini** en ajoutant la ligne :

```ini
; /etc/php/7.3/cli/php.ini - /etc/php/7.3/apache2/php.ini
extension=gearman
```

Ensuite il faut configurer le *Worker Oscar* qui se chargera de réaliser les tâches disponibles sur le serveur :

```bash
# on copie le gabarit de configuration du service
cp install/oscarworker.dist.service config/oscarworker.service

# On édite le service
nano config/oscarworker.service
```

> Dans le fichier `config/oscarworker.service`, vous devez simplement indiquer le chemin complet vers le fichier PHP **bin/oscarworker.php**.

Ajouter le *worker oscar* au service du système.

```bash
# Passage en root
sudo su

# On va dans le dossier des service
cd /etc/systemd/system  

# On ajoute la configuration du service dans SYSTEMD avec un lien symbolique
ln -s /var/OscarApp/oscar/config/oscarworker.service oscarworker.service

# On lance le service
service oscarworker start
# Si vous utilisez systemd
systemctl start oscarworker.service

# On regarde si tout est OK
journalctl -u oscarworker.service -f

# On active le service
service enable oscarworker
```

Etape détaillée dans [Installation de Gearman](./install-gearman.md)


## Installation de la base de données

### Création de la base de données vide

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

création du fichier **config/autoload/oscar-editable.yml**

```bash
touch config/autoload/oscar-editable.yml
```

Assurez vous qu'il est accessible en écriture


### Base de données configuration

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
php bin/oscar.php check:privileges
```

> Executer cette commande jusqu'à obtenir un message "Les privilèges sont à jour" (sera prochainement corrigé).


### Configuration métier

Lors de l'étape de configuration de la base de donnée, vous avez créé un fichier `config/autoload/local.php`.


Ce fichier contient les paramètres métier de l'application. Ces paramètres sont détaillés dans les parties suivantes : 

- [Configuration des documents](config-documents.md)
- [Configuration du moteur de recherche](config-elasticsearch.md)
- [Installation et configuration de Gearman (serveur de tâche)](config-gearman.md)
- [Configuration du PFI](config-pfi.md)
- [Configuration de la distribution des courriels](config-mailer.md)
- [Configuration des notifications](config-notifications.md)
- [Configuration de la numérotation OSCAR](config-numerotation.md)

dans le fichier [Configuration métier](./configuration.md)

Créez également le fichier `config/autoload/oscar-editable.yml` :

```bash
touch config/autoload/oscar-editable.yml
```

Puis donner les droits d'accès en écriture :

```bash
chmod 777 config/autoload/oscar-editable.yml
```

Ce fichier est utilisé pour les paramètres administrable depuis l'interface (Administration > Options). 


### Tester la configuration

Vous pouvez tester la configuration avec la commande :

```bash
php bin/oscar.php check:config
```

Assurez vous que les modules PHP requis sont bien détectés avec la mention "Installed" et que la base de données réponds. A cette étape, Oscar est fonctionnel mais il reste encore quelques paramètres à configurer.


### Configurer les mails

[Documentation du mailer](./mailer.md)


### Configurer le serveur web (Apache)

Activer les modules Apache si besoin :

```bash
a2enmod rewrite
a2enmod ssl
service apache2 reload
# Si sous systemd
systemctl restart apache2.service
```

Éditer le fichier de configuration apache2 :

```bash
vi /etc/apache2/sites-available/000-default.conf
```

```apacheconf
<VirtualHost *:80>
   ServerAdmin stephane.bouvry@unicaen.fr
   ServerName oscar-pp.unicaen.fr
   ServerAdmin webmaster@localhost

   # redirection vers 443
   RewriteEngine on
   RewriteCond %{SERVER_PORT} !^443$
   RewriteRule ^/(.*) https://%{SERVER_NAME}/$1 [L,R]
</VirtualHost>

<VirtualHost *:443>
   ServerAdmin stephane.bouvry@unicaen.fr
   ServerName oscar-pp.unicaen.fr
   DocumentRoot /var/OscarApp/oscar/public

   SSLEngine On
   SSLCertificateFile /etc/ssl/certs/oscar-pp_unicaen_fr.crt
   SSLCertificateKeyFile /etc/ssl/private/oscar-pp_unicaen_fr.key
   SSLCACertificateFile /etc/ssl/certs/DigiCertCA.crt

   # Visible dans l'application
   SetEnv APPLICATION_ENV beta

   <Directory /var/OscarApp/oscar/public>
      DirectoryIndex index.php
      AllowOverride All
      Order allow,deny
      Allow from all
      Require all granted
   </Directory>

   LogLevel debug
   ErrorLog ${APACHE_LOG_DIR}/oscar-error.log
   CustomLog ${APACHE_LOG_DIR}/oscar-access.log combined
</VirtualHost>
```

On peut utiliser un lien symbolique pour simplifier les bascules

```bash
cd /var/www
ln -s ../path/to/oscar/public oscar
```


### Droits d'écriture

S'assurer que les dossiers :

 - `./data/`
 - Le dossier choisi pour l'index Lucene (si c'est l'indexeur choisi)
 - Le dossier de stockage des documents
 - Le dossier de log `./logs`  
 - Le fichier de log `./logs/oscar.log`

sont bien accessibles en écriture.

### Unicaen App (ldap & mail)

La configuration de **UnicaenApp** et **UnicaenAuth** (surcouches utilisées dans
Oscar) ont leurs fichiers de configuration respectifs dans le dossier `/config/autoload` :

 - Pour UnicaenApp, `config/autoload/unicaen-app.local.php`
 - Pour UnicaenAuth, `config/autoload/unicaen-auth.local.php`

Des fichiers d'exemple sont disponibles avec l'extension `.dist`.

#### Configurer l'authentification LDAP

Copier le fichier d'exemple :

```bash
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php
vi !$
```

Puis compléter la configuration :

```php
<?php
//config/autoload/unicaen-app.local.php
$settings = array(
  // LDAP    
  'ldap' => array(
    'connection' => array(
      'default' => array(
        'params' => array(
          'host'                => 'ldap.domain.tdl',
          'port'                => 389,
          'username'            => 'uid=identifiant,ou=system,dc=domain,dc=fr',
          'password'            => 'P@$$W0rD',
          'baseDn'              => 'ou=people,dc=domain,dc=fr',
          'bindRequiresDn'      => true,
          'accountFilterFormat' => '(&(objectClass=posixAccount)(supannAliasLogin=%s))',
        )
      )
    )
  ),
  // etc ...
);
```

NOTE : Concernant le filtre `accountFilterFormat`, si votre LDAP est non supann, penser à consulter la partie suivante.

#### Authentification LDAP : Non-Supann

Pour les LDAP **non-spann**, il est possible que le champ utilisé pour l'autentification soit différent de **supannaliaslogin**, généralement le champ **uid**. Si c'est la cas, vous pouvez éditer le fichier **config/autoload/unicaen-auth.local.php** en renseignant la clef `ldap_username` : 

```php
<?php
//config/autoload/unicaen-auth.local.php
$settings = array(
    // ...
    // Champ utilisé pour l'autentification (côté LDAP)
    // exemple avec UID au lieu de supannaliaslogin
    'ldap_username' => 'uid',
);

return array(
    'unicaen-auth' => $settings,
);
```

Vous devrez également adapter les filtres LDAP en conséquence dans le fichier **config/autoload/unicaen-app.local.php** : 

```php
<?php
//config/autoload/unicaen-app.local.php
$settings = array(
    //
    'ldap' => array(
        'connection' => array(
            // ...
        ),
        'dn' => [
            'UTILISATEURS_BASE_DN'                  => 'ou=people,dc=domain,dc=fr',
            'UTILISATEURS_DESACTIVES_BASE_DN'       => 'ou=deactivated,dc=domain,dc=fr',
            'GROUPS_BASE_DN'                        => 'ou=groups,dc=domain,dc=fr',
        ],
        
        'filters' => [
            'LOGIN_FILTER'                          => '(uid=%s)',
            'UTILISATEUR_STD_FILTER'                => '(|(uid=p*)(&(uid=e*)(eduPersonAffiliation=student)))',
            'CN_FILTER'                             => '(cn=%s)',
            'NAME_FILTER'                           => '(cn=%s*)',
            'UID_FILTER'                            => '(uid=%s)',
            // Les autres filtres sont optionnels
        ],
        /****/
    ),
    // ...
);

return array(
    'unicaen-app' => $settings,
);
```

Pensez également à corriger la clef `accountFilterFormat` dans la connexion LDAP renseignée dans le fichier `config/autoload/unicaen-app.local.php` : 

```php
<?php
//config/autoload/unicaen-app.local.php
$settings = array(
  // LDAP    
  'ldap' => array(
    'connection' => array(
      'default' => array(
        'params' => array(
          // ...
          'accountFilterFormat' => '(&(objectClass=posixAccount)(uid=%s))', // << ICI
        )
      )
    )
  ),
  // etc ...
);
```


#### Configurer l'authentification CAS

**UnicaenAuth** va permettre de configurer l'accès à Oscar en utilisant le *Cas*.

```php
<?php
//config/autoload/unicaen-auth.local.php
$settings = array(
    'cas' => array(
        'connection' => array(
            'default' => array(
                'params' => array(
                    'hostname' => 'cas.domain.fr',
                    'port' => 443,
                    'version' => "2.0",
                    'uri' => "",
                    'debug' => false,
                ),
            ),
        ),
    ),
    /**
     * Identifiants de connexion LDAP autorisés à faire de l'usurpation d'identité.
     * NB: à réserver exclusivement aux tests.
     */
    'usurpation_allowed_usernames' => array('brucebanner', 'dieu'),
);

/**
 * You do not need to edit below this line
 */
return array(
    'unicaen-auth' => $settings,
);
```

### Relation Person / Authentification

Une option a été ajouté pour force Oscar à ignorer la casse lorsque il établit la relation entre l'indentifiant de connexion et le login de la fiche personne. Par défaut cette option est ignorée, pour l'activier, éditer le fichier de configuration local : 

```php
<?php
// config/autoload.local.php
// ...
return array(
    // ...
    // Oscar
    'oscar' => [
        // ...
        'authPersonNormalize' => true,
    ]
);
```

### Usurpation

Pour les copies de développement/préprod, l'option `usurpation_allowed_usernames`
permet de s'identifier à la place d'un utilisateur.

On utilise l'identifiant `compte=compteusurpation` où `compte` correspond à
l'identifiant principale (qui doit figurer dans le tableau `usurpation_allowed_usernames`),
et `compteusurpation` correspond au compte usurpé. Le mot de passe est celui de `compte`.

Cette option n'est pas compatible avec l'identification CAS.

BUG CONNU : Cette option est utilisé pour les tests uniquement. Il peut arriver que UnicaenApp
ait des difficultés à detecter le rôle à charger lors d'une usurpation. Vérifiez toujours
lors d'une usurpation qu'un rôle est bien actif en cliquant sur le nom du compte
dans le menu principal.


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
php bin/oscar.php auth:add
```

Puis on lui attribue le rôle "Administrateur" :

```bash
php bin/oscar.php auth:promote
```

Utiliser ensuite le navigateur pour vous rendre sur oscar et utiliser l'identifiant **admin** avec la mot de passe **password** pour vous connecter en tant qu'administrateur.


**UnicaenAuth** va permettre de configurer l'accès à Oscar en utilisant le *Cas*.
Pour les copies de développement/préprod, l'option `usurpation_allowed_usernames`
permet de s'identifier à la place d'un utilisateur.
