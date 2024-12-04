# 📢 CHANGEMENTS

## 2024

### Décembre
 - [Starling] Accès aux LOGS depuis l'interface d'administration
 - [Starling] Ajout du module "Notes" sur la fiche activité
 - [Up] Critère de tri ajouté à la recherche des organisations.
 - [Up] La colonne indicence financière a été ajoutée pour les exports des projets.

### Novembre 2024
 - [Starling] Nouvelle fiche activité
 - [Starling] Système d'étiquette pour les activités
 - [Starling] Les membres/partnaires peuvent être copié/collé d'une activité vers une autre.
 - [Fix] Erreur sur les filtres 'Plusieurs personnes' et 'Plusieurs organisations'
 - [Fix] Erreur avec les commandes lièes aux dépenses (`spent:list`, `spent:infos`, `spent:accounts`)
 - [Fix] Erreur dans le calcule d'accès aux documents des activités pour les personnes associées à une organisation affectée au projet des activitées.
 - [Fix] La date de dernière autentification est de nouveau mise à jour correctement.
 - [Fix] Erreur lors de la génération des versions Excel des feuilles de temps mensuelles validées pour une personne
 - [Fix] Affichage d'un message d'erreur lors de l'envoi d'un document sans spécifier le type depuis la fiche activité
 - [Fix] Maj du module Signature : Fix un problème avec les observateurs fixes déclarés via l'email. Normalisation de la case des emails.
 - [Master:Up] Ajout d'un connector DB (Oracle) pour les synchronisations des personnes/organisations (doc : [doc/connectors-db.md](doc/connectors-db.md))
 - [Master:Up] Mise à jour des *templates* pour la génération des feuilles de temps (doc: [doc/timesheet.md#personnalisation-des-rendus](doc/timesheet.md#personnalisation-des-rendus)). Permet de simplifier la personnalisation du logo, tout en conservant les *templates* par défaut.
 - [Fix] Les *templates* des feuilles de temps ont été corrigé pour régler un décalage dans les colonnes (Synthèse mensuelle de l'activité)

### Octobre 2024

#### 23 octobre 2024
 - [Up] Une commande a été ajoutée (`php bin/oscar.php infos`) pour permettre d'afficher les détails sur la version installée dans l'interface (remplace l'ancien système à cause des restrictions GIT). Elle crée un fichier `oscar-info.json` placé à la racine des sources. Ce fichier est utilisé pour afficher les informations dans la page /gitlog (lien en pied de page).
 - [Fix] le moteur de recherche des activités dans la fiche projet ne fonctionnait pas (https://redmine.unicaen.fr/Etablissement/issues/58963)