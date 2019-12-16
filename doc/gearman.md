# Gearman

**Gearman** est un serveur de gestion de tâche. Il se divise en 3 parties indépendantes : 
 - Le daemon qui va archiver les tâches à faire
 - Le client, qui va soumettre des tâches au serveur
 - Le worker, qui pourra interroger le serveur pour savoir si des tâches sont en attente et les réaliser.

## Serveur de Job GEARMAN

Le serveur Gearman est un *daemon* qui va gérer la liste des **jobs** en attente. C'est un application indépendante disponible nativement dans les paquets officiels Debian.

### Installation

Installation via les paquets Debian : 

```bash
apt install gearman-job-server
```

Par défaut, il sera activé en tant que service.


### Statut du serveur

Vous pouvez vérifier l'état du *daemon* avec la commande :  

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

### Monitoring du serveur

Le paquet **gearman-tool** contient l'utilitaire **gearadmin** qui permet de surveiller l'état du serveur. pour les la liste des JOBS en attente : 

```bash
gearadmin --status | sort -n | column -t
```

> Les pipes (`| sort -n | column -t`) servent uniquement à rendre la sortie plus facile à lire.

A noter que dans le cadre de test ou pour vérifier le bon fonctionnement du système - vérifier que le client envoi bien les job au serveur, on peut utiliser une *watcher* : 

```bash
watch "gearadmin --status | sort -n | column -t"
```
La sortie est un tableau de synthèse sous la forme : 

```
.
monJob1    1  0  0
autreJob   0  0  1
fooJob     50 1  1
```

Chaque ligne représente un **job** avec dans le première colonne, le nombre de job en attente, dans le seconde, les job en cours de traitement, et dans la dernière, le nombre de *workers* assignés aux jobs.




## Module PHP

La partie PHP va se charger de 2 choses : Envoyer des **JOBS** à faire au serveur (Classe PHP `GearmanClient`) et traiter des ces jobs en créant des **workers** (Classe `GearmanWorker`). On aura donc 2 utilisations distinctes, **Client** et **Worker**.

On commence par installer le module PHP

```bash
apt install php7.3-gearman
```

### Client

Le **Client** va soumettre au *deamon* Gearman des **Jobs** à faire.

```php
// gearman-client.php
$client = new \GearmanClient();
$client->addServer();
$response = $client->doBackground('fonctionJob', 'Paramètres envoyés');
```

Vous pouvez tester l'envoi du job en ligne de commande : 

```bash
php gearman-client.php 
```


> Pour voir les jobs sur le *deamon* Gearman, vous pouvez utiliser la commande : 
> cela affichera : 
> ```Every 2,0s: gearadmin --status | sort -n |...  ed209: Fri Dec 13 14:25:30 2019
> .
> fonctionJob  1  0  0
> ```

Si vous rééxécuté à nouveau le script, vous verrez s'incrémenter dans la liste des jobs en attente.

> ```
> .
> fonctionJob  2  0  0
> ```

### Worker

Le **Worker** est un processus qui aura la charge de traiter les **jobs** en attente sur les *deamon* Gearman.

```php
// gearman-worker.php
$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction('fonctionJob', 'maFonctionJob');
while($worker->work());

function maFonctionJob(GearmanJob $job) {
    $params = $job->workload();
    echo "Traitement du JOB avec le paramètre : $params"; 
}
```

Vous pouvez executer le worker, il traitera les JOBS en attente, et les nouveaux dès qu'il sont envoyés au *deamon Gearman*.


### GearmanWorker

### GearmanClient

### Worker en tant que service (systemctl)

Configuration pour **systemctl** pour exécuter le worker en tant que service système : 

```bash
# /etc/systemd/system/monworker.service
[Unit]
Description=MONWORKER Service

# Dépendant de Gearman
After=gearmand.service

[Service]
Restart=on-failure
Type=simple

# Paramètres à mettre à jour
User=Utilisateur
ExecStart=/usr/bin/php /path/to/gearman-worker.php
```

Puis lancer le service : 

```bash
systemctl start monworker.service
```

Vous pouvez surveiller la sortie du worker et vérifier que tout se passe correctement : 

```bash
journalctl -u monworker.service -f
```
