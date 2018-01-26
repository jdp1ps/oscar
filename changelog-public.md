# Lasted Build

## Déclarations
 - Il est maintenant possible de *synchroniser* un fichier ICS déjà importé préalablement. Si le déclarant retéléverse un ICS, Oscar analyse parmis les créneaux présent ceux issus d'un import et tente des les mettre à jour automatiquement. Oscar ajoute les nouveaux créneaux mais **il ne supprime pas les créneaux qui ont disparu**
 - Un nouvel onglet dans l'écran d'importation est disponible. Il affiche la liste des ICS importés. Il permet de supprimer les créneaux importés depuis un des imports (en cas d'erreur).
 - Lors de l'import d'un ICS, Oscar essaye de déterminer le lot de travail idéal pour un créneau en s'appyant sur le nom du calendrier (il doit contenir l'acronyme du projet ou CODE de l'activité) et l'intitulé (il doit contenire le code du lot de travail)

# Build 552
 - Patch DB : Fusion des organisations partageant le même code LDAP
 - MaJ de la transmission du connectorID pour le connecteur LDAP
 - MaJ de la transmission du connectorID pour le connecteur Harpège
 - Activités > Export : L'export contient maintenant une colonne par rôle possible pour les personnes/organisations avec les données correspondant
 - Rôle LDAP des personnes : La synchros des personnes via LDAP intègre la copie des affectations (memberOf) pour l'affichage des rôle niveau application
 - Personne (général) : Mise au propre de la localisation et le l'assignation administrative
 - Personne (liste) : On voit maintenant la rôle de la personne hérité des
 - FIX : La mise à jour d'un rôle ne provoque plus la perte du filtre LDAP et des droits qui vont avec
 - Liste des organisations : La recherche se fait également sur le code
 - Liste des organisations : La recherche se fait également sur le siret
 - Liste des organisations : Le filtre par type n'est plus perdu lors d'un changement de page
 

# Build 451
 - Amélioration des performances sur la pages d'index des activités de recherche
 - FIX : Erreur de recherche sur les types d'activités sans préciser le type (mise à jour visuel) 
 - Modification de l'export des versements (ajout d'informations et réagencement plus pertinent des colonnes)
 - Dédoublonnage des personnes/organisations dans l'exportation des activités de recherche
 - FIX : Bug identifié par Marc dans l'export (incohérence sur les organismes / personnes)

# Semaine XX-XX
 - FIX : Reconstruction de l'index de recherche
 - Le status de l'activité apparaît maintenant dans l'en-tête de la fiche détaillée
 - Des icônes ont été ajoutés pour les status suivants et certains pictogramme ont été modifier pour éviter les confusions
 - Le système d'affichage des messages sera plus clair
 - FIX : J'ajout d'un rôle à un acteur déjà présent affiche bien l'acteur dans le dialogue
 - UP : Ajouter un membre/partenaire sans spécifier une organisation/personne provoque une erreur


# Semaine 33-34
 - Documents administratifs : Ajout des liens pour les admins (suppression)
 - FIX : La fusion des organisations est corrigée, un lien a été ajouté dans le rapport de synchronisation
 - Synchronisation des organizations disponible dans l'interface (admin), les personnes sont déplacées correctement
 - Synchronisation des personnes disponible dans l'interface (admin) et mise à jour
 - Synchro Harpège : Synchronisation de l'INM (uniquement) pour éviter les conflicts avec les données LDAP
 - Ajout du champs pays dans le formulaire des organisation
 - Les status Déposé et Refusé ont été ajouté dans les activités


# Semaine 25-26-27

 - Dans la vue Liste des activité de recherche, une option dans **les options de vue** permet d'**exporter les versements au format CSV**
 - Première version des documents administratifs
 - Nouveau status dans les versements :**Écart**
 - FIX : Problème d'affichage des partenaires réglé
 - FIX : Synchro Ldap des personnes (le filtre a été élargi)

# Semaine 23-24

## Versement

La mise en page des versements dans la fiche activité a été modifié :

![Modification de la mise en page](/images/changelog/2016-06-versements.png)

Détails :

![Modification de la mise en page](/images/changelog/2016-06-versements-note.png)

1. Les versements ont une bordure de couleur, verte pour les versements effectués
2. Rouge s'ils sont prévus mais que la date est dépassée
3. Grise s'ils sont prévus
4. Cet encart permet de voir si la somme des versements (prévus + effectués) ne correspond pas au montant initiale prévu



# Semaine 22

- Droits : dans les projets excéssibles en "traversée", seul l'intitulé des activités non-accessible est présentée.

# Semaine 21

- FIX : Bug de perte du status des activités lors d'une modification des informations
- Ajout d'un champ pour préciser le format des déclarations de temps (par défaut "pas de déclarations")
- FIX : Bug d'affichage des membres/partenaires dans les activités en fiche
- UP : Ajout d'un champ de recherche dans la liste des versements pour filtrer par numéro oscar/SAIC
- FIX : La recherche de la page d'accueil dirige vers la nouvelle liste de recherche

La vue pour les déclarations de temps mensuelle progresse

# Semaine 20

- (DATA) Rôle des organisations : L'information 'Composante de gestion' a été mise à jour vers 'Tutelle de gestion' sur les rôles existants au niveau des projets et des activités.
- (UP) Export CSV : Les colonnes monétaires (Montant, versements perçus et prévus) utilisent maintenant une mise en forme permettant de convertir les informations en données monétaires dans les tableurs (Vérifié sur LibreOffice)
- (UP) Export CSV : La colonne "Intitulé" de l'activité a été ajoutée aux données exportées
- (FIX) Export CSV : Le calcules des versements perçus et prévus est corrigé (le montant utilisé un système de cache qui ne se mettait pas correctement à jour), le cache a été supprimé (l'export sera a priori un peu plus lent).
- (FIX) Recherche d'activité : L'acronyme du projet a été ajouté à l'index de recherche, les recherche par Acronyme de projet devraient donner de meilleurs résultats.
- (UP) Recherche d'activité : Un filtre "Sans projet" a été ajouté dans la liste des filtres disponibles
- (UP) La page de maintenance a été modifié, malheureusement, l'extrème vélocité des mises à jour ne vous permettra pas de voir cet écran

---

# Semaine 18-19

## Maintenance des données

- Script : Les "Responsables" sont maintenant "Responsable scientifique", les doublons consécutifs à ce changement de qualification ont été supprimés
- Script : Le rôle "Composante de gestion" est maintenant remplacé par le rôle "Tutelle de gestion"
- Script : Le rôle "Chargé de mission europe" n'est plus pris en charge car il fait doublon avec le rôle "Chargé de valoriastion", en effet, sur le plan applicatif, les 2 étiquettes impliquent des fonctionnalités identiques.
- Script : Le script des statuts a été éxécuté, il n'est pas lancé automatiquement car sera soumis à des évolutions (validation)
- Script : Le script qui déplace (ou copie) les membres et partenaires d'un projet vers ces activités est opérationnel (non lancé en production) est prêt. Une factorisation de l'affichage des membres sera utile afin d'éviter les redondances.

## Évolution

- Nouvelle vue pour la recherche de convention, elle permet l'aggrégation de filtre de recherche ainsi que l'utilisation de filtre par exclusion
- Mise en page des versements a été harmonisés, on y voit maintenant les informations tel que le N° oscar et le PFI directement dans la liste.
- La modélisation pour la déclaration des heures est en cours

# Semaine 16-17

## Évolution
- Export CSV : Les colonnes N°Oscar et type Oscar ont été ajoutées, la composante de gestion a sa propre colonne
- Le menu principal a été réorganisé
- **Lot de travail** : Depuis la fiche d'une activité, les chargés de valorisation peuvent configurer des lots de travail (modification / suppression).
- Une relation entre une personne et une organisation avec un rôle peut être définit depuis **la fiche organisation**.
- Une personne désignée *Responsable Scientifique* d'une structure dispose d'un nouveau menu pour voir les projets de la structure avec les mêmes autorisations que le responsable du projet (lecture total).
- Un script perfectible assure la synchronisation de la relation Personne responsable => Organisation en s'appuyant sur les données LDAP.

## FIX
 - L'édition des rôles avec des dates ne provoque plus d'erreur
 - Le PFI ne s'affiche plus en double dans la fiche activité
 - La date de mise à jour se propage correctement dans les activités/projets
 - La constitution de l'index de recherche intègre correctement les personnes définit au niveau du projet
 - Le total des montant ne ne tiens compte que des versements effectués, l'arrondis est calculé correctement

# Semaine 14-15
 - FIX : La saisie et l'affichage des montants prend en compte les centimes
 - FIX : Pas d'unicité sur l'acronyme du projet
 - FIX : Le N° de pièce dans les versements s'enregistre correctement
 - FIX : Ajout de type mime dans la liste de ceux autorisés pour le téléversement de document
 - FIX : Les personnes qui ne peuvent gérer les versement ne voit plus le bouton "ajouter un versement"
 - FIX : Le datepicker est en français + ergonomie
 - FIX : La date de mise à jour du projet/activité est correctement mise à jour lors d'opérations sur les membres, partenaires, versement, jalons, activité afin de rendre plus cohérent le trie par date de mise à jour, a noter que ce trie est maintenant celui par défaut dans la liste des activité/projet.
 - Mise à jour de la mise en page des (listes) activités , certains rôles sont affichés séparement (Laboratoire, Chargé de valorisation, Responsable scientifique, etc...), le financeur est en couleur pour le distinguer des autres partenaires.
 - Le **monitorage** a été remis en service sur différentes actions (ajout/modification/suppression de partenaire/membres sur projet/activité), certaines actions ont été ajoutées au monitorage (ajout/modification/suppression de jalon/versement), ce fil de vie est visible dans les fiches correspondantes.
 - **Jalons** Un liste permet maintenant de voir tous les jalons, maj du modèle
 - **Versements** Une liste de versement a été ajouté, cette vue permet de voir indépendament des activités le fil des versements et de les gérer (modification, suppression).

---

# semaine 14
 - PROD : Les activités ont été numérotées, désormais les activités sont automatiquement numérotées à la création. Par défaut, l'année est issue des dates 1: Signature, 2: Début de la convention, 3: Date de création
 - Un filtre de période a été ajouté à la liste des activités
 - Fix : pagination des listes métiers

# semaine 13
 - Installation de la PROD (https://oscar.unicaen.fr) / ajouts dans la doc d'informations complémentaires (notamment concernant la gestion du cache de Doctrine)
 - Ajout du SIRET dans le formulaire des organisations
 - Bascule de CSS pour distinguer la prod et la préprod + Fix CSS divers
 - Le Logo OSCAR est maintenant en SVG, il s'adapte à l'environnement chromatique des différentes versions avec brio
 - Fix : Recherche sur l'EOTP
 - Fix : La reconstruction de l'index de Recherche levé une exception à cause d'un partenaire déclaré sans organisation
 - La recherche de l'accueil se fait par défaut sur les activités

 - Liste des activités
  - UI : Les filtres des activités sont affichés si besoin
  - Mise en page des différentes listes métiers
  - Dans la recherche avancée, le système de mémorisation des critères de recherche a été implanté en local


# semaine 12
 - Activités (générale) les listes *métier* des activités ont maintenant un filtre personne pour réduire les activités affichées
 - Petit relooking de l'accueil, ajout d'un logo pour permettre un repérage rapide dans les onglets du navigateur
 - \#6019 bescherelle...
 - Versements : 2 vues ont été ajoutées pour les chargé de valorisation afin de filtrer les versements à venir (1 mois) et ceux en retard (date dépassée)
 - Ajout de la liste des activité à venir et des activités bientôt terminées
 - Export CSV : Ajout des colonnes (Date de signature, ouverture, responsable scientifique, chargé de valorisation, financeurs, laboratoire, composante responsable)
 - Activité : Discipline ajoutée (possibilité d'ajouter plusieurs disciplines)
 - Ajout d'une commande pour consolider les données des personnes (calcule CodeLDAP <> ID harpège)
 - Ajout d'un privilège pour l'affichage des informations administratives
 - Synchronisation des Personnes sur HARPÉGE (récupération de l'INM et création des personnes absentes dans Oscar)

# semaine 11

 - Préprod : Suite aux modification liées à l'implantation des nouveaux types, l'index de recherche a été réinitialisé
 - UP : Synchronisation des nouveaux types d'activité de MC mis en place + synchro des activités
 - FIX : Erreur lors de la récupération du type de contrat (a provoqué une erreur de la synchro), maintenant, les convention synchronisée dont le type n'a pas été trouvé sont importées sans type. Les activités ont été resynchronisées sur la préprod
 - FIX : Restauration des type de convention définies par MC (test en local)
 - FIX Un contrôle des droits a été ajouter sur l'action "supprimer une activité"
 - FIX Un contrôle des droits a été ajouter sur l'action "numéroter une activité"
 - FIX Personne sans LDAP : Une personne connecté via ldap mais non présente dans Oscar levé une erreur car l'objet Person n'été pas trouvée
 - UP #5942 : On peut mainenant créer autmatiquement un nouveau projet à partir d'une activité
 - UP #5941 : Permière version de la vue "Liste des activités sans projet"
 - UP : La fiche activité dispose maintenant d'un petit menu en haut à droite permettant de regrouper les opérations de maintenance
 - FIX #5936 : Versement (MeP)
 - UP Les versements prévisionnels apparaissent désomais dans les jalons mais ne peuvent pas être supprimés
 - UP Versements / Jalons : Une mise en page différentes est appliquées au versement "en retard" et au jalons terminés


# semaine 9-10-11
 - Fix bug CSS et modification des gabarits imbriqués pour éviter des tailles de texte trop petit
 - Fix : Bug de chargement de certains scripts Javascript (surcharge de scripts unicaen pour la compatibilité RequireJS)
 - Organisation :
   - Ajout des champs SIFAC (ID, codePays, N° de TVA CA, Siret, etc...)
   - Synchronisation depuis la base de donnée SIFAC (pour le moment les fournisseurs, clients en cours)
 - Role :
   - Ajout des privilèges
   - Association privilege <> role
   - Intégration de contrôle des privilièges niveau Application, Activité, Projet, Personne et Organisation
   - Fix : Authentification hors Ldap
 - Type :
   - Création d'une table de correspondance entre les types de convention centaure et le référenciel de MC (à déployer)


# Semaine 8
 - Liste des projets, par défaut, la liste des projets est affiché dans l'ordre de création
 - Liste des projets, FIX : Erreur lors d'une recherche via EOTP
 - Activités de recherche : Numérotation automatique

# Semaine 5-6
 - Versement : 2 états (Réalisé/prévisionnel) avec une bascule de validation sur la date selon cet état
 - Activité (dupliquer) La copie se fait en profondeur et inclu la création des membres ET des partenaires de l'activité initiale
 - Activité (supprimer, admin uniquement)
 - Type de date (facette), note : la class OscarFacet contient les "facettes" de base
 - Les jalons scientifiques fourni par MC ont été ajouté à la BdD

 ---

# Semaine 4-5

 - Organisations :
   * Ajout d'un script pour synchroniser les structures depuis LDAP (la synchro Harpège est à l'étude)
   * Les "composantes Gestionnaire" (INSERM, CNRS, UCBN, IFREMER, ARCHADE, ENSICAEN) ont été ajouté à la synchronisation
   * La factorisation des rôles a été ajouté à la synchronisation

 - Affichage des rôles :
   * Les rôles ne se cheveuchent plus
   * Icône pour supprimer le rôle (sans rechargement)
   * Possibilité de selectionner le rôle et de le supprimer avec les touches SUPPR ou BACK_SPACE (quand le role est trop petit pour permettre d'afficher l'icône)
   * La suppression des rôles implique une confirmation
   * Ajout des membres/partenaires via une modal pour éviter un rechargement
   * Ajout d'un role à une personne, s'adapte au context (Activité/Projet)
   * Modification d'un rôle existant
   * Modification de style (un icône indique si le rôle est relatif au projet ou à une activité)

 - Versements : Première version

 - Jalons (dates libres) : Première version

 - MeP des liste d'activité :
   * Affichage du type simplifié (2ème type si >3, sinon affiche le dernier), on peut voir le type en détail au survole
   * L'acronyme du projet est affiché à droite des types si ce dernier est disponible
   * Le numéro SAIC est maintenant affiché

 - Le numéro SAIC est détecté dans le recherche (liste des activité) et adapte la recherche La forme détéctée est XXXXSAIC?. Par exemple 2015SAIC002 recherchera toutes les conventions avec un numéro de convention commençant par 2015SAIC002.

# 4 / 20 Janvier 2016

## UI
- Composant Timewalker
- Calcule du contenu des tooltips (les rôles identiques sur une même période sont regroupés)
- Problème de mise en page générale
- Ajout de filtres dans les activitès de recherche

## Données
- Synchronisation automatique des données mis en préprod
- Status des activité de recherche
- TVA synchronisée

## Dev
- Documentation technique
- Bench PHP7 en version dev

# 17 décembre

## Données : Membres d'un projet
- Jérémie JULOU et Caroline OZOUF ont été retrouvé (Valos manquants)
- Optimisation de la synchro des personnes
- Les Co-responsables ont été synchronisés dans les activités de recherche

## Données : Devise
La devise a été ajoutée au modèle, au formulaire et synchronisé depuis centaure.
Pour les montants qui ne sont pas exprimés en Euros, la conversion est affichée
au survolle. (liste uniquement, sera généralisé).

## Données : TVA
La TVA a été ajouté au modèle. PAS SYNCHRONISÉE DEPUIS CENTAURE

## Données : Rôles des personnes
Les rôles suivants ont été ajoutés :
- Co-responsable
- Chargé de mission Europe
- Consultant
- Doctorant
- Conseiller

## Interface : Liste des personnes
A TESTER, un filtre a été ajouter permettant de préciser le rôle de la personne
que l'on cherche. Il est possible de cumuler les rôles. La recherche est un OU
(Recherche les personnes ayant un des rôles présents dans le filtre). Le rôle
est cherché dans les activités et/ou les projets.

-----
## 8-15 décembre
### Fonctionnalité : Création d'une personne
On peut maintenant créer une personne (nom, prénom, email requis) en passant par le menu administration.

### Fonctionnalité : Selecteur de type d'activité
Les types d'activités ont été actualisés depuis les types de contrat issus de Centaure, un selecteur par ajouté au formulaire des activités.

### Fonctionnalité : Création automatique des projets
Maintenance sur les données, le script de synchronisation a été modifiée pour éviter la présence de certains doublons. L'importation des conventions *Centaure* déclenche maintenant automatiquement la création d'un projet selon différents critères :
 - Si la convention a un PFI, le script cherche un projet avec un PFI identique, si elle trouve, l'activité créé est ajoutée à ce projet,
 - Si l'activité fait référence à un avenant précédent, et que cet avenant correspond à une activité de recherche avec un projet, l'activité est ajoutée au projet correspondant,
 - Si aucune des 2 condition précédente n'est remplie, un projet est créé à partir des données de l'activité.

**Note : ** Je n'ai pas encore clarifié la politique de gestion du PFI

Les doublons ont été supprimé (projet) ainsi que les projets vides

### Fonctionnalité (a finaliser) : Fusion des projets
Permet de regrouper les données de 2 projets dans un seul (activités, membres et partenaires)

### Fonctionnalité : Affecter à un projet
On peut maintenant, depuis la fiche activité, affecter ou réaffecter une activité à un projet

### Fonctionnalité : Simplification des partenaires/membres
Dans la fiche projet, le menu d'administration propose maintenant 2 fonctionnalités permettant de simplifier la distribution des membres et des partenaires.
Elle commence par supprimer les membres/partenaires d'une activité si une affectation identique existe pour le projet.
Ensuite, elle déplace une affectation au niveau du projet si elle est présente dans chaque activités du projet.

### Modèle
Le PFI d'un projet est maintenant calculé depuis les PFI des activités. Les requètes de recherche de projet ont été adaptées à cette évolution. Certains projets ont par conséquent plusieurs PFI. La recherche de projet par PFI a été adapté.

### Fix
 - Lors de la création d'une activité de recherche, le fait de laisser le champ **montant** vide ne provoque plus d'erreur.
 - La requète de recherche des projets a été mise à jour
 - Suite au changement de mode de création automatique des projets, les données en pré-production ont été réimportées

### Developpement
 - Mise en place de la base de test pour évaluer la qualité des traitements des données

## 7 décembre 2015
### Fonctionnalité : Téléversement des documents dans les activités de recherche
Le lien est disponible **dans la fiche d'une activité** à droite du titre.

## 3, 4 décembre 2015
### Fonctionnalités : Type de document
 - Les types de documents issus de centaure sont implémentés dans le modèle Oscar
 - Les noms des fichiers ont été repris tel quel
 - la synchronisation des données a été mis à jour et corrigée (les données sont à jour)
 - Les types de documents sont maintenant affichés dans les listes
 - L'icône selon le type de fichier s'affiche correctement (sauf pour les types "exotiques", dans ce cas icône générique)
 - La liste des documents téléversés a été ajoutée et permettra aux administrateurs de vérifier si l'importation des données fonctionne (voir : [/documents-des-contracts.html](/documents-des-contracts)
 - La mise en page a été adaptée en conséquence

### Dev
 - Mise à jour de la lib Unicaen + FIX BDD suite à l'évolution du modèle dans UnicaenAuth
 - Création de la lib PhpFileExtension (destinée au contrôl des type de fichier)
 - Ajout des types de documents : factorisation des types (VERSION1, VERSION2, VERSION..5) en un seul et gestion du numéro dans la colonne idoine
 - Mise à jour de la synchronisation des documents (Désomais, le blob est pris dans tout les cas, le fichier ne l'est que si le blob est absent)
 - Création d'un *partial* pour les vues type "liste de document" et utilisation généralisée du *partial* dans la liste du projet, du contrat et la liste complète.

---

## 1, 2 décembre 2015
 - Ajustement et évolution des éléments d'interface + standardisation des icones/logiques de présentation.
 - Réorganisation des informations dans la fiche Projet/Activité
 - Ajout de rappel contextuel pour éviter l'amalgame Projet/activité
 - FIX : le lien "fiche" ne peut plus passer derrière l'eotp (rendant impossible le click)
 - Les rôles obsolètes sont cachés dans les items de liste
 - Ajout de la liste des activités *sans projet* dans le tableau de bord
 - Revue des entêtes pour les fiches projet/activité de recherche

## 30 novembre 2015
 - Dans les listes de projet, le "refermer" fonctionne à nouveau.
 - FIX [#4528](https://redmine.unicaen.fr/Etablissement/issues/4528) le Helper des type d'activité n'affiche plus le noeud racine ('Root').
 - FIX [#4527](https://redmine.unicaen.fr/Etablissement/issues/4527) Le projet n'est plus affiché dans la liste des activités de la fiche projet.
 - Mise en page :
  * Item liste des projets (version dépliable) ; Fiche Projet
  * Séparateur logique dans les listes (par date) revu
  * Séparateur logique (discipline dans le fiche personne) réaffiché pour les année

## 20, 23 et 24 novembre 2015
 - Mise en page des Types Activités
 - Développement JS pour l'organisation de l'Arbre (côté IU), glisser-déposer, trie des noeuds
 - Suppression d'une branche (Côté serveur)
 - Insertion (Côté serveur)
 - Déplacement des noeud (Côté serveur)
 - Suppression (Côté serveur)
 - Test de saisie et organisation
 - Intégration des nouveaux types d'activités aux activités de recherche (formulaire + vue)
 - Recherche organisation : n'est plus sensible à la case
 - Fiche personnes : la liste des projets intègre également les projets contenant une activité où la personnes a un rôle. Les projets sont affichés en premier et les activités sans projet sont affichées ensuites.
 - Fiche organisation : la liste des projets intègre également les projets contenant une activité où l'organisation a un rôle. Les projets sont affichés en premier et les activités sans projet sont affichées ensuites.

## 19 novembre 2015
 - Ajout du log des personnes
 - Mise à jour du Helper pour les activités de recherche cliquables
 - Factorisation de l'affichage des membres/partenaires dans la fiche personne, fiche organisation
 - Système d'arbre des types d'activités (conception + prémices)

## 18 novembre 2015
 - Ajout de Logs sur les mouvements des personnes/organization sur les activités de recherche et les projets.
 - Mise à jour de la vue "Fiche Projet"
 - Mise à jour de la vue "Fiche Activité de recherche"
 - Mise à jour du partial "Timewalker"
 - le controlleur générique 'EnrollController' est terminé, il assure
  * L'ajout d'un membre sur un projet
  * L'ajout d'un membre sur une activité
  * L'ajout d'une organisation sur une activité
  * L'ajout d'une organisation sur un projet
  * La suppression d'un membre sur un projet
  * La suppression d'un membre sur une activité
  * La suppression d'une organisation sur une activité
  * La suppression d'une organisation sur un projet
 - contrôle de opérations ci-dessus via l'interface
 - Ajout des membres et des partenaires "hérités" dans le fiche "Activité"


## 17 novembre 2015
 - Factorisation de la vue 'Enroll', doit permettre d'afficher les membres et les partenaires de la même manière
 - Modification sur les entités ProjectMember et ProjectPartner (standardistion avec le Trait `TraitRole`)
 - Ajout des différentes routes pour la gestion des personnes/organisations | activité/projet, ajout et suppression
 - Base de données mise à jour
 - Centralisation de la gestion des rôles au niveau activité de recherche et projet, pour le moment en controlleur gère toutes ces opérations de façon polymorph associé à une patial de vue pour afficher les rôles. (non terminé)

## 16 novembre 2015

 - Factorisation des personnes, un script aggrège les personnes qui ont le même rôle sur tous les contrats pour les affecter au projet.
 - Création automatique de projet à partir des EOTP (PFI), script de synchro simple
 - Modification du calcule des droits admin (test du droit parent dans le cadre des droits imbriqués)
 - Fix : Mise en page de la liste des projets
 - Fix : Mise en page de la fiche projet
 - FIX : Erreur sur les dates lorsque elles sont renseignées
 - Mise en page du formulaire "Nouvelle activité"
 - Remise en place du Doctrine Paginator pour les listes en attendant de trouver mieux (problème de requétage très lent)

## 13 novembre 2015

### Développement
 - Test unitaires sur l'évaluation des dates côté middleware (méthodes isPast, isFuture, isObsolete)
 - Ajout de clause conditionnelle pour les rôles hors date

### UI
 - Mise en page dans la liste des activités de recherche
 - Fix du message de connexion
 - Les cartouches des Membres/Partenaires ont un aspect différents selon que le rôle soit actif (normal), passé (translucide et barré) ou à venir (translucide).
 - Mise en page et présentation des documents dans la fiche "Activité de recherche"

### Préprod
  - Problème de connexion Oracle résolu.
  - Lancement des scripts de synchro un par un pour tester d'éventuels erreurs liées à la mise en prod.
    * Sync personnes : OK
    * Sync documents : OK (Ajouter un clause sur la date d'upload pour éviter de se refaire tt la liste)
    * Sync organisations : OK
    * Sync activités : OK
    * Sync membres activités : OK
    * Sync partenaires activités : OK

## 12 novembre 2015

### Développement
 - Création et modification des activités de recherche OK
 - Préparation du script SH pour la synchro automatique
 - FIX : Valeur pas défaut des dates sur le TraitRole
 - Bug SVN dans PHPStorm Vu (contourné)
 - Installation Oracle sur le serveur Préprod
 - Test

### Préprod
 - Mise à jour et installation


## 10 novembre 2015

### FIX
 - Date de fin correctement enregistrée lors de l'ajout d'un role à une organization ou une personne sur une activité de recherche
 - Libéllé mis à jour dans le formulaire de rôle
 - Les redirections vers les activités de recherche fonctionne après l'jour d'un rôle.
 - Mise en page des listes simples d'activité + layout (montant à droite)

---

### Fonctionnalités
 - Fiche **Activité de recherche / partenaires** : affichage maintenant les rôles dans le temps pour les organisations.
 - Fiche **Activité de recherche / membres** : affichage maintenant les rôles dans le temps pour les personnes.
 - Fiche **Activité de recherche / partenaires** : un bouton *ajouter* permet d'ajouter un nouveau partenaire (organization).
 - Fiche **Activité de recherche / membres** : un bouton *ajouter* permet d'ajouter un nouveau membre (personne).
 - Fiche **Activité de recherche / Partenaires** : Les rôles peuvent être supprimé (nouveau système, à savoir, la supression repose sur un flag, l'identité du supprimant(**Person**) est enregistrée ainsi que la date de suppression).
 - Fiche **Activité de recherche / Membres** : Les rôles peuvent être supprimé (idem).
 - Mise en forme de la liste des **activités de recherche**
 - Mise en forme de la fiche **activités de recherche**

### Développement
 - Mise à jour du `TraitRole` ajout des dates de début et de fin
 - Création d'un *patial* générique pour l'affichage des Rôles *dans le temps*
 - Création de nouveaux controller pour les membres/paretnaires d'une activité
 - Création des actions pour l'ajout des membres/paretnaires
 - Ajout d'un constructeur générique pour initialiser correctement la date de création des Rôles
 - Le service ActivityLog dispose maintenant d'une méthode getPerson qui retourne la personne (Person) associée au compte Ldap actif.

### Debug / Fix
 - FIX : Les date de suppression sont maintenant correctement enregistrées pour les partenaires et les membres d'une **activité de recherche**
 - Les liens automatiques (Helper Links) sur les personnes avec un rôles sont corrigés, l'id transmis est celui de la personne et pas l'id du rôle.
 - Les redirections vers les activités doivent être renommées pour éviter les confusions
 - Fix des HTTP(S)_PROXY sur le serveur

---

## 9 novembre 2015 et avant...

### Générale
 - La copie de *pré-prod* est en ligne à l'adresse <https://oscar-pp.unicaen.fr>
 - La copie en *pré-prod* est passée en mode sécurisé (certificat SSL créé)
 - La page de [CHANGELOG](/changelog) a été ajoutée (en même temps vous voyez ce message...)

### Fonctionnalités
 - Dans la **liste des organisations**, le nombre d'activité de recherche est affiché (pas le nombre de projet)
 - Dans la **fiche organisation**, la liste des activités est affichée
 - Le formulaire **nouvelle organisation** fonctionne correctement
 - Ajout de la liste des activités de recherche (anciennement les *GrantProject*)
 - Outil de recherche ajouté à la vue des **activités de recherche**, la recherche se fait sur l'intitulé ou la description, ou **sur le PFI de manière stricte si ce dernier est standard**.
 - Fiche pour consulter les détails d'une activité de recherche

### Synchronisation avec Centaure

 - Les **activités de recherche** (Convention dans centaure) sont synchronisées, pour le moment, la synchronisation est faite *à plat*, si le script de synchronisation ne parvient pas à déterminer le projet initial, l'activité est synchronisée **sans projet**.
 - Les **personnes** présentent dans Oscar sont synchronisées avec les données issues de Centaure ainsi que celles de LDap.
 - Les **organisations** sont synchronisées avec Centaure.
 - Les relations entres les **activités de recherche** et les **personnes** sont synchronisées à partir des données de centaures.
 - Les relations entres les **activités de recherche** et les **organisations** sont synchronisées à partir des données de centaures.
 - Les outils en ligne de commande de synchronisation ont été mis à jour.
    * `php public/index.php centaure sync contract` (synchro des activités depuis centaure)
    * `php public/index.php centaure sync person` (synchro des personnes depuis centaure)
    * `php public/index.php centaure sync organization` (synchro des organismes depuis centaure)
    * `php public/index.php centaure sync activityOrganization` (synchro de la relation Activité de recherche --- organismes depuis centaure)
    * `php public/index.php centaure sync activityPerson` (synchro de la relation Activité de recherche --- personnes depuis centaure)

### Synchronisation depuis des SI tiers
 - `php public/index.php oscar sync:ldap` (Syncho des personnes depuis LDap)


### Développement
 - Le *refactoring* du modèle **GrantProject** en **activité de recherche** est terminé (quelques traces mineurs subsistent dans les commentaires).
 - Un *Helper* permet d'automatiser les générations des *cartouche* pour les personnes et les organismes
 - Un *Helper* permet de créer des liens automatiques sur les personnes et les organismes
 - Le serveur de test peut maintenant être lancé en mode developpement avec la commande `APPLICATION_ENV=development php -S 127.0.0.1:8000 -t public/ public/index.php` et donc en mode prod avec la commande `APPLICATION_ENV=production php -S 127.0.0.1:8000 -t public/ public/index.php`. Le fix de la variable d'environnement permet également de lancer les scripts en ligne de commande sur le même principe (en developpement, les scripts affichent les message de type DEBUG).
 - Mise à jour de la documentation d'installation
 - Installation d'un module Doctrine tiers [Oro Doctrine Extensions](https://github.com/orocrm/doctrine-extensions) pour débloquer les fonctions Postgresql (et surtout celle de date).
