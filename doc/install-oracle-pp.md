# Support Oracle OCI8 pour PHP

Procédure d'installation du **module PHP OCI8** afin de permettre d'utiliser les connexions à une base de données Oracle en PHP.

Cette procédure se fait en 2 étapes, commencer par récupérer et installer les différents utilitaires pour la connexion à Oracle (utilitaires indépendant de PHP). Puis l'installation du module PHP OCI8.


## Oracle Instant Client

Les différents utilitaires sont accessibles depuis la page <https://www.oracle.com/database/technologies/instant-client/linux-x86-64-downloads.html>

Vous devrez récupérer au minimum  : 

 - Basic Package (ZIP) instantclient-basic-linux.x64-X.X.X.X.dbru.zip
 - SDK Package (ZIP) instantclient-sdk-linux.x64-X.X.X.X.dbru.zip


Dans cet exemple, je vais placer les utilitaires dans le dossier **/opt/oracle** : 

```bash
mkdir -p /opt/oracle
cd !$
```

On va dézipper les 2 archives obtenues : 

```bash
unzip  /path/to/instantclient-basic-linux.x64-X.X.X.X.dbru.zip
unzip  /path/to/instantclient-sdk-linux.x64-X.X.X.X.dbru.zip
```

Vous devriez avoir dans votre dossier **/opt/oracle** un dossier **instantclient_X_X**


##  PHP7.3 OCI

## LIBAIO1

On commence  par installer la  librairie **libaio**

```bash
$ apt install libaio
```

### PECL

Pour l'installation du module OCI8 de PHP7.3, on va utiliser **pecl**
 
Pour installer PECL : 

```bash
$ apt install php-pear
$ pecl version
  PEAR Version: 1.10.8
  PHP Version: 7.3.11-1+ubuntu18.04.1+deb.sury.org+1
  Zend Engine Version: 3.3.11
  Running on: Linux ED209 #67-Ubuntu SMP Thu Aug 22 16:55:30 UTC 2019 x86_64
```

PECL a besoin de **phpize** qui est inclus dans la librairie **php7.3-dev** pour compiler certains module (dont OCI8 qui nous interesse): 

```bash
$ apt install php7.3-dev
$ phpize -v
Configuring for:
PHP Api Version:         20180731
Zend Module Api No:      20180731
Zend Extension Api No:   320180731
```


### OCI8 

```bash
pecl install oci8
```

Enfin, on installe OCI8. Vous devrez renseigner l'emplacement de l'instantclient installé à l'étape précédente :
 
la réponse sera : **instantclient,/opt/oracle/instantclient_X_X**



### PHP.INI

On va maintenant activer le module PHP (OCI8) dans le PHP.INI.
Pensez à le faire pour les PHP CLI et SERVEUR
  

```
# Extrait du fichier php.ini
;extension=interbase
;extension=ldap
;extension=mbstring
;extension=exif      ; Must be after mbstring as it depends on it
;extension=mysqli

# ON ACTIVE  OCI8
extension=oci8  ; Use with Oracle Database 12c Instant Client

;extension=odbc
;etc...
```
    

### libnnz19.so !

Si vous testez la présence du module : 

```bash
php -i | grep oci8
```

Vous devriez obtenir un *warning* concernant la librairie **libnnz19.so** : 
 
```
PHP Warning:  PHP Startup: Unable to load dynamic library 'oci8' (tried: /usr/lib/php/20180731/oci8 (/usr/lib/php/20180731/oci8: cannot open shared object file: No such file or directory), /usr/lib/php/20180731/oci8.so (libnnz19.so: cannot open shared object file: No such file or directory)) in Unknown on line 0
```

Cette librairie est présente dans le dossier **/opt/oracle/instantclient_X_X**, et **oci8.so** en a besoin pour fonctionner : 

```
# Voir les dépendances de OCI8.so avec la  commande LDD
$ ldd /usr/lib/php/20180731/oci8.so 
	linux-vdso.so.1 (0x00007fff8c935000)
	libclntsh.so.19.1 => /opt/oracle/instantclient_19_3/libclntsh.so.19.1 (0x00007f8c6cff4000)
	libc.so.6 => /lib/x86_64-linux-gnu/libc.so.6 (0x00007f8c6cc03000)
	libnnz19.so => not found
	libdl.so.2 => /lib/x86_64-linux-gnu/libdl.so.2 (0x00007f8c6c9ff000)
	libm.so.6 => /lib/x86_64-linux-gnu/libm.so.6 (0x00007f8c6c661000)
	libpthread.so.0 => /lib/x86_64-linux-gnu/libpthread.so.0 (0x00007f8c6c442000)
	libnsl.so.1 => /lib/x86_64-linux-gnu/libnsl.so.1 (0x00007f8c6c228000)
	librt.so.1 => /lib/x86_64-linux-gnu/librt.so.1 (0x00007f8c6c020000)
	libaio.so.1 => /lib/x86_64-linux-gnu/libaio.so.1 (0x00007f2714183000)
	libresolv.so.2 => /lib/x86_64-linux-gnu/libresolv.so.2 (0x00007f8c6be05000)
	/lib64/ld-linux-x86-64.so.2 (0x00007f8c7122e000)
	libclntshcore.so.19.1 => not found
```

Vous pouvez utiliser les liens symboliques, ou ajouter au système une configuration pour lui préciser que le dossier /opt/oracle/instantclient_X_X contient des librairies dynamiques.

#### LD.CONF

Nous allons ajouter un fichier de configuration dans le dossier **/etc/ld.so.conf.d/** pour indiquer au système l'emplacement de l'*instantclient* afin qu'il puisse trouver les librairies **.so** manquantes : 

```bash
# Création du fichier /etc/ld/oci8.conf avec  l'emplacement du instantclient
echo "/opt/oracle/instantclient_19_3" >> /etc/ld.so.conf.d/oci8.conf
```

Maintenant, il faut recharger  la configuration  :

```bash
ldconf
```

On peut vérifier que tout est OK avec **ldd** : 

```bash
#  Maintenant tout est OK
ldd /usr/lib/php/20180731/oci8.so
	linux-vdso.so.1 (0x00007ffc2e3f8000)
	libclntsh.so.19.1 => /opt/oracle/instantclient_19_3/libclntsh.so.19.1 (0x00007f2715aa1000)
	libc.so.6 => /lib/x86_64-linux-gnu/libc.so.6 (0x00007f27156b0000)
	libnnz19.so => /opt/oracle/instantclient_19_3/libnnz19.so (0x00007f2714f68000)
	libdl.so.2 => /lib/x86_64-linux-gnu/libdl.so.2 (0x00007f2714d64000)
	libm.so.6 => /lib/x86_64-linux-gnu/libm.so.6 (0x00007f27149c6000)
	libpthread.so.0 => /lib/x86_64-linux-gnu/libpthread.so.0 (0x00007f27147a7000)
	libnsl.so.1 => /lib/x86_64-linux-gnu/libnsl.so.1 (0x00007f271458d000)
	librt.so.1 => /lib/x86_64-linux-gnu/librt.so.1 (0x00007f2714385000)
	libaio.so.1 => /lib/x86_64-linux-gnu/libaio.so.1 (0x00007f2714183000)
	libresolv.so.2 => /lib/x86_64-linux-gnu/libresolv.so.2 (0x00007f2713f68000)
	/lib64/ld-linux-x86-64.so.2 (0x00007f2719cdb000)
	libclntshcore.so.19.1 => /opt/oracle/instantclient_19_3/libclntshcore.so.19.1 (0x00007f27139c8000)
```

