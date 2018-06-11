# Elasticsearch

## Installation sous DEBIAN

Commencer par vérifier la présence de Java

```bash
$ java -version
openjdk version "1.8.0_162"
OpenJDK Runtime Environment (build 1.8.0_162-8u162-b12-1~deb9u1-b12)
OpenJDK 64-Bit Server VM (build 25.162-b12, mixed mode)
```

S'il n'est pas installé, vous devez **installer Java depuis les dépôts officiels** :

```bash
$ apt-get install default-jre
```

Puis **installer ElasticSearch** à partir des dépôts Debian officiels :

```bash
$ sudo apt-get install elasticsearch
```

Pour **configurer ElasticSearch en tant que service** :

```bash
$ sudo update-rc.d elasticsearch defaults 95 10
```

ou bien

```bash
$ sudo systemctl enable elasticsearch.service
```

Pour **lancer le service ElasicSearch** :

```bash
$ sudo -i service elasticsearch start
```

ou bien

```bash
$ sudo systemctl start elasticsearch.service
```

Pour **interrompre le service ElasticSearch** :

```bash
$ sudo -i service elasticsearch stop
```

ou bien

```bash
$ sudo systemctl stop elasticsearch.service
```

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