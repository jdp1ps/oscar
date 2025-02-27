# 📢 CHANGEMENTS (MASTER/RIPLEY)
## 2025

### Février
 - [fix] Erreur d'indexation lié au typage automatique des valeurs dans elasticsearch. La configuration du mapping a été modifié (**nécessite après mise à jour, une réindexation des activités, organisations et personnes**)
 - [fix] Maj doc sur les champs utilisés dans les dépenses
 - [fix] Le nom du fichier est correctement repris lors du téléchargement d'un document
 - [fix] Erreur d'affichage du type d'activité dans certains cas précis
 - [fix] Erreur lors de la création d'une activité
 - [fix] Recherche textuelle des organisations

### Janvier
 - [fix] Connector : Identifiant sous la forme d'entier pour les personnes
 - [fix] Connector : Erreur lorsque l'identifiant contient un "_"
 - [fix] Erreur lors de la création d'une activité
 - [fix] Implode

## 2024

### Décembre
 - [Up] Critère de tri ajouté à la recherche des organisations.
 - [Up] La colonne indicence financière a été ajoutée pour les exports des projets.

### Novembre 2024
 - [Fix] Erreur sur les filtres 'Plusieurs personnes' et 'Plusieurs organisations'
 - [Fix] Erreur avec les commandes lièes aux dépenses (`spent:list`, `spent:infos`, `spent:accounts`)
 - [Fix] Erreur dans le calcule d'accès aux documents des activités pour les personnes associées à une organisation affectée au projet des activitées.
 - [Fix] La date de dernière autentification est de nouveau mise à jour correctement.
 - [Fix] Erreur lors de la génération des versions Excel des feuilles de temps mensuelles validées pour une personne
 - [Fix] Affichage d'un message d'erreur lors de l'envoi d'un document sans spécifier le type depuis la fiche activité
 - [Fix] Maj du module Signature : Fix un problème avec les observateurs fixes déclarés via l'email. Normalisation de la case des emails.
 - [Up] Ajout d'un connector DB (Oracle) pour les synchronisations des personnes/organisations (doc : [doc/connectors-db.md](doc/connectors-db.md))
 - [Up] Mise à jour des *templates* pour la génération des feuilles de temps (doc: [doc/timesheet.md#personnalisation-des-rendus](doc/timesheet.md#personnalisation-des-rendus)). Permet de simplifier la personnalisation du logo, tout en conservant les *templates* par défaut.
 - [Fix] Les *templates* des feuilles de temps ont été corrigé pour régler un décalage dans les colonnes (Synthèse mensuelle de l'activité)

### Octobre 2024

#### 23 octobre 2024
 - [Up] Une commande a été ajoutée (`php bin/oscar.php infos`) pour permettre d'afficher les détails sur la version installée dans l'interface (remplace l'ancien système à cause des restrictions GIT). Elle crée un fichier `oscar-info.json` placé à la racine des sources. Ce fichier est utilisé pour afficher les informations dans la page /gitlog (lien en pied de page).
 - [Fix] le moteur de recherche des activités dans la fiche projet ne fonctionnait pas (https://redmine.unicaen.fr/Etablissement/issues/58963)