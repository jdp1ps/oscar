# PHP 8.2

La configuration PHP est à adapter selon vos besoins. Prévoir une mémoire minimum à 1024 pour répondre aux besoins de certains scripts (notamment les exports massifs de données). Ainsi que d'ajuster la taille des données téléversées parfois volumineux (10Mo par exemple à Caen)

Pour simplifier, vous pouvez créer un fichier de configuration PHP dédié

```bash
nano /etc/php/8.2/apache2/conf.d/99-oscar.ini
```

```txt
# /etc/php/8.2/apache2/conf.d/99-oscar.ini
# --------------------------
# Configuration PHP : OSCAR
# --------------------------

# Général
date.timezone = Europe/Paris
max_execution_time = 240
memory_limit = 2048M
upload_max_filesize=10M

# Debug
log_errors = On
display_startup_errors = Off
display_errors = Off
error_reporting = E_ERROR
```

Pensez également au fichier pour PHP-CLI `/etc/php/8.2/cli/conf.d/99-oscar.ini` et là aussi, adapter la configuration
à vos besoins