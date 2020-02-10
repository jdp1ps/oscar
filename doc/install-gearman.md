# Installation de Gearman

Gearman est un *daemon* qui se chargera de gérer les tâches Oscar.


## Installation du deamon

L'installation de fait via le gestionnaire de paquet **apt** :

```bash
# Installation de Gearman
apt install gearman-job-server
```

## Installation du client PHP

Vérifier si la librairie Gearman PHP est bien installée avec la commande :

```bash
php bin/oscar.php check:config
```

Si ça n'est pas le cas, installez là :

```bash
apt-get install php7.3-gearman
```

## Installation du service oscarworker

Ensuite il faut configurer le *Worker Oscar* qui se chargera de réaliser les tâches disponibles sur le serveur :

```bash
# on copie le gabarit de configuration du service
cp install/oscarworker.dist.service config/oscarworker.service

# On édite le service
nano config/oscarworker.service
```

Dans le fichier `config/oscarworker.service`, vous devez simplement indiquer le chemin complet vers le fichier PHP **bin/oscarworker.php**.

On va ensuite ajouter le *worker oscar* au service du système.

```bash
# Passage en root
sudo su

# On va dans le dossier des service
cd /etc/systemd/system

# On ajoute la configuration du service dans SYSTEMD avec un lien symbolique
ln -S /var/OscarApp/oscar/config/oscarworker.service oscarworker.service

# On lance le service
service oscarworker start

# On regarde si tout est OK
journalctl -u oscarworker.service -f

# On active le service
service enable oscarworker
```

## Commandes usuelles

### Vérifier le statut du deamon

```bash
service gearman-job-server status
```

### Surveiller les tâches en attentes

```bash
watch "gearadmin --status | sort -n | column -t"
```

### Afficher les logs du OscarWorker

```bash
journalctl -u oscarworker.service -f
```
