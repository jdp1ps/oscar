# 📢 CHANGEMENTS

## 2024

### Novembre 2024
 - [Fix] Maj du module Signature : Fix un problème avec les observateurs fixes déclarés via l'email. Normalisation de la case des emails.
 - [Up] Ajout d'un connector DB (Oracle) pour les synchronisations des personnes/organisations (doc : [doc/connectors-db.md](doc/connectors-db.md))
 - [Up] Mise à jour des *templates* pour la génération des feuilles de temps (doc: [doc/timesheet.md#personnalisation-des-rendus](doc/timesheet.md#personnalisation-des-rendus)). Permet de simplifier la personnalisation du logo, tout en conservant les *templates* par défaut.
 - [Fix] Les *templates* des feuilles de temps ont été corrigé pour régler un décalage dans les colonnes (Synthèse mensuelle de l'activité)

### Octobre 2024

#### 23 octobre 2024
 - [Up] Une commande a été ajoutée (`php bin/oscar.php infos`) pour permettre d'afficher les détails sur la version installée dans l'interface (remplace l'ancien système à cause des restrictions GIT). Elle crée un fichier `oscar-info.json` placé à la racine des sources. Ce fichier est utilisé pour afficher les informations dans la page /gitlog (lien en pied de page).
 - [Fix] le moteur de recherche des activités dans la fiche projet ne fonctionnait pas (https://redmine.unicaen.fr/Etablissement/issues/58963)