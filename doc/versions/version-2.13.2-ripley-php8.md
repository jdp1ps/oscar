# Mise à Jour 2.13.2 "RIPLEY"

> Attention, version BETA sur la branche **"ripley-laminas-php8"**

## Mise à jour et patch

Pour les mises à jour et patch à venir, pensez à mettre à jour, en plus des sources, les lirairies tiers : 

```bash
git fetch
git pull
composer install
```

## Avant Mise à jour

Interruption du service. 

```bash
service apache2 stop
service oscarworker stop
```

Interruption des taches CRON. 
```bash
# Suspention des crons sous www-data
crontab -u www-data -e

# Ou avec l'utilisateur utilisé pour l'installation initiale
crontab -e
```


Backup de la base. 

Mise à jour du système. 


### Installation de PHP 8.2

Installation du paquet `php8.2`

```bash
apt install php8.2
```

> Si **PHP8.2** n'est pas disponible sur votre système, vérifier que la *Source list* **packages.sury.org/php** est présente dans vos *sources.list*, dans le cas contraire : 
> ```bash
> echo "deb https://packages.sury.org/php/ bullseye main" > /etc/apt/sources.list.d/php.list 
> ```
> Puis mettre à jour : 
> ```bash
> apt update 
> ```


Installation des extensions nécessaires : 

```bash
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
```

```bash
# Mode Apache/PHP 8.2
apt install libapache2-mod-php8.2
```
Configuration de `php8` comme PHP par défaut.

```bash
update-alternatives --set php /usr/bin/php8.2
update-alternatives --set phar /usr/bin/phar8.2 
update-alternatives --set phar.phar /usr/bin/phar.phar8.2 
update-alternatives --set phpize /usr/bin/phpize8.2 
update-alternatives --set php-config /usr/bin/php-config8.2 
```

> **Important** : La mise à jour de PHP implique un changement du fichier ``php.ini`` maintenant localisé ici : **/etc/php/8.2/apache2/php.ini**. Si vous avez ajusté la configuration, pensez à le modifier

### Apache2 / PHP8

```bash
a2dismod php7.4
a2enmod php8.2
```

### Mise à jour de composer (version 2.2.x)

La version de composer est **très importante**.

```bash
wget https://getcomposer.org/download/latest-2.2.x/composer.phar
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
````

## Mise à jour technique

### Mise à jour des sources

```bash
git fetch
```

Vous devriez voir la branche **ripley-laminas-php8** : 

```
Depuis https://git.unicaen.fr/open-source/oscar
   fb9f3601..4fe9f83d  ripley-suborganization -> origin/ripley-suborganization
   d5e5838e..b216c145  master                 -> origin/master
   4ecdf421..a739dc85  ripley                 -> origin/ripley
 * [nouvelle branche]  ripley-laminas-php8    -> origin/ripley-laminas-php8
   826e821d..a4996af6  spartan                -> origin/spartan
```

Basculer sur la branche **ripley-laminas-php8** : 

```bash
git checkout ripley-laminas-php8
```

### Installation des librairies tiers 

> ⚠️ Un système de jeton de sécurité (*token*) est en place sur **Github**. Vous devez générer un jeton de sécurité depuis Github : https://github.com/settings/tokens puis de configurer ce jeton pour **Composer** :
> ```bash
> composer config --global --auth github-oauth.github.com JETON
> ```

Installation des librairies

```bash
composer install
```

### Mise à jour du schéma de donnée

#### Configuration BDD

La configuration de la base de données ``./config/autoload/local.php`` : 

```php
// Vers la fin du fichier 
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                // >>> ICI
                'driverClass' => '\Doctrine\DBAL\Driver\PDO\PgSQL\Driver',
                // ... 
                'params' => array(
                    'host' => "dbb.address.ok",
                    // etc...
                    // et Ajout de la clef DRIVER
                    'driver' => 'pdo_pgsql'
                ),
            ),
        ),
    ),

```

Mise à jour des privilèges : 

```bash
php bin/oscar.php check:privileges
```

Un petit *check*
```bash
php bin/oscar.php check:config
```

> Le WORKER ne répondera pas, c'est normal

Puis MAJ de la BDD si tout est OK

```bash
php vendor/bin/doctrine-module orm:schema-tool:update --force
```

```bash
# Relance du worker
service oscarworker start
```

### Rotation des logs

Les fichiers de logs sont maintenant journalier sour la forme `oscar-YYYY-MM-DD.log`, assurez vous que l'utilisateur Web a les accès en écriture dans le dossier `logs`

### Utilisateur du CRON

> ⚠️ Le nouveau système de log propose une rotation des logs par fichier à la journée. Donc quand Oscar produit des logs, il va créer un fichier de log pour la journée. Si le fichier est créé par une tache CRON, l'accès au fichier de log en écriture sera limité à l'utilisateur CRON, et le serveur apache pourait ne pas pouvoir pas écrire dedans.

Voici la commande pour planifier vos taches CRON avec **www-data** (ou l'utilisateur que vous utilisez pour les droits Apache).

```bash
crontab -u www-data -e
```

> A noter que si vous exécutez des commandes Oscar, le même problème peut survenir (un fichier de log créé avec les droit d'utilisateur de l'auteur de la commande). Donc pensez à vérifier l'accès au fichier de log créé. (Note: Un nouvelle version de l'installation proposera probablement la création d'un utilisateur **oscar** pour résoudre ce problème)

### Système de génération des documents

La procédure de création de document (PDF) a évolué. Elle exploite maintenant un utilitaire plus performant : [wkhtmltopdf](https://wkhtmltopdf.org/), une librairie Open source (LGPLv3) dédiée à la convertion HTML > PDF

Pour l'installer sous Debian : 

```bash
apt install wkhtmltopdf
```

### Configuration des documents

Se rendre dans la partie Administration > Configuration et maintenance

#### Etape 1 : Qualifier les documents sans type

Allez dans la partie "Type de document", migrer les documents non typés. Cela permettra ensuite des deplacer ces documents

Ensuite, choisir un type de document par défaut en éditant un des types, et cocher l'option "type par défaut".

> Les documents nouvellement déposés via des procédures automatisées seront automatiquement qualifié avec de type si ça n'est pas précisé.

#### Etape 2 : Créer les onglets

Dans la partie **Type d'onglet documents** (bouton "Configurer" à droite),
créer les différents onglets de documents prévus. Pensez à sélectionner l'onglet par défaut pour les envois de document des demandes d'activité et choisir les rôles concernés par les différents onglets.

#### Etape 3 : Migration des documents

Par défaut, les documents ne sont rangés dans aucun onglet, et ne seront pas visibles. Il faut donc migrer les anciens documents dans les onglets. Pour cela la procédure **"Migration des documents hors-onglets"** va permettre de déplacer les documents qui ne sont rangés dans aucun onglet dans un des onglets créé.

Dans **Administration > Nomenclatures > Type d'onglet documents**, vous pouvez sélectionner un type de document


## Les choses à vérifier

- Accès à l'application : Authentification CAS, LDAP, Compte local (si besoin)
- Accès à une fiche activité contenant des documents (visibilité des documents)
- Envoi/suppression de document dans une activité
- Document généré


## Organisations Parents

> Cette partie est encore expérimental, si vous ignorez le champ `parent` pour le moment cela n'aura aucune répercussion sur sur les données

Oscar **Ripley** propose une gestion des structures arborescente. Un champ ``parent`` a été ajouté au modèle et permet de gérer cette filiation entre les structures.

Ce champ a été ajouté au **connecteur organization** et doit contenir le **CODE** de la structure parente (facultatif).

L'objectif est de pouvoir gérer plus facilement les droits d'accès sur une modèle de structure arborescent. 

## Date de fin des personnes

La fiche personne propose un champ "Validité" qui permet de fixer une date de fin à une personne. Si cette dernière est renseignée, elle sera utilisé pour vérifier l'accès du compte à Oscar indépendament des informations d'authentification.

Cette information peut être synchronisée depuis le connecteur Person avec le champ : `datefininscription`

## Dépenses enagées

Mise à jour de la quête SIFAC pour le chargement des dépenses engagées. la modification concerne la clause WHERE : `BTART IN('0250','0100')`. Oscar s'appuie sur ces 2 valeurs pour distinguer les lignes d'engagement des lignes réalisées.

> Si la requête n'est pas mise à jour, la colonne "Engagé" restera à 0

```php
<?php

function migrate_implode(string $separator, ?array $array)
{
    if (is_null($array)) {
        return '';
    }
    return implode($separator, $array);
}

$param_postgresql_host = /*oscar_php8_postgresql*/ "localhost";
$param_gearman_host = /*"oscar_php8_gearman"*/"localhost";
$param_elastic_host = /*"'oscar_php8_elasticsearch:9200'"*/"localhost";


return array(
    'view_manager' => array(
        'display_not_found_reason' => getenv('APPLICATION_ENV') == 'development',
        'display_exceptions' => getenv('APPLICATION_ENV') == 'development',
    ),

    // Oscar
    'oscar' => [
         // Contenu...
         // ...
         'connectors' => [
            // Connector SIFAC/DEPENSES
            'spent' => [
                'sifac' => [
                    'class' => \Oscar\Connector\ConnectorSpentSifacOCI::class,

                    /**** Nouvelle requête ****/
                    'params' => [
                        'username' => 'identifiant',
                        'password' => 'motdepasse',
                        'SID' => 'F13',
                        'port' => 1527,
                        'hostname' => 'sifac.domain.ext',
                        'optimize_clause' => ' AND STUNR > %s',
                        'spent_query' => "select  MEASURE AS pfi,  RLDNR as AB9, STUNR as idsync,  awref AS numSifac, vrefbn as numCommandeAff, vobelnr as numPiece, LIFNR as numFournisseur, KNBELNR as pieceRef, fikrs AS codeSociete, BLART AS codeServiceFait, FAREA AS codeDomaineFonct, sgtxt AS designation, BKTXT as texteFacture, wrttp as typeDocument, TRBTR as montant, fistl as centreDeProfit, fipex as compteBudgetaire, prctr AS centreFinancier, HKONT AS compteGeneral, budat as datePiece, bldat as dateComptable, gjahr as dateAnneeExercice, zhldt AS datePaiement, MANDT as mandt, BTART as BTART,  PSOBT AS dateServiceFait from sapsr3.v_fmifi where RLDNR ='9A' AND measure = '%s' AND MANDT ='430' AND BTART IN('0250','0100')"
                    ]
                    /****/
                ]
            ]
        ],
    ],

    // ... etc
);

```

## Signature numérique


### Installation
Créer le lien symbolique pour les *assets* visuels.

```
cd public/unicaen
ln -s ../../vendor/unicaen/signature/public/dist signature
```

### Dossier de dépôt des documents

Créer et donner les droits d'accès en écriture au dossier des signatures : 

### Configuration des parapheurs

```
cp config/autoload/unicaen-signature.local.php.dist config/autoload/unicaen-signature.local.php
```

Se rendre dans l'administration des privilèges pour accorder les droits d'accès en fonction des rôles.


> En cours