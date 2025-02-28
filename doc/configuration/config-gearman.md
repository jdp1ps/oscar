# OSCARWORKER

## Installation

### Gearman

Le serveur Gearman permet de différer l'exécution de certaines opérations couteuses. Il faut commencer par installer le serveur de JOB sur le système :

```bash
apt install gearman-job-server
```
 
Vous pouvez vérifier que le serveur est bien lancé avec la commande : 

```bash
systemctl status gearman-job-server
```
 
Résultat : 

```bash
● gearman-job-server.service - gearman job control server
   Loaded: loaded (/lib/systemd/system/gearman-job-server.service; disabled; vendor preset: enabled)
   Active: active (running) since Thu 2019-12-12 12:05:44 CET; 2min 23s ago
     Docs: http://gearman.info/
 Main PID: 16302 (gearmand)
    Tasks: 7 (limit: 4915)
   CGroup: /system.slice/gearman-job-server.service
            └─16302 /usr/sbin/gearmand --pid-file=/run/gearman/gearmand.pid --listen=localhost -daemon --log-file=/var/log/gearman-job-server/gearmand.log

déc. 12 12:05:44 bouvry-Precision-7520 systemd[1]: Starting gearman job control server...
déc. 12 12:05:44 bouvry-Precision-7520 systemd[1]: Started gearman job control server.
```

### Oscarworker

Installation du module Gearman de PHP : 

```bash
# Installation du module Gearman PHP
apt install php8.2-gearman
```

Création de *Oscarworker* : 

```bash
# on copie le gabarit de configuration du service
cp install/oscarworker.dist.service config/oscarworker.service

# On édite le service
nano config/oscarworker.service
```

Dans le fichier `config/oscarworker.service`, vous devez simplement indiquer le chemin complet vers le fichier PHP **bin/oscarworker.php**.

```ini
# Fichier config/oscarworker.service
[Unit]
Description = OSCAR Worker
After = gearmand.service
StartLimitIntervalSec = 60
StartLimitBurst = 3

[Install]
WantedBy = multi-user.target

[Service]
Restart = on-failure
Type = simple
ExecStop = /bin/kill -s TERM $MAINPID
Restart = always
RestartSec = 30

# ------------------------------------------------------------>>>
# >>> Mettre le chemin complet vers bin/oscar-worker.php
ExecStart = /usr/bin/php /var/OscarApp/oscar/bin/oscar-worker.php
# ------------------------------------------------------------<<<

User = root
```

On va ensuite ajouter le *worker oscar* au service du système.

```bash
# On va dans le dossier des service
cd /etc/systemd/system

# On ajoute la configuration du service dans SYSTEMD avec un lien symbolique
ln -s /var/OscarApp/oscar/config/oscarworker.service oscarworker.service

# On active le service
systemctl enable oscarworker.service

# On lance le service
service oscarworker start
```

```bash
# Voir la liste des services
systemctl list-units --type=service

```

Vous pouvez surveiller le *Worker Oscar* avec la commande : 

```bash
# On regarde si tout est OK
journalctl -u oscarworker.service -f
```

A cette étape, le serveur Gearman est opérationnel et le Worker Oscar est installé.

## Informations complémentaires

### Modifier l'URL du serveur de job Gearman

Oscar permet si besoin de modifier l'URL du serveur GEARMAN, pour cela, ajouter dans la configuration Oscar `config/autoload/local.php` une clef **gearman-job-server-host** dans la section **oscar** :

```php
<?php
return [
    // ...
    'oscar' => [
        // Paramètre utilisé en PHP avec la méthode addServer(<ICI>)
        'gearman-job-server-host' => 'localhost'
    ]
];
```


### Tester le worker en ligne de commande

```bash
php bin/oscar.php check:config
```

### Monitorer l'activité du worker

```bash
journalctl -u oscarworker.service -f
```



      