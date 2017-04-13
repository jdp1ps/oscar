# Synchonisation des données issues de Centaure

Oscar se synchronise toute les nuits sur les données contenues dans la base de données *Oracle* de Centaure.

Le script en question est situé dans `./bin/centaure-sync.sh`, ce script *Bash* se contente d'exécurer des commande contenu dans le module *CentaureSync* du projet *Oscar*. Pour fonctionner, les synchronisations necessitent l'installation du module **oci** de PHP qui garantit la prise en charge Oracle. 

Concernant l'importation des documents téléversés par les chargé de valorisation, un montage automatique vers le dossier centaure sur **perro** a été mis en place. Le script se charge de renommer le fichier et de le copier dans le dossier de dépôt définit dans la configuration.

Le script de synchronisation produit un rapport placé par défaut dans `/var/log/oscar-YYYY-MM-DD.log`. *Les erreurs ne sont pas censées être bloquantes*.