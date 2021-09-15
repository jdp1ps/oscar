# Problème connus


## Installation

### Composer : Your requirements could not be resolved to an installable set of packages.

Vous êtes sur PHP 7.4, et composer retourne cette erreur : 

```
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Your requirements could not be resolved to an installable set of packages.

Problem 1
- Installation request for mpdf/mpdf v7.1.9 -> satisfiable by mpdf/mpdf[v7.1.9].
- mpdf/mpdf v7.1.9 requires php ^5.6 || ~7.0.0 || ~7.1.0 || ~7.2.0 || ~7.3.0 -> your PHP version (7.4.21) does not satisfy that requirement.
Problem 2
- mpdf/mpdf v7.1.9 requires php ^5.6 || ~7.0.0 || ~7.1.0 || ~7.2.0 || ~7.3.0 -> your PHP version (7.4.21) does not satisfy that requirement.
- unicaen/app 3.1.12 requires mpdf/mpdf ^7.1 -> satisfiable by mpdf/mpdf[v7.1.9].
- Installation request for unicaen/app 3.1.12 -> satisfiable by unicaen/app[3.1.12].
```

Utiliser la commande *composer* suivant pour ignore les problèmes liès à la version de PHP : 

```
composer install --ignore-platform-reqs
```