# Support Oracle OCI8 pour PHP

> UPDATE : Debian bullseye / PHP7.4

Procédure d'installation du **module PHP OCI8** afin de permettre d'utiliser les connexions à une base de données Oracle en PHP.

Cette procédure se fait en 2 étapes, commencer par récupérer et installer les différents utilitaires pour la connexion à Oracle (utilitaires indépendants de PHP). Puis l'installation du module PHP OCI8.

### Prérequis
 - *phpize* et *pecl* Installation du paquet `php7.4-dev`

## Oracle Instant Client

Les différents utilitaires sont accessibles depuis la page 
https://git.unicaen.fr/open-source/oscar/-/wikis/Installation-OCI

Téléchargez les dans votre `/tmp`


## Installation

```bash
# Extraction des archives
unzip -o /tmp/instantclient-basiclite-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/
unzip -o /tmp/instantclient-sdk-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/
unzip -o /tmp/instantclient-sqlplus-linux.x64-18.5.0.0.0dbru.zip -d /usr/local/

# Lien symbolique "instantclient/sqlplus"
ln -sf /usr/local/instantclient_18_5 /usr/local/instantclient
ln -sf /usr/local/instantclient/sqlplus /usr/local/bin/sqlplus

# Installation via PECL
echo 'instantclient,/usr/local/instantclient' | pecl install oci8-2.2.0

# Configuration PHP
echo "extension=oci8.so" > /etc/php/7.4/apache2/conf.d/30-php-oci8.ini
echo "extension=oci8.so" > /etc/php/7.4/cli/conf.d/30-php-oci8.ini

# Dynamic Library Config
echo "/usr/local/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf
ldconfig
```

Petit *restart* de *Apache*

```bash
service apache2 restart
```


## Problèmes connus

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

