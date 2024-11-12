# Documentation technique

Bienvenue dans la **Documentation technique** du projet **O.S.C.A.R**.


## Installation et maintenance

S'adresse aux gestionnaires d'application souhaitant installer et configurer une instance d'oscar.

### Installation de base

 - [Procédure d'installation](install-prod.md) : Installation détaillée d'une instance Oscar
 - [Procédure de mise à jour](update.md) : Procédure à appliquer lors d'une mise à jour 
 - [Version DOCKER](../dockerize/README.md) : Déployer une instance via *Docker*

## Documentation complémentaire

### Configuration de base
 - [Authentification (CAS/LDAP)](configuration/config-auth.md) : Configurer les accès à Oscar
 - [Gestion des documents](configuration/config-documents.md) : Configuration de la gestion des documents.
 - [Configuration du moteur de recherche (Elasticsearch)](configuration/config-elasticsearch.md)
 - [Configuration du serveur de tâche (Gearman)](configuration/config-gearman.md)
 - [Configuration du PFI](configuration/config-pfi.md)
 - [Configuration de la distribution des courriels](configuration/config-mailer.md)
 - [Configuration des notifications](configuration/config-notifications.md)
 - [Configuration de la numérotation automatique OSCAR](configuration/config-numerotation.md)
 - [Configuration des feuilles de temps](timesheet.md)
 - [Configuration des dépenses (SIFAC)](configuration/config-sifac.md)
 - [Configuation des signatures](./configuration/config-signature.md)

### Synchronisation au Système d'Information
 - [Connectors Oscar](connectors.md) : Connecter Oscar au système d'information
 - [Importer des activités](activity-import.md) Synchroniser les activités de recherche depuis une source Excel.

### Utilitaires et utilisation avancée
 - [Administrer Oscar en ligne de commande](oscar-commands.md)
 - [Oscar API](config-api.md) Configurer Oscar pour permettre son accès via une API Rest
 - [Modifier la méthode de génération des PDF](configuration/config-docpdf.md) Permet un gain de performance pour la génération des *certains* documents

## Fonctionnalités optionnelles

 - [Demande d'activité](activity-request.md)
 - [Personnaliser l'export des activités](activities-export.md)
 - [Activier le mode ADAJ](adaj.md)


# Développeurs

 - [Ajouter des actions,vues,controlleurs,service dans Zend Framework 3](devnote/mvc.md)
 - [Créer un composant d'interface VUEJS](devnote/vuejs.md)
 - [Installer les drivers Oracle OCI](install-oracle-pp.md)
 - [Modèle de données (version simplifiée)](images/oscar-database-simplified.png)
