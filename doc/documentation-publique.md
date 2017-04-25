# OSCAR (Organisation et Suivi des Contrats et des Activités de Recherche)

Oscar est une **application web** développée par l'Université de Caen Normandie. Ce document présente les prérequis techniques et la procédure d'installation.


## Prérequis

 - Système linux (Debian, Ubuntu)
 - Serveur web (Apache2)
 - PHP 5.6+ (support LDAP, Postgresql)
 - Postgresql 9+
 - Annuaire LDAP
 
Selon les différents connecteurs, des modules PHP peuvent être requis (ex : Connecteur vers une base de données MySQL, nécessite le module MySQL de PHP)


## Installation

### Mettre à jour le système

```bash
apt-get update
```

```bash
apt-get upgrade
```


### Logiciels tiers

```bash
# Subversion, pour obtenir les sources de l'application oscar
apt-get install Subversion

# Apache2, le serveur web
apt-get install apache2

# PHP + Modules PHP
apt-get install php5 php5-ldap php5-curl php5-cli php5-pgsql php5-intl

# Postgresql (ou autre selon le client de BDD utilisé)
apt-get install postgresql-client postgresql-client-common
```

Pour le développement : 

```bash
# GIT (composer va en avoir besoin pour récupérer les librairies tiers)
apt-get install git

# NodeJS (idem)
apt-get install nodejs nodejs-legacy npm
```

### Proxy

Si besoin, penser à configurer le proxy

```bash
export http_proxy=http://proxy.domain.fr:3128
export https_proxy=http://proxy.domain.fr:3128
```

## Installation de la copie

### Dossier racine

Dans le dossier **/var/oscar** (Dossier recommandé), à créer si ça n'est pas déjà fait :

```bash
mkdir -p /var/oscar
cd !$
```

Faire un *checkout*,

```bash
svn co https://svn.unicaen.fr/svn/oscar/trunk/developpement --username <username>
```


### Configuration d'oscar

```bash
cd /var/oscar/developpement
```

### Oscar (Base de données)

Copier le fichier de configuration `./config/autoload/local.php.dist`, puis éditer
la configuration de la base de donnée. **Penser à modifier le driver selon la bdd
utilisée**.

```bash
cp config/autoload/local.php.dist config/autoload/local.php
vi !$
```

Une copie des données de développement est maintenue à jour dans le dossier `./data/backup_oscar-dev-lasted.sql_` en tenant compte des évolutions du modèle.

Pour une installation "vide", utiliser la structure et les données initiales du dossier `install/`

### Unicaen App (ldap & mail)

Copier le fichier `unicaen-app.local.php.dist` puis éditer :

```bash
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php
vi !$
```

Ce fichier permet de configurer l'authentification LDAP utilisée dans Oscar


### Installation des librairies tiers PHP (développement uniquement)

Les librairies PHP sont gérées avec [Composer](https://getcomposer.com), installez le si ça n'est pas déjà fait :

```bash
curl -sS https://getcomposer.org/installer | php
```

Puis installer/mettre à jour les dépendances du projet :

```bash
php composer.phar install
```

Le processus peut être assez long

### Installation des librairies tiers FRONT (Developpement uniquement)

(Toujours depuis le dossier developpement)

```bash
npm install
```

Puis installation des librairies JS/CSS via *bower*

```bash
node node_modules/bower/bin/bower --allow-root update
```


### Mise à jour de la BDD

Depuis la racine du projet, commencer par visualiser les requêtes qui seront envoyées

```bash
php vendor/bin/doctrine-module orm:schema-tool:update --dump-sql
```

Puis mettre à jour la structure avec le flag `--force` :

```bash
php vendor/bin/doctrine-module orm:schema-tool:update --force
```

### Configuration du VHost

Activer les modules Apache si besoin :

```bash
a2enmod rewrite
a2enmod ssl
service apache2 reload
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
   ServerAdmin webmaster@localhost
   DocumentRoot /var/www/oscar

   SSLEngine On
   SSLCertificateFile /etc/ssl/certs/oscar-pp_unicaen_fr.crt
   SSLCertificateKeyFile /etc/ssl/private/oscar-pp_unicaen_fr.key
   SSLCACertificateFile /etc/ssl/certs/DigiCertCA.crt

   # Visible dans l'application
   SetEnv APPLICATION_ENV beta

   <Directory /var/www/oscar>
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

Indiquer comme **DocumentRoot /var/www/oscar/oscar/trunk/developpement/public*** puis créer un lien symbolique dans **/var/www** :

```bash
cd /var/www
ln -s ../oscar/oscar/trunk/developpement/public oscar
```

## Droits d'écriture

S'assurer que les dossiers :

 - `./data/DoctrineORMModule`
 - Le dossier choisi pour l'index Lucene
 - Le dossier de stoquage des documents

Sont bien accessibles en écriture.
