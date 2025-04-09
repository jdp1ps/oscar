#!/bin/bash

source .env

echo " - création du dossier des données Postgresql (mkdir -p $VOLUMES_POSTGRESQL_DATAS)"
mkdir -p $VOLUMES_POSTGRESQL_DATAS
#sudo chown -R 1000:1000 $VOLUMES_POSTGRESQL_DATAS
echo " - création du dossier des documents des activités (mkdir -p $VOLUMES_DOCUMENTS_ACTIVITY)"
mkdir -p $VOLUMES_DOCUMENTS_ACTIVITY
#sudo chown -R 1000:1000 $VOLUMES_DOCUMENTS_ACTIVITY
echo " - création du dossier des documents publiques  (mkdir -p $VOLUMES_DOCUMENTS_PUBLIC)"
mkdir -p $VOLUMES_DOCUMENTS_PUBLIC
#sudo chown -R 1000:1000 $VOLUMES_DOCUMENTS_PUBLIC
echo " - création du dossier des documents des demandes d'activité  (mkdir -p $VOLUMES_DOCUMENTS_REQUEST)"
mkdir -p $VOLUMES_DOCUMENTS_REQUEST
#sudo chown -R 1000:1000 $VOLUMES_DOCUMENTS_REQUEST
echo " - création du dossier des documents PCRU  (mkdir -p $VOLUMES_DOCUMENTS_PCRU)"
mkdir -p $VOLUMES_DOCUMENTS_PCRU
#sudo chown -R 1000:1000 $VOLUMES_DOCUMENTS_PCRU
echo " - création du dossier de cache doctrine (mkdir -p $VOLUMES_CACHE_DOCTRINE)"
mkdir -p $VOLUMES_CACHE_DOCTRINE
#sudo chown -R 1000:1000 $VOLUMES_CACHE_DOCTRINE
echo " - création du dossier des logs (mkdir -p $VOLUMES_LOG)"
mkdir -p $VOLUMES_LOG
echo " - création du dossier temporaire (mkdir -p $VOLUMES_TMP)"
mkdir -p $VOLUMES_TMP
echo " - création du dossier de configuration personnalisé (mkdir -p $VOLUMES_CONFIG)"
mkdir -p $VOLUMES_CONFIG
#sudo chown -R 1000:1000 $VOLUMES_LOG
echo " - création du dossier des gabarits  (mkdir -p $VOLUMES_TEMPLATES)"
mkdir -p $VOLUMES_TEMPLATES
#sudo chown -R 1000:1000 $VOLUMES_TEMPLATES

# Copie des templates

if [ ! -f "$VOLUMES_TEMPLATE_MAIL" ]; then
  echo "cp ./data/templates/mail.phtml $VOLUMES_TEMPLATE_MAIL"
  cp ./data/templates/mail.phtml "$VOLUMES_TEMPLATE_MAIL"
fi

if [ ! -f "$VOLUMES_TIMESHEET_PERSON_MONTH" ]; then
  echo "cp ./data/templates/timesheet_person_month.default.html.php $VOLUMES_TIMESHEET_PERSON_MONTH"
  cp ./data/templates/timesheet_person_month.default.html.php "$VOLUMES_TIMESHEET_PERSON_MONTH"
fi

if [ ! -f "$VOLUMES_TIMESHEET_PERIOD" ]; then
  echo "cp ./data/templates/timesheet_period.default.html.php $VOLUMES_TIMESHEET_PERIOD"
  cp ./data/templates/timesheet_period.default.html.php "$VOLUMES_TIMESHEET_PERIOD"
fi

if [ ! -f "$VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS" ]; then
  echo "cp ./data/templates/timesheet_activity_synthesis.default.html.php $VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS"
  cp ./data/templates/timesheet_period.default.html.php "$VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS"
fi

LOGO="$VOLUMES_TEMPLATES/logo.png"
if [ ! -f "$LOGO" ]; then
  echo "cp ./data/templates/logo.example.png $LOGO"
  cp ./data/templates/logo.example.png "$LOGO"
fi

echo "cp ./data/templates/functions.inc.php $VOLUMES_TEMPLATES/functions.inc.php"
cp ./data/templates/functions.inc.php "$VOLUMES_TEMPLATES/functions.inc.php"

echo "touch $VOLUMES_CONFIG/oscar-editable.yml"
touch "$VOLUMES_CONFIG/oscar-editable.yml"

chmod -R 777 $VOLUMES_LOG
chmod -R 777 $VOLUMES_TMP
chmod -R 777 "$VOLUMES_CONFIG/oscar-editable.yml"
chmod -R 777 $VOLUMES_DOCUMENTS
chmod -R 777 $VOLUMES_CACHE_DOCTRINE

echo "Terminé"