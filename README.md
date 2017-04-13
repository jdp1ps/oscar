# Installation du projet

```bash
svn co <repository-url>
```

La racine du projet est dans le dossier `developpement/`.

```bash
cd developpement
```


# Outils de développement

## Fichiers de configuration pour le développement

 - `package.json` : Configuration Node.js (Gulp)
 - `bower.json` et `.bowerrc` : Configuration Bower (dépendance JS/CSS)
 - `composer.json/lock` : Configuration Composer (dépendances PHP)


## Installation des outils

### Outils pour le front (Gulp/bower)

Pour installer les outils Node : 

```
$ npm install
```

La commande var télécharger les outils de développement dans le dossier `node_modules` (le contenu ne doit pas être commité). Ces utilitaires ne sont utilisés qu'en développement et en post-deploiment.

Puis créer les aliases si les commandes n'existe pas en global : 

```bash
alias bower='/path/to/trunk/node_modules/.bin/bower'
alias gulp='/path/to/trunk/node_modules/.bin/gulp'
```

Pour tester si les outils sont bien installés : 

```
$ bower -v
1.4.1

$gulp -v
[11:32:59] CLI version 3.8.11
[11:32:59] Local version 3.8.11
```


### Bower

**[Bower](http://bower.io/)** permet de gérer les dépendances front d'un projet (Javascript/CSS).

Pour **mettre à jour ou installer les dépendances Javascript** du projet, on utilise depuis la racine du projet la commande : 

```
$ bower update
```

Les librairies Javascript/CSS sont installées dans le dossier `/public/js/vendor` (ce chemin est configuré dans le fichier `.bowerrc`).

Pour ajouter une dépendance au projet, on utilise la commande : 

```
$ bower install <nomDeLaLibJS> --save
```

la librairie sera ajoutée aux dépendances du projet dans le fichier `bower.json`.

La recherche d'une libraire peut être faites avec la commande : 

```
$ bower search awsomelibrary
```


## Gulp : Task runner

Gulp est un utilitaire NodeJS. Il a pour principale fonction : 

 - Compilation SASS
 - @todo Check de syntaxe JS 
 - @todo Minification des fichiers JS/CSS

Il est installé avec node et permet d'automatiser certaines tâches qui sont déclarées dans le fichier `gulpfile.js`.


@todo



## Composer : Dépendances PHP

Les dépendances PHP sont gérées avec <https://getcomposer.org/>, pour mettre à jour les dépendances du projet, se placer à la racine du projet et taper : 

```
$ composer update
```

Pour ajouter une dépendance au projet : 

```
$ composer install <libraryName> --save
```


## Serveur de développement

Depuis le dossier `developpement` : 

```
$ php -S 127.0.0.11:2048 -t public/ public/index.php
```

ou

```bash
$ ./bin/launch-server-dev.sh
```