# version 2.10 "Creed"

## Appliquer cette mise à jour depuis Oscar 2.8

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)


## Système de recherche des Personnes (2019/05)

Le système de recherche des personnes a été amélioré et permet maintenant de configurer ElasticSearch pour les recherches. [Configuration de la recherche des personnes](./doc/configuration.md#recherche-des-personnes)


## Synthèse (2019/05)

Une option de **synthèse générale (v2)** est accessible depuis la fiche d'une activité. Elle offre une vision globale aux chargés de valorisation sur l'état des heures déclarées pour un projet, par période et par personne, elle permet de contrôler rapidement le cumule des heures : 

![Synthèse des heures](./doc/images/synthes-2-001.png)

En cliquant sur **Détails**, une vue plus précise permet de voir la répartition déclarée par la personne pour la période choisie : 

![Synthèse des heures](./doc/images/synthes-2-002.png)

Un export Excel de ces données sera proposé. A noté que pour les interconnexions avec d'autres application de votre SI, ces informations sont accessibles auformat JSON à la même adresse en ajoutant `&format=json` à la requète.



## Section des documents publiques

Les documents publiques peuvent maintenant être organisés en section. Les sections doivent être configurées dans la partie configuration par l'administrateur.