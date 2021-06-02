# Configuration du module PCRU

Le module PCRU de Oscar permet d'automatiser la soumission des contrats de recherche depuis Oscar vers les systèmes PCRU.

## Chargement des référentiels

La qualification pour PCRU implique de compléter les informations sur la contrat de recherche avec de nouvelles données : 

 - Source de financement
 - Pôle de compétitivité
 - Type de contrat

Ces informations ont été ajoutées au modèle de données (optionnelles) mais implique de charger un référentiel fournit par la CNRS.

Rendez-vous dans **Administration > Configuration et maintenance > Modules > PCRU** puis, cliquer sur *Mettre à jour les référentiels* 

![Configuration PCRU](images/pcru-config.png)

Cette opération va actualiser les référentiels suivant : 

 - Liste des pôles de compétitivité
 - Liste des sources de financement
 - Liste des types de contrat (PCRU)

## Correspondance des types de contrat OSCAR > PCRU

Les types de contrats sont déjà proposés dans Oscar via le référentiels **Type d'activité**, PCRU impose une liste de contrat spécifique. Afin de vous laissez libre dans l'organisation des types d'activité, Vous allez devoir configurer la correspondance effective entre les types PCRU, et leur équivalent dans OSCAR. Pour cela, rendez-vous dans **Administration > Configuration et maintenance > Modules > PCRU > Correspondance des types de contrat** puis, cliquer sur *Configurer*

## Privilèges

Pensez à activer les privilèges idoines aux rôles habilités à gérer les données PCRU

 - Voir les données PCRU depuis la fiche activité
 - Activer PCRU sur une activité (et autoriser à déclencher les soumission)
 - Voir la liste des données PCRU et leur état
 - Lancer le transfert manuel

## Envoi des données PCRU

La communication des donnèes vers PCRU se fait via FTP, l'accès au serveur PCRU peut être configuré depuis l'interface d'administration.