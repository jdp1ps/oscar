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
 - [Configuration de la distribution des courriels](configuration/config-mailer.md)
 - [Configuration du PFI](configuration/config-pfi.md)
 - [Configuration des notifications](configuration/config-notifications.md)

### Configuration spécifique
 - **Activités de recherche**
   * [Configuration des feuilles de temps](configuration/config-timesheet.md)
   * [Dépenses (SIFAC)](configuration/config-sifac.md) : Configuration de la synchronisation des dépense via le PFI depuis SIFAC
   * [Parapheur numérique](./configuration/config-signature.md) : Mise en place de la signature numérique de document avec ESUP
 - **Général**
   - [Demande d'activité](activity-request.md)
 - **Synchronisation au Système d'Information**
   - [Connectors Oscar](connectors.md) : Connecter Oscar au système d'information
   - [Importer des activités](activity-import.md) Synchroniser les activités de recherche depuis une source Excel.

## Maintenance
 - [Administrer Oscar en ligne de commande](oscar-commands.md)

## Autre configuration

- [Oscar API](config-api.md) Configurer Oscar pour permettre son accès via une API Rest
- [Modifier la méthode de génération des PDF](configuration/config-docpdf.md) Permet un gain de performance pour la génération des *certains* documents
- [Personnaliser l'export des activités](activities-export.md)
- [Activier le mode ADAJ](adaj.md)
- [Personnaliser le numéro OSCAR (20xxDRIxxxxxx)](configuration/config-numerotation.md) : Modifier le formalisme de la numérotation automatique de Oscar
- [Export des activité - Champ calculé](./activities-export.md)
- [Configuration PCRU](./configuration/config-pcru.md)


# Développeurs

 - [Ajouter des actions,vues,controlleurs,service dans Zend Framework 3](devnote/mvc.md)
 - [Créer un composant d'interface VUEJS](devnote/vuejs.md)
 - [Modèle de données (version simplifiée)](images/oscar-database-simplified.png)
