# version 2.11 "MacClane"

## Upgrade : Zend Framework 3

La version *MacClane* fait suite à la mise à jour précédente (Passage à PHP 7.3). Oscar est maintenant basé sur la version 3 du framework **Zend**. Malgrès de nombreuses vérifications, pensez à prévoir une phase de test pour vérifier que toutes les fonctionnalités sont bien opérationnelles sur votre système.

Cette mise à niveau permet de bénéficier des dernières mises à jour de sécurité des systèmes linux, et rend Oscar compatible avec Postgresql 10.

## Nettoyage du dépôt GIT

L'historique du dépôt GIT a été modifié afin d'en supprimer des archives volumineuses et inutiles depuis plusieurs version (< Creed). **Vous devriez donc au moment du *fetch* constater que vous avez sur votre copie locale plusieurs milliers de *commits* en attente**. C'est normal. L'utilisation d'un *reset* à la place d'un *pull* réglera le problème.

## Nouveautés

 - **Feuille de temps > déclarant** :  Modification du système de saisie des commentaires. Le déclarant peut maintenant saisir son commentaire au fil de l'eau depuis la vue mois. Cette modification permet de simplifier la gestion des commentaires, et de ne pas perdre les commentaires en cas d'annulation d'une déclaration.

 - **Export des activités > Champs calculés** : L'administrateur peut maintenant configurer des champs calculés pour les sorties Excel/CSV. Exemple disponible dans [Configurer les champs calculés dans les export OSCAR](../activities-export.md)

 - **Utilitaire en ligne de commande** : Les commandes Oscar (php public/index.php) ont commencé à être migrées progressivement vers le nouveau système de commande (basé sur le composant "Console" de Symfony, tel que recommandé par l'équipe Zend). La liste des commandes disponibles est accessible via la commande (php bin/oscar.php).

 - **Nouveau projet / nouvelle activité** : Le privilège permettant de créer des activités/projets était initialement réservé au niveau application (et donc effectif uniquement pour les rôles déterminés au sein de l'application). Ce privilège a maintenant été étendu aux rôles liès aux organisations afin de permettre une meilleure décentralisation de l'ajout de projet/activité. Pour les droits obtenus via l'organisation, une zone de saisie supplémentaire sera visible dans les formulaires pour y renseigner le rôle de l'organisation de rattachement (la liste des rôles sera limitée aux rôles donnant un accès NB: c'est cette affectation qui par la suite donnera accès à l'activié/projet créé).
 
 - **Oscar API** : Première version de l'API Oscar. Cette API permet à d'autre  application du SI d'accéder à Oscar en lecture
 
 - **Gearman** Utilisation d'un serveur de gestion des tâches (**Gearman**) afin d'optimiser certaines opérations : Génération des notifications, planification de la reconstruction/optimisation des indices de recherche.
 
## Procédure de mise à jour

### Préparation et mise à jour des sources

Commencez par faire une sauvegarde de vos données ainsi que de l'installation Oscar avant de mettre à jour les sources

> **Attention**, la maintenance du dépôt GIT va provoquer des conflits si vous utiliser `git pull`.

Puis récupérer les sources

```bash
# Actualisation du dépôt local
git fetch

# Réinitialiser la copie local à partir de la source distante
git reset --hard origin/creed

# Basculez sur la branche "Macclane"
git checkout maccclane
```


### Mise à jour des librairies tiers PHP

Puis lancer l'installation des librairies PHP

```bash
# Installation des libs PHP
composer install
```


### Gearman

Suivez la procédure : [installation et configuration de Gearman](../config-gearman.md)

### Mise à jour du modèle

Puis on mets à jour le modèle : 

```bash
php vendor/bin/doctrine-module orm:schema-tool:update --force
```

### Mise à jour des privilèges
 
```bash
php bin/oscar.php check:privileges
``` 

 
## En cours

 - *Dockerisation* Des travaux sont en cours afin de rendre possible d'utiliser **docker** pour le déploiement de Oscar. Docker permet de simplifier grandement (et d'automatiser) le déploiement des applications et de garantir leurs compatibilités sous différents systèmes..
 
 - **Dépense** (expérimental) Les écrans et la connexion SIFAC sont toujours en cours, les accès aux écrans de synchronisation des dépenses basé sur le journal des pièces sont disponibles (Avec les privilèges associés). Propose pour le moment des données brute
 
 - **Écran de budget** (UI uniquement) Sera à préciser quand la partie dépense sera plus avancée
 
 - **Plan comptable** Écran de gestion du plan comptable à utiliser dans l'écran du budget
 
## Correctifs

 - Augmentation des résultats des recherches pour les organisations
 - Augmentation des résultats des recherches pour les personnes
