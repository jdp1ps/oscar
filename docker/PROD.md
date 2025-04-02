```bash
cd ~/

# Emplacement où sont stoqués les documents
mkdir -p documents/activity
mkdir -p documents/request
mkdir -p documents/pcru
mkdir -p documents/public

# Index elasticsearch
mkdir -p elasticsearch_data

# Données Postgresql (si hébergé sur la VM)
mkdir -p postgresql_data

git clone https://git.unicaen.fr/open-source/oscar.git

cd oscar
cp config/autoload/oscar.yml.dist config/autoload/oscar.yml
cp config/autoload/oscar.yml.dist config/autoload/oscar.yml
cp .env.prod.dist .env

nano .env

# Par défaut, si la base est sur la VM, rien à éditer 

#### Proxy de l'hôte > container oscar

# Activation du mod Proxy
sudo  a2enmod proxy proxy_http

# Copy du Vhost
sudo cp docker/host.apache.proxy.conf /etc/apache2/sites-available/000-default.conf
nano /etc/apache2/sites-available/000-default.conf

# Configurer le VHost avec certificat SSL
sudo systemctl restart apache2

```