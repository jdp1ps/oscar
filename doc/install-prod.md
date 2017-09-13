# Installation en production/pré-production

L'installation a été testé sous Debian et Ubuntu Server

## Prérequis

 - Système linux (Debian, Ubuntu)
 - Serveur web (Apache2)
 - PHP 7.0+ (compatible 5.6+ pour le moment) (support LDAP, Postgresql)
 - Postgresql 9.4+
 - Annuaire LDAP (supann)

## Installation du système

On commence par mettre le système à jour.

```bash
apt-get update
```

```bash
apt-get upgrade
```

### Installation des logiciels

```bash
# Installation de GIT
apt-get install git-core

# Installation de APACHE2
apt-get install apache2

# PHP + Modules PHP
apt-get install \
    php7.0 \
    php7.0-cli \
    php7.0-curl \
    php7.0-intl \
    php7.0-ldap \
    php7.0-mcrypt \
    php7.0-pgsql


# Postgresql (ou autre selon le client de BDD utilisé)
apt-get install postgresql-client postgresql-client-common
```

### Proxy (Si besoin)

Si besoin, configurer le proxy :

```bash
export http_proxy=http://proxy.unicaen.fr:3128
export https_proxy=http://proxy.unicaen.fr:3128
```

## Installation de la copie

Dans le dossier **/var/oscar**, à créer si ça n'est pas déjà fait :

```bash
mkdir -p /var/oscar
cd !$
```

Faire un *checkout* de la copie de travail,

```bash
git clone https://<USER>@git.unicaen.fr/bouvry/oscar
```


## Vendor (librairies tiers)

*Oscar* utilise des libraires tiers (vendor). 

Pour le développement, elles sont gérées via composer, son utilisation nécessite 
 d'avoir accès aux librairies embarquées **Unicaen** : *UnicaenApp*, *UnicaenAuth*.
 
**Pour une installation en production/démo**, une archive du dossier vendor est 
 disponible dans le dossier `install` au format *.tar.gz* :

```bash
tar xvfz install/vendor.tar.gz
```

Ou pour les développeurs situé dans le **réseau unicaen** : 

```bash
php composer.phar update
```


## Configuration d'oscar


### Base de donnée

Oscar est conçu pour fonctionner avec une base de données *Postgresql*.

La configuration de l'accès à la BDD est renseignée dans le fichier
`./config/autoload/local.php`.

Si le fichier n'existe pas, un modèle existe dans le dépôt :

```bash
cp config/autoload/local.php.dist config/autoload/local.php
vi !$
```

### Installation de la base de donnée vide

Création de l'utilisateur/bdd locale si besoin :

```bash
su - postgres
```

Puis création de l'utilisateur/bdd :

```sql
CREATE USER oscar WITH PASSWORD 'azerty';
CREATE DATABASE oscar_dev;
GRANT ALL PRIVILEGES ON DATABASE oscar to oscar_dev;
\q
```

### Données initiales

Les données "de base" sont à disposition dans
le dépôt dans le fichier : `data/backup_oscar-empty.sql`.

```bash
psql -h localhost -U oscar < data/backup_oscar-empty.sql
```

### Configuration de la BDD dans Oscar

La configuration de la BDD est spécifiée dans le fichier `config/autoload/local.php`.

Si le fichier n'existe pas, un modèle est présent dans `config/autoload/unicaen-app.local.php.dist` :

```bash
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php
vi !$
```
Exemple de configuration :

```php
<?php
/config/autoload/local.php
return array(
    // ...
    // Accès BDD
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
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

### Configurer le serveur web (Apache)

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

On peut utiliser un lien symbolique pour simplifier les bascules

```bash
cd /var/www
ln -s ../oscar/public oscar
```


### Droits d'écriture

S'assurer que les dossiers :

 - `./data/`
 - Le dossier choisi pour l'index Lucene
 - Le dossier de stoquage des documents
 - Le fichier de log 

Sont bien accessibles en écriture.



### Unicaen App (ldap & mail)

La configuration de **UnicaenApp** et **UnicaenAuth** (surcouches utilisées dans 
Oscar) on leurs fichiers de configuration respectifs dans le dossier `/config/autoload` : 

 - Pour UnicaenApp, `config/autoload/unicaen-app.local.php`
 - Pour UnicaenAuth, `config/autoload/unicaen-auth.local.php`
 
 
Des fichiers d'exemple sont disponibles avec l'extension `.dist`.
  
**UnicaenApp** :
 - Configuration de l'authentification avec LDAP
 - Paramètre pour le *Mailer*
 

```bash
cp config/autoload/unicaen-app.local.php.dist config/autoload/unicaen-app.local.php
vi !$
```

Voici un exemple de configuration LDAP : 

```php
<?php
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

  
**UnicaenAuth** va permettre de configurer l'accès à Oscar en utilisant le *Cas*. 
Pour les copies de développement/préprod, l'option `usurpation_allowed_usernames` 
permet de s'identifier à la place d'un utilisateur.


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
php public/index.php oscar auth:add admin admin@domaine.tld password "Administrateur"
```

Puis lon lui attribut le rôle "Administrateur" : 

```bash
php public/index.php oscar auth:promote admin Administrateur 
```

Utiliser ensuite la navigateur pour vous rendre sur oscar et utiliser l'identifiant **admin** avec la mot de passe **password** pour vous connecter en tant qu'administrateur.




