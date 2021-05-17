# version 2.12 "Spartan"

## Nouveautès

### Organisations : Nouveau champs

Le modèle des organisations a été enrichi, les champs suivant ont été ajoutés : 

 - `duns` : Numéro DUNS, 
 - `tvaintra`  : TVA Intacommunautaire,
 - `labintel` : Numéro Labintel (CNRS)
 - `rnsr` : Numéro RNSR (Répertoire National des Structures de Recherche)

> Ces champs restent facultatif dans la majorité des cas, mais (un de ces champs est attendu par le module PCRU)


### Activités : Nouveau champs

Les champs : 

 - Source de financement
 - Pôle de compétitivité / Validé par le côle de compétitivité

> Les listes proposent des sources fixes officielles (CNRS)

### Module PCRU

Le module PCRU permet d'automatiser l'extraction des données Oscar vers PCRU. Il propose un nouveau module pour visualiser/gérer les données PCRU d'une activité de recherche depuis la fiche activité :

CAPTURE

L'écran PCRU permet de contrôler les données extraites, mais également de télécharger le fichier de donnée CSV valide PCRU.

Si des informations PCRU sont manquantes, elle seront indiquées ainsi qu'une solution pour résoudre le problème.

Une fois les donnèes valident, 2 solutions : 

 - Rendre les données éligibles pour l'envoi automatique des informations
 - Télécharger manuellement les fichiers pour les soumettre via PCRU

---

## Mise en place technique

### référenciels

- **Pôles de compétitivité** : Le référenciel des pôles de compétitivité peut être actualisé automatiquement depuis l'interface (Configuration et maintenance > Nomenclatures > Référenciel des pôles de compétitivité), le bouton **Actualiser** permet de charger automatiquement le référenciel.

 - **Source de financement** : Le référenciel des sources de financement peut être actualisé automatiquement depuis l'interface (Configuration et maintenance > Nomenclatures > Référenciel des sources de financement), le bouton **Actualiser** permet de charger automatiquement le référenciel.

### PCRU

Le module PCRU permet de gérer et d'automatiser les transmissions d'information avec PCRU.



Vous pouvez activer le module PCRU depuis l'interface d'administration (Configuration et maintenance > Modules > PCRU)
 - PCRU (Privilèges)

