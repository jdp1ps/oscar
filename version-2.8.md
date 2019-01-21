# version 2.8 "Callahan" (Janvier 2019)


## Code des LOTS de TRAVAIL obligatoire

Renseigner un code pour un lot de travail est maintenant obligatoire. La commande : 

```bash
$ php public/index.php oscar patch workpackageCode
```

Permet de lister les activités identifiées avec des lots de travail dont le code est vide pour permettre de corriger manuellement depuis la fiche activité le code manquant sur le lot.

## Authentification LDAP !

Pour les instances d'oscar utilisant LDAP, cette version profite d'évolutions de la librairie tiers **UnicaenApp** qui permet maintenant de configurer le champ LDAP utilisé pour l'authentification. Voir la documentation ([Configurer l'identifiant de connexion LDAP](./doc/install-prod.md), partie **Authentification LDAP : Non-Supann**).


Pour appliquer cette mise à jour, suivre la procédure standard détaillée dans le fichier [Procédure de mise à jour Oscar](./doc/update.md)


## Feuille de temps

Refonte complète de l'interface de saisie et de validation des déclarations

[Détails sur le fonctionnement des Feuille de temps](./doc/timesheet.md)


## Autres

 - [Liste du personnel](./doc/liste-personnel.md) 
 - [Masquage de champs de saisie dans la fiche Activité](./doc/configuration.md#activité-formulaire-de-saisie)
 - Ajout d'un écran d'administration / configuration : Il permet d'identifier facilement les problèmes d'accès à certaines partie de l'application en indiquant si le privilèges est manquant.
 - Numérotation Oscar personnalisable
 - Import : Ajout des champs : Statut, TVA, Devise, etc... (voir documentation)
 
 
## FIX
 - Feuille de temps > Déclaration : Le commentaire n'est plus conservé d'une saisie à l'autre
 - Doc > Installation : Commande des privilèges manquante dans le documentation d'installation
 - Doc > Import d'activité : La partie consacrée aux paiements est plus claire (ajout d'exemple)
 - Activité > Numéro : La capacité du champ a été augmentée 
 - Les privilèges 'Voir les notifications planifiées' fonctionne correctement
 - Déclaration : Les jours fériès Lundi de pâques, ascension et pentecôte ont été ajoutés
 - Ajout de LOG d'erreur



