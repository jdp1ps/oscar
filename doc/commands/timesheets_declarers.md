# timesheets:declarers [IDPERSON]

Cette commande **sans argument** retourne la liste des déclarants présents dans OSCAR : 

```
$ php bin/oscar.php timesheets:declarers

Déclarants
==========

 ------- ------------------------- ------------------------------------------------------- ---------------- 
  ID      Déclarant                 Affectation                                             Déclaration(s)  
 ------- ------------------------- ------------------------------------------------------- ---------------- 
  5063    Stephane Bouvry           Pôle Développement                                      1               
  4632    Arnaud Daret              Pôle Développement                                      1               
  6064    Emilie Lenel              Centre d'Enseignement Multimédia Universitaire (CEMU)   2               
  4803    Jerome Poineau            UFR des Sciences                                        1               
  7012    Pablo Cubides Kovacsics   UFR des Sciences                                        1               
  11600   Vlere Mehmeti             UFR des Sciences                                        1               
  6023    Amelie Lefranc            Centre d'Enseignement Multimédia Universitaire (CEMU)   2               
  4048    Pascal Claquin            UFR des Sciences                                        2               
  4958    Jeanine Berthier          Centre d'Enseignement Multimédia Universitaire (CEMU)   2               
  4192    David Lemeille            Centre de Recherche en Environnement Côtier (CREC)      1               
  7121    Axelle Lesieur            UFR des Sciences                                        1               
  4677    Gaelle Ruiz               Direction de la Communication                           2               
  4381    Jean-Paul Lehodey         Centre de Recherche en Environnement Côtier (CREC)      1               
  4992    Clotilde Blondel          Centre d'Enseignement Multimédia Universitaire (CEMU)   1               
  4037    Isabelle Mussio           UFR des Sciences                                        1               
  4047    Anne-Marie Rusig          UFR des Sciences                                        1               
 ------- ------------------------- ------------------------------------------------------- ---------------- 
```

En utilisant l'identifiant (colonne ID) en argument, vous obtenez la synthèse des informations pour ce déclarant : 

```
$ php bin/oscar.php timesheets:declarers 4047

Déclarant Anne-Marie Rusig
==========================

Lots identifiés
---------------

 ---------- ------ ------------------ ------------------- ------------------------------------------------- 
  Projet     Lot    Début              Fin                 Intitulé                                         
 ---------- ------ ------------------ ------------------- ------------------------------------------------- 
  MARINEFF   MT 1   Sun 1 April 2018   Sat 30 April 2022   Production et immersion de l'opération pilote 1  
  MARINEFF   MT 2   Sun 1 April 2018   Sat 30 April 2022   Production et immersion de l'opération pilote 2  
  MARINEFF   MT 3   Sun 1 April 2018   Sat 30 April 2022   Formation des professionnels                     
  MARINEFF   MT C   Sun 1 April 2018   Sat 30 April 2022   Module de travail : Communication                
  MARINEFF   MT M   Sun 1 April 2018   Sat 30 April 2022   Module de travail : Management                   
 ---------- ------ ------------------ ------------------- ------------------------------------------------- 

Périodes
--------

Du 2018-04-01T00:00:00+02:00 au 2022-04-30T00:00:00+02:00
 --------- ---------- -------- --------- ------- 
  Période   Conflict   Total    Nbr Lot   Jours  
 --------- ---------- -------- --------- -------   
  2018-10   Non        184.00   5         31     
  2018-11   Non        168.00   5         30     
  2018-12   Non        157.50   5         31     
  2019-01   Non        174.50   5         31     
  2019-02   Non        157.50   5         28     
  2019-03   Non        168.00   5         31     
  2019-04   Non        166.00   5         30     
  2019-05   Non        160.00   5         31     
  2019-06   Non        138.50   5         30     
  2019-07   Non        177.50   5         31     
  2019-08   Non        159.50   5         31     
  2019-09   Non        167.25   5         30     
  2019-10   Non        182.50   5         31     
  2019-11   Non        152.00   5         30     
 --------- ---------- -------- --------- ------- 
```