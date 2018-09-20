# version 2.7 "Lewis" (Juin 2018)

Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)


## Générale

 - Mise à jour de l'interface pour la gestion des jalons et ajout d'une option "supprimer"
 - L'écran de paramètre utilisateur a été mis à jour
 - Système de mail pour les jalons (Voir DOC)
 - Refonte de l'export des activités
 - La personne ayant pris en charge un jalon est affichée dans l'interface

## Administration
 - Le script de test de configuration a été mis à jour
 - Le script de test des mails à été amélioré


## Importation
 - la procédure de conversion des fichiers CSV en JSON accepte maintenant des valeurs multiples dans la configuration en utilisant un séparateur (DOC à jour)
 - Le champ utilisé pour stoquer l'identifiant de synchronisation des activités à été étendu à 128 caractères
 
 
## Feuille de temps

Plusieurs amméliorations/Fix sont en rapport avec la saisie et la validation des déclarations. Les récents changements de ce procéssus on mis en suspends cette partie de l'application. Elle reste fonctionnel, mais ne doit pour le moment pas être utilisée

 - Le formulaire n'a plus de champs Feuille de temps car inutile depuis la mise en place des lots. Les calendrier ne se chevauchent plus sous Firefox 60.x +