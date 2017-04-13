# Installation PHP (support Oracle)

## Instant client

```bash
# pour que la commande ./configure blabla fonctionne ;)
cd /opt/
# Installation des bouzins Oracle, l'Instant Client
wget http://download.oracle.com/otn/linux/instantclient/121020/instantclient-basic-linux.x64-12.1.0.2.0.zip

# Et le SDK
wget http://download.oracle.com/otn/linux/instantclient/121020/instantclient-sdk-linux.x64-12.1.0.2.0.zip

echo 'si téléchargement via wget est impossible => voir sur http://www.oracle.com/technetwork/topics/linuxx86-64soft-092277.html'

# On dézip
unzip instantclient-basic-linux.x64-12.1.0.2.0.zip
unzip instantclient-sdk-linux.x64-12.1.0.2.0.zip

# on Entre dans le gourbi pour y ajouter un lien symbolique qui (peut être) sera absent
cd instantclient_12_1/
ln -fs libclntsh.so.12.1 libclntsh.so
```

## PHP OCI8

Passons maintenant à PHP...

```bash
# Installation de PHP5 DEV
apt-get install php5-dev

# Installation de 'Build essential' (pour avoir make)
apt-get install build-essential

# Lib pour oracle
apt-get install libaio1

# Récupération du bouzin
wget http://pecl.php.net/get/oci8-2.0.8.tgz

# extraction du bouzin
tar xzvf oci8-2.0.8.tgz

# compilation du bouzin
cd oci8-2.0.8/
phpize
./configure --with-oci8=shared,instantclient,/opt/instantclient_12_1/
make all install
```

A ce moment, tout doit s'être passé sans message d'erreur, sinon le dépôt d'un cierge au dieu Azimov est une bonne piste de curation...

Pour s'assurer que le bouzin est solidement appareillé :

```bash
php -i | grep oci

>>
PWD => /opt/oci8-2.0.8
_SERVER["PWD"] => /opt/oci8-2.0.8
```

# pour aller plus loin avec debian
```bash
cat > /etc/php5/mods-available/oci8.ini
; configuration for php OCI8 module
; priority=20
extension=oci8.so

On branche le module
```bash
php5enmod oci8

Puis un redémarrage d'apache s'impose
```bash
apache2ctl restart

