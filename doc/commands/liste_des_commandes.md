# Oscar en ligne de commande

Oscar propose une interface d'accès en ligne de commande pour faciliter le DEBUG et la maintenance manuelle ou automatique.

## Liste des commandes

Vous pouvez obtenir la liste des commandes disponible en tapant la commande racine : 

```bash
php bin/oscar.php
Console Tool

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help                          Displays help for a command
  list                          Lists commands
 activity
  activity:search-rebuild       Reconstruction de l'index de recherche des activités
 auth
  auth:add                      Ajouter un utilisateur en mode interactif
  auth:info                     Affiche la liste des authentifications
  auth:list                     Affiche la liste des authentifications
  auth:pass                     Modification du mot de passe
  auth:promote                  Permet d'ajouter un rôle applicatif à une authentification
  auth:sync                     Synchronisation des authentifications depuis un fichier JSON
 check
  check:config                  Vérification de la configuration
  check:logger                  Vérification des logs
  check:mailer                  Vérification du mailer
  check:privileges              Vérification et mise à jour des privilèges
  check:sequences-num           Mise à jour automatique de l'incrementation des séquences d'IDs pour les tables
 dev
  dev:commandexample            Petit exemple de commande OSCAR
 elasticsearch
  elasticsearch:query           lancement d'une requête brute
 organizations
  organizations:search          Recherche dans les organisations
  organizations:search-rebuild  Reconstruction de l'index de recherche des organisations
  organizations:sync            Execute la synchronisation des organisations
 person
  person:roles                  Recherche dans l'index de recherche des personnes
  person:syncone                Recherche dans l'index de recherche des personnes
 persons
  persons:purge                 Supprime de la liste des personnes les données non-utilisées
  persons:search                Recherche dans l'index de recherche des personnes
  persons:search-rebuild        Execute la reconstruction de l'index de recherche des personnes
  persons:sync                  Execute la synchronisation des personnes
 spent
  spent:accounts                Affiche la liste des comptes utilisés dans Oscar par masse
  spent:activitylist            Affiche la liste des dépenses des activités
  spent:infos                   Permet d'obtenir les informations sur les dépenses d'un PFI
  spent:list                    Affiche la liste des dépenses
  spent:sync                    Permet d'obtenir les dépenses
  spent:syncall                 Synchronisation des dépenses
 timesheets
  timesheets:declarer           Affiche l'état des déclarations pour un déclarant
  timesheets:declarers          Affiche la liste des déclarants

```

## Précisions sur certaines commandes


### Persons (Personnes)

#### person:roles <ID|LOGIN>

Cette commande permet d'afficher les rôles d'une personne : 

```
php bin/oscar.php person:roles daret

Rôles de Arnaud Daret dans les activités de recherche : 
========================================================

Arnaud Daret est présent sur des projets/activités de recherche en tant que : 
 - Ingénieur
 - Responsable scientifique

Rôles de Arnaud Daret dans les organisations : 
===============================================

La personne Arnaud Daret est présente sur des organisations en tant que : 
 - Directeur de composante

Rôles de Arnaud Daret dans l'application : 
===========================================

La personne Arnaud Daret est présente dans l'application en tant que : 
 - Administrateur
```

### Spent (Dépenses)

#### spent:accounts

Cette commande permet de dresser les listes de compte utilisés dans OSCAR après synchronisation des dépenses. Cette commande est utilisée pour détecter les comptes pour lesquelles la MASSE n'a pas été qualifiée. Dans la sortie de la commande, les compte sans MASSE sont marqué avec la masse **N.D.**.

> Remarque : Si des comptes n'ont pas de masse qualifiée, les utilisateurs ayant un droit de vision sur les dépenses veront un message d'avertissement dans la synthèse des dépenses indiquant que des dépenses sont *hors-masse*.

```
php bin/oscar.php spent:accounts

Comptes utilisés : 
===================

 ----------- ---------------- --------------- -------- ------------------------------------------------------------------------------- 
  Masse(cd)   Masse            Masse Héritée   Compte   Intitulé                                                                       
 ----------- ---------------- --------------- -------- ------------------------------------------------------------------------------- 
              N.D.                             101      Capital                                                                        
              N.D.                             1312     Régions                                                                        
              N.D.                             1315     Collectivités publiques                                                        
              N.D.                             1316     Entreprises publiques                                                          
              N.D.                             1318     Autres                                                                         
              N.D.                             186      Biens et prestations de services échangés entre établissements (charges)       
              N.D.                             6061     Fournitures non stockables (eau, énergie)                                      
              N.D.                             606      Achats non stockés de matière et fournitures                                   
              N.D.                             654      Pertes sur créances irrécouvrables                                             
              N.D.                             65       Autres charges de gestion courante                                             
              N.D.                             658      Charges diverses de gestion courante                                           
              N.D.                             705      Etudes                                                                         
              N.D.                             706      Prestations de services                                                        
              N.D.                             7083     Locations diverses                                                             
              N.D.                             7084     Mise à disposition de personnel facturée                                       
              N.D.                             74       Subventions d'exploitation                                                     
              N.D.                             758      Produits divers de gestion courante                                            
              N.D.                             89       Bilan*                                                                         
  I           Investissement   I               21511    sur sol propre                                                                 
  I           Investissement   I               2154     Matériels industriels                                                          
  I           Investissement                   215      Installations techniques, matériels et outillage industriels                   
  I           Investissement   I               2183     Matériel de bureau et matériel informatique                                    
  I           Investissement   I               2184     Mobilier                                                                       
  I           Investissement                   218      Autres immobilisations corporelles                                             
  I           Investissement   F               6288     Prestations de services externes                                               
  F           Fonctionnement   F               4081     Fournisseurs                                                                   
  F           Fonctionnement   F               4084     Fournisseurs d'immobilisations                                                 
  F           Fonctionnement   F               47       Comptes transitoires ou d'attente                                              
  F           Fonctionnement                   6063     Fournitures d'entretien et de petit équipement                                 
  F           Fonctionnement                   6064     Fournitures administratives                                                    
  F           Fonctionnement                   6067     Fournitures et matériels d'Enseignement et Recherche                           
  F           Fonctionnement                   6068     Autres matières et fournitures                                                 
  F           Fonctionnement   F               6135     Locations mobilières                                                           
  F           Fonctionnement   F               6155     Sur biens mobiliers                                                            
  F           Fonctionnement   F               6156     Maintenance                                                                    
  F           Fonctionnement   F               617      Etudes et recherches                                                           
  F           Fonctionnement   F               6183     Documentation technique                                                        
  F           Fonctionnement   F               6185     Frais de colloques, séminaires, conférences                                    
  F           Fonctionnement   F               6228     Divers                                                                         
  F           Fonctionnement   F               6236     Catalogues et imprimés                                                         
  F           Fonctionnement   F               6237     Publications                                                                   
  F           Fonctionnement   F               6238     Divers (pourboires, dont courant)                                              
  F           Fonctionnement   F               6247     Transports collectifs du personnel                                             
  F           Fonctionnement   F               6248     Divers                                                                         
  F           Fonctionnement   F               6251     Voyages et déplacements                                                        
  F           Fonctionnement   F               625      Déplacements, missions et réceptions                                           
  F           Fonctionnement   F               6256     Missions                                                                       
  F           Fonctionnement   F               6257     Réceptions                                                                     
  F           Fonctionnement   F               626      Frais postaux et de télécommunications                                         
  F           Fonctionnement   F               6281     Concours divers (cotisations)                                                  
  F           Fonctionnement   F               628      Divers                                                                         
  F           Fonctionnement   F               6511     Redevances pour concessions, brevets, licences, marques, procédés, logiciels   
  F           Fonctionnement   F               6516     Droits d'auteur et de reproduction                                             
  P           Personnel        P               6331     Versement de transport                                                         
  P           Personnel        P               6332     Allocations logement                                                           
  P           Personnel        P               6378     Taxes diverses                                                                 
  P           Personnel        P               6411     Salaires, appointements                                                        
  P           Personnel        P               6413     Primes et gratifications                                                       
  P           Personnel        P               6414     Indemnités et avantages divers                                                 
  P           Personnel        P               6415     Supplément familial                                                            
  P           Personnel        P               641      Rémunérations du personnel                                                     
  P           Personnel        P               6451     Cotisations à l'Urssaf                                                         
  P           Personnel        P               6453     Cotisations aux caisses de retraites                                           
  P           Personnel        P               6454     Cotisations aux Assedic                                                        
  P           Personnel        P               6458     Cotisations aux autres organismes sociaux                                      
  P           Personnel        P               6471     Prestations directes                                                           
  P           Personnel        P               6474     Versements aux autres œuvres sociales                                          
  P           Personnel        P               6475     Médecine du travail, pharmacie                                                 
 ----------- ---------------- --------------- -------- ------------------------------------------------------------------------------- 
```

#### spent:activitylist

Affiche la liste des activités ayant des dépenses.

#### spent:infos <PFI>

Affiche la synthèse des dépenses (comme dans la fiche activité) 

```
php bin/oscar.php spent:infos 014CI069

Dépenses pour 014CI069
======================

 ----------------------- ----------- 
  Annexe                  Total      
 ----------------------- ----------- 
  Fonctionnement (F)      -1930.97   
  Investissement (I)      0          
  Personnel (P)           -16435.67  
  Hors-masse              0          
  Nbr d'enregistrements   75         
  TOTAL                   -18366.64  
 ----------------------- ----------- 
```

#### spent:list <PFI>

Affiche la liste des dépenses (comme dans le détail des dépenses depuis la fiche activité).

```
php bin/oscar.php spent:list 014CI069

Dépenses pour 014CI069
======================

 ------------ --------------------------------------------- ---------- -------------------- --------------- ---------- -------------- ------------ ---------- 
  N°Pièce      Description                                   Montant    Date Comptable       Date Paiement   Année      N° Réf Pièce   IDs                    
 ------------ --------------------------------------------- ---------- -------------------- --------------- ---------- -------------- ------------ ---------- 
  0000017144   C_190611_0016_102618                          -248       FG                   20190611        20190624   2019           0030044341   42728 +2  
  0000019697   C_190710_0071_104596                          -14.57     FG                   20190705        20190711   2019           0030053869   42733 +1  
  0000022241                                                 -66.9      FG                   20190805        20190827   2019           0030055979   42727 +1  
  0000023642   Paie 08/2019                                  -5667.48   PG_REM,PG_COT_HCAS   20190910        20190831   2019           0030064997   42719 +8  
  0000022882   C_190711_0018_104660                          -48.4      FG                   20190709        20190903   2019           0030053942   42731 +2  
  0000025220   Paie 09/2019                                  -2833.7    PG_REM,PG_COT_HCAS   20190926        20190926   2019           0030071918   42724 +8  
  0000026810   C_191002_0025_108110                          -101.8     FG                   20190930        20191011   2019           0030077141   42737 +1  
  0000029230   20191024_0039_109559                          -240.34    FG                   20191023        20191106   2019           0030079062   42735 +4  
  0000030973   C_191118_0009_111041                          -112.42    FG                   20191115        20191121   2019           0030080471   42710 +3  
  0000030942   C_191107_0003_110369                          -356.25    FG                   20191106        20191121   2019           0030079820   42706 +1  
  0000031201   *Depl. du  09.10.19 Jsq 09.10.19 Vers LYON    -36.5      FG                   20191118        20191122   2019           0030080533   42707 +1  
  0000033822   Paie 11/2019                                  -5100.74   PG_REM,PG_COT_HCAS   20191204        20191130   2019           0030089081   42680 +8  
  0000032779   C_191119_0018_111115                          -339.63    FG                   20191118        20191205   2019           0030080621   42700 +6  
  0000033438   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -0.14      FG                   20191210        20191211   2019           0030093657   42688 +1  
  0000033437   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -0.61      FG                   20191210        20191211   2019           0030093657   42689 +1  
  0000033439   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -0.11      FG                   20191210        20191211   2019           0030093657   42687 +1  
  0000033429   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -2.26      FG                   20191210        20191211   2019           0030093657   42697 +1  
  0000033430   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -0.03      FG                   20191210        20191211   2019           0030093657   42696 +1  
  0000033431   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -2.16      FG                   20191210        20191211   2019           0030093657   42695 +1  
  0000033432   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -0.19      FG                   20191210        20191211   2019           0030093657   42694 +1  
  0000033433   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -1.33      FG                   20191210        20191211   2019           0030093657   42693 +1  
  0000033434   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -0.26      FG                   20191210        20191211   2019           0030093657   42692 +1  
  0000033435   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -0.58      FG                   20191210        20191211   2019           0030093657   42691 +1  
  0000033436   008 REGIE 014 UFR SANTE,REGIE 014 UFR SANTE   -1.03      FG                   20191210        20191211   2019           0030093657   42690 +1  
  0000034181   20191120_0064_111355                          -75.64     FG                   20191119        20191213   2019           0030093854   42699 +2  
  0000035393   Paie 12/2019                                  -2833.75   PG_COT_HCAS,PG_REM   20191219        20191220   2019           0030097418   42663 +8  
  0000036160   20191211_0044_113000                          -281.82    FG                   20191210        20191223   2019           0030101541   42671 +8  
 ------------ --------------------------------------------- ---------- -------------------- --------------- ---------- -------------- ------------ ----------
```

#### spent:sync <PFI>

Déclenche la synchronisation des dépenses à partir d'un PFI.

> Remarque : Cette commande permet de tester la connexion SIFAC

#### spent:syncall

Déclenche la synchronisation des dépenses à partir de tous les PFI utilisés dans les activités de recherche.

> Remarque : Cette commande est très lente (beaucoup de ligne comptable à charger)