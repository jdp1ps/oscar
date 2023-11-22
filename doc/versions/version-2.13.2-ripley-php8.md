# Mise à Jour 2.13.x "RIPLEY"


## Avant Mise à jour


## Mise à jour technique

Mise à jour des sources :

```bash
git fetch
git checkout ripley
```

Mise à jour du schéma de donnée :

```bash
php vendor/bin/doctrine-module orm:schema-tool:update --force
```

Module SSH2 (php) si vous utilisez PCRU

```bash
sudo apt install php7.4-ssh2
```



## Configuration des documents

Se rendre dans la partie Administration > Configuration et maintenance

### Etape 1 : Qualifier les documents sans type

Allez dans la partie "Type de document", migrer les documents non-typé si besoin

IMAGE

> Cela permettra ensuite des déplacer ces documents

Ensuite choisir un type de document par défaut en éditant un des types, et cocher l'option "type par défaut".
Les documents nouvellement déposés via des procédures automatisées seront automatiquement qualifié avec de type si ça n'est pas précisé.

### Etape 2 : Créer les onglets

Dans la partie  **Type d'onglet documents** (bouton "Configurer" à droite),
créer les différents onglets de documents prévus. Pensez à sélectionner l'onglet par défaut pour les envois de document des demandes d'activité et choisir les rôles concernés par les différents onglets.

## Etape 3 : Migration des documents

Par défaut, les documents ne sont rangés dans aucun onglet, et ne seront pas visibles. Il faut donc migrer les anciens documents dans les onglets. Pour cela la procédure **"Migration des documents hors-onglets"** va permettre de déplacer les documents qui ne sont rangés dans aucun onglet dans un des onglets créé.

Dans **Administration > Nomenclatures > Type d'onglet documents**, vous pouvez sélectionner un type de document
