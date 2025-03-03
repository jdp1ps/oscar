# Installation en production/pré-production

L'installation a été testée sous Debian

## Prérequis

- Système linux (Debian, Ubuntu)
- Serveur web (Apache2)
- PHP 8.2.x (support LDAP, Postgresql, mcrypt, intl, DOM/XML, mbstring, gd, zip)
- Postgresql 9.4+ (version 10 supportée)
- Annuaire LDAP (supann)

Matériel (Recommandation)

- CPU 2 Core 2.4 Ghz
- RAM 4 Go
- Espace disque 20G (Application seule, hors documents)

> Prévoyez plus d'espace si vous stoquez des documents directement sur la machine hébergeant Oscar.

## Installation du système

Mise à jour du système

```bash
apt-get update
apt-get upgrade
```

Installation de GIT

```bash
apt-get install git-core wget
```

Installation du serveur web (Apache) et PHP8.2

```bash
# Installation de APACHE2
apt install apache2

# PHP + Modules PHP
apt install \
  php-pear \
  php8.2-bcmath \
  php8.2-bz2 \
  php8.2-cli \
  php8.2-curl \
  php8.2-dev \
  php8.2-dom \
  php8.2-gd \
  php8.2-gearman \
  php8.2-intl \
  php8.2-ldap \
  php8.2-mbstring \
  php8.2-pgsql \
  php8.2-ssh2 \
  php8.2-xml \
  php8.2-zip
  
# Installation WKHtmlToPdf/OpenSans
apt install wkhtmltopdf fonts-open-sans
```

[Configuration PHP](./configuration/config-php.md)

Installez également le client postgresql qui sera nécessaire pour importer la structure initiale de la base de donnée :

```bash
# Postgresql (ou autre selon le client de BDD utilisé)
apt-get install postgresql postgresql-client postgresql-client-common
```

## Installation

Dans cette documentation, le dossier d'installation est `/var/OscarApp`

### Récupération des fichiers source

```bash
mkdir -p /var/OscarApp
cd !$
git clone https://git.unicaen.fr/open-source/oscar.git
cd oscar
```

### Dépendances PHP

*Oscar* utilise des libraires PHP tiers (vendor). Les librairies tiers sont gérées
via [Composer](https://getcomposer.org/).

```bash
# Récupération de la dernière version 2.2.x de composer
wget https://getcomposer.org/download/2.2.24/composer.phar

# On le place dans /bin
mv composer.phar /bin/composer

#On donne les droit d'accès
chmod +x /bin/composer
```

Installation des dépendances PHP

```bash
composer install --prefer-dist
```

### OscarWorker : Moteur de tâche 

[Installation de oscarworker](./configuration/config-gearman.md)


### Fichiers de configuration par défaut

Fichiers de base

```bash
# Fichier général
cp config/autoload/unicaen-app.local.php config/autoload/unicaen-app.local.php
# Authentification (LDAP/CAS/DB)
cp config/autoload/unicaen-auth.local.php config/autoload/unicaen-auth.local.php
# Module signature (Parapheur)
cp config/autoload/unicaen-signature.local.php config/autoload/unicaen-signature.local.php
```

Configuration éditable (Sera modifié depuis l'interface web)

```bash
touch config/autoload/oscar-editable.yml
chmod 777 config/autoload/oscar-editable.yml
```

### Logs

Donner l'accès en écriture au dossier de `logs` :

```bash
touch logs/oscar.log
# Dossier des logs pour le parapheur
mkdir -p logs/signature_exchange
chmod -R logs
```

### Accès aux dossiers (Documents)

```bash
# Documents des activités
chmod 777 data/documents/activity
# Documents publiques
chmod 777 data/documents/public
# Documents en cours de signature
chmod 777 data/documents/signature
# Demandes d'activités
chmod 777 data/documents/request
```

### Liens symboliques

```bash
cd public/unicaen
ln -s ../../vendor/unicaen/signature/public/dist signature
cd ../../
```

### Moteur de recherche (Elasticsearch)

[Installation de Elasticsearch](./install-config/extras/install-elasticsearch.md)

### Modèle de données

#### CAS 1 : Vous avez un serveur de base de données (Recommandé)

Connectez-vous à votre serveur de base de donnée pour créer la base de données initiale à partir du fichier
`install/oscar-install.sql`

#### CAS 2 : La base de données est sur la même machine (Version test/dev)

Suivez le guide [Installation d'un serveur Postgresql](install-config/extras/install-postgresql.md)

Puis chargez la structure de données initiale à partir du fichier `install/oscar-install.sql`

#### Base de données / MAJ du modèle

La configuration de l'accès à la BDD est renseignée dans le fichier
`./config/autoload/local.php`.

```bash
nano config/autoload/local.php
```

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
Puis on actualise le modèle

```bash
# Accès au dossier pour Doctrine
mkdir -p data/DoctrineORMModule
chmod -R 777 data/DoctrineORMModule

# Mise à jour du modèle
php vendor/bin/doctrine-module orm:schema-tool:update --force --complete
```

### Mise à jour des privilèges de l'application

Les droits d'accès aux fonctionnalités sont gérés en base de données via des **privilèges**. Au cours du développement,
des fonctionnalités sont ajoutées régulièrement, donnant lieu à la création à de nouveaux privilèges pour réguler
l'accès à ces fonctionnalités.

Il faut donc à chaque mise à jour mettre à jour ces privilèges en base de données.

Pour **mettre à jour les privilèges**, executez la commande :

```bash
php bin/oscar.php check:privileges
```

> Executer cette commande jusqu'à obtenir un message "Les privilèges sont à jour" (sera prochainement corrigé).

### Accès HTTP - Configurer le serveur web (Apache)

[Installation et configuration Apache2](./install-config/extras/install-apache.md)

### Mailer

[Configuration de la distribution des mails](configuration/config-mailer.md)

### Bravo

Oscar est installé

---

## Tester l'installation

### Vérifier la configuration (Ligne de commande)

```bash
php bin/oscar.php check:config
```

Assurez-vous que les modules PHP requis sont bien détectés avec la mention "Installed" et que la base de données
répond. À cette étape, Oscar est fonctionnel "techniquement".

### Vérification Web

Les administrateurs techniques peuvent créer une authentification temporaire pour tester la connection.

## Configuration technique/métier

- [Authentification](./configuration/config-auth.md) : Authentification LDAP, CAS, local... 
- [Documents](./configuration/config-documents.md) 
- [Notifications](configuration/config-notifications.md) Système de notification (Jalons)
- [Numérotation OSCAR](configuration/config-numerotation.md)
- Configuration des dépenses : 
    * [Configuration numéro financier (PFI)](./configuration/config-pfi.md)
    * SIFAC : [Remontée des dépenses avec SIFAC](./configuration/config-sifac.md)
    * SIFAC+ (a venir)
    * ...
- [Feuilles de temps](./timesheet.md)



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

