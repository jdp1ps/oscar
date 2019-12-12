# Gearman

**Gearman** est un gestionnaire de tâche 


## Serveur de Job

Installation via les paquets Debian : 

```bash
apt install gearman-job-server
```

Quelques commandes : 

```bash
# Status du deamon
service gearman-job-server status
```

```
● gearman-job-server.service - gearman job control server
   Loaded: loaded (/lib/systemd/system/gearman-job-server.service; enabled; vendor preset: enabled)
   Active: active (running) since Thu 2019-12-12 12:05:44 CET; 2min 23s ago
     Docs: http://gearman.info/
 Main PID: 16302 (gearmand)
    Tasks: 7 (limit: 4915)
   CGroup: /system.slice/gearman-job-server.service
           └─16302 /usr/sbin/gearmand --pid-file=/run/gearman/gearmand.pid --listen=localhost --daemon --log-file=/var/log/gearman-job-server/gearmand.log

déc. 12 12:05:44 bouvry-Precision-7520 systemd[1]: Starting gearman job control server...
déc. 12 12:05:44 bouvry-Precision-7520 systemd[1]: Started gearman job control server.
```

## Monitoring du serveur

Le paquet **gearman-tool** contient l'utilitaire **gearadmin** qui permet de surveiller l'état du serveur. pour les la liste des JOBS en attente : 

```bash
gearadmin --status | sort -n | column -t
```

> Les pipes (`| sort -n | column -t`) servent uniquement à rendre la sortie plus facile à lire.

A noter que dans le cadre de test ou pour vérifier le bon fonctionnement du système - vérifier que le client envoi bien les job au serveur, on peut utiliser une *watcher* : 

```bash
watch "gearadmin --status | sort -n | column -t"
```



## Module PHP

La partie PHP va se charger de 2 choses : Envoyer des JOBS à faire au serveur (GearmanClient) et traiter des JOBS (GearmanWorker). On aura donc 2 utilisations distinctes, Client et Worker.

On commence par installer le module PHP

```bash
apt install php7.3-gearman
```

### Client



### Worker




