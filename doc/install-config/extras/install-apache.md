# Configurer le serveur web (Apache)

## Modules Apache

```bash
a2enmod rewrite
a2enmod ssl
service apache2 reload
```

## Virtualhost 

Éditer le fichier de configuration apache2 :

```bash
vi /etc/apache2/sites-available/000-default.conf
```

```apacheconf
<VirtualHost *:80>
   ServerAdmin stephane.bouvry@unicaen.fr
   ServerName oscar-pp.unicaen.fr
   ServerAdmin webmaster@localhost

   # redirection vers 443
   RewriteEngine on
   RewriteCond %{SERVER_PORT} !^443$
   RewriteRule ^/(.*) https://%{SERVER_NAME}/$1 [L,R]
</VirtualHost>

<VirtualHost *:443>
   ServerAdmin stephane.bouvry@unicaen.fr
   ServerName oscar-pp.unicaen.fr
   DocumentRoot /var/OscarApp/oscar/public

   SSLEngine On
   SSLCertificateFile /etc/ssl/certs/oscar-pp_unicaen_fr.crt
   SSLCertificateKeyFile /etc/ssl/private/oscar-pp_unicaen_fr.key
   SSLCACertificateFile /etc/ssl/certs/DigiCertCA.crt

   # Visible dans l'application
   SetEnv APPLICATION_ENV beta

   <Directory /var/OscarApp/oscar/public>
      DirectoryIndex index.php
      AllowOverride All
      Order allow,deny
      Allow from all
      Require all granted
   </Directory>

   LogLevel debug
   ErrorLog ${APACHE_LOG_DIR}/oscar-error.log
   CustomLog ${APACHE_LOG_DIR}/oscar-access.log combined
</VirtualHost>
```

## Droits d'écriture

S'assurer que les dossiers sont accessibles au serveur :

 - `data/*`
 - Le dossier de stockage des documents (`data/documents/*`)
 - Les fichiers de log (`logs/*`)
