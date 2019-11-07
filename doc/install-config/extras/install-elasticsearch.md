# Elasticsearch

## Installation sous DEBIAN

Cette précédure d'installation a été téstée sur debian Stretch

### Java 1.8.x

Commencer par vérifier la présence de Java

```bash
$ java -version
openjdk version "1.8.0_162"
OpenJDK Runtime Environment (build 1.8.0_162-8u162-b12-1~deb9u1-b12)
OpenJDK 64-Bit Server VM (build 25.162-b12, mixed mode)
```

S'il n'est pas installé, vous devez **installer Java depuis les dépôts officiels** :

```bash
apt-get install default-jre
```


### ElascticSearch

Commencez par vérifier si le paquet est disponible :

```bash
apt search elasticsearch
```


### Ajout du dépôt officiel

Si le paquet n'est pas disponible, vous pouvez ajouter *ElasticSearch* au *sourcelist* debian :

Installez d'abord *apt-transport-https* :

```bash
apt-get install apt-transport-https
```

Puis ajoutez la ligne au sourcelist :

```bash
echo "deb https://artifacts.elastic.co/packages/6.x/apt stable main" | tee -a /etc/apt/sources.list.d/elastic-6.x.list
```

Et chargé la clef :

```bash
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | apt-key add -
```


### Installation

Mettez à jour puis installez :

```bash
apt update
apt install elasticsearch
```


### Vérifier la présence de ElasticSearch

La commande `service --status-all` va vous permettre de lister les services chargés par le système, vous devrier voir une ligne *ElasticSearch* :

```
[ - ] elasticsearch
```

> Attention : le [ - ] indique que le service est INACTIF


### Elasticsearch en tant que service

Utilisez la commande `ps -p 1` pour déterminer le système de gestion de service :

```
PID TTY          TIME CMD
  1 ?        00:00:06 systemd
```

Normalement sous Debian/Ubuntu, vous serez sous **systemd**.

### Service SYSTEMD

```bash
# Installation
systemctl daemon-reload
systemctl enable elasticsearch.service

# Démarrage/arrêt
systemctl start elasticsearch.service
systemctl stop elasticsearch.service
```

Vous pouvez tester l'état du service avec la commande `service elasticsearch status` :

```
● elasticsearch.service - Elasticsearch
   Loaded: loaded (/usr/lib/systemd/system/elasticsearch.service; enabled; vendor preset: enabled)
   Active: active (running) since Tue 2018-06-12 13:58:45 CEST; 4min 9s ago
     Docs: http://www.elastic.co
 Main PID: 12727 (java)
    Tasks: 58 (limit: 4915)
   CGroup: /system.slice/elasticsearch.service
           └─12727 /usr/bin/java -Xms1g -Xmx1g -XX:+UseConcMarkSweepGC -XX:CMSInitiatingOccupancyFraction=75 -XX:+UseCMSInitiatingOccupancyOnly -XX:+AlwaysPreTouch -Xss1m -Djava.awt.headless=true -Dfile.encoding

juin 12 13:58:45 ED209 systemd[1]: Started Elasticsearch.
```


### Service SysV init

(Cette configuration n'a pas été testé et s'appuie sur le documentation officielle d'ElasticSearch)

```bash
# Initialisation du service
update-rc.d elasticsearch defaults 95 10

# Démarrage/arrêt
service elasticsearch start
service elasticsearch stop
```

### Tester le service

Enfin, pour **tester si ElasticSearch réponds** :

```bash
$ curl localhost:9200
{
  "name" : "HaShKey",
  "cluster_name" : "elasticsearch",
  "cluster_uuid" : "uidHash",
  "version" : {
    "number" : "5.6.8",
    "build_hash" : "688ecce",
    "build_date" : "2018-02-16T16:46:30.010Z",
    "build_snapshot" : false,
    "lucene_version" : "6.6.1"
  },
  "tagline" : "You Know, for Search"
}
```
