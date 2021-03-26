# Documentation technique

Bienvenue dans la **Documentation technique** du projet **O.S.C.A.R**.


## Installation et maintenance

S'adresse aux gestionnaires d'application souhaitant installer et configurer une instance d'oscar.

### Installation de base

 - [Procédure d'installation](install-prod.md)
 - [Procédure de mise à jour](update.md)

### Configuration de base
 - [Gestion des documents](config-documents.md) : Configuration de la gestion des documents.
 - [Configuration des documents](config-documents.md)
 - [Configuration du moteur de recherche](config-elasticsearch.md)
 - [Configuration du serveur de tâche](config-gearman.md)
 - [Configuration du PFI](config-pfi.md)
 - [Configuration de la distribution des courriels](config-mailer.md)
 - [Configuration des notifications](config-notifications.md)
 - [Configuration de la numérotation automatique OSCAR](config-numerotation.md)
 - [Configuration des feuilles de temps](timesheet.md)
 - [Configuration des dépenses (SIFAC)](config-sifac.md)

### Synchronisation au Système d'Information
 - [Connectors Oscar](connectors.md) : Connecter Oscar au système d'information
 - [Importer des activités](activity-import.md) Synchroniser les activités de recherche depuis une source Excel.

### Utilitaires et utilisation avancée
 - [Administrer Oscar en ligne de commande](oscar-commands.md)
 - [Oscar API](config-api.md) Configurer Oscar pour permettre son accès via une API Rest
 - [Modifier la méthode de génération des PDF](../config-docpdf.md) Permet un gain de performance pour la génération des *certains* documents

## Fonctionnalités optionnelles

 - [Demande d'activité](activity-request.md)
 - [Personnaliser l'export des activités](activities-export.md)
 - [Activier le mode ADAJ](adaj.md)

## Note de version
  - **VERSION 2.11 "MacClane"** ([Note de version](versions/version-2.11.md))
  - VERSION 2.10 "Creed" ([Note de version](versions/version-2.10.md))
  - VERSION 2.9 "Matrix" ([Note de version](versions/version-2.9.md))
  - VERSION 2.8 "Callahan" ([Note de version](versions/version-2.8.md))
  - VERSION 2.7 "Lewis" ([Note de version](versions/version-2.7.md))

 > La version Oscar installée est indiquée en pied de page de l'application

# Développeurs

 - [Ajouter des actions,vues,controlleurs,service dans Zend Framework 3](devnote/mvc.md)
 - [Créer un composant d'interface VUEJS](devnote/vuejs.md)
 - [Installer les drivers Oracle OCI](install-oracle-pp.md)
