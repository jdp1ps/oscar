#!/bin/bash

source .env

echo " - création du dossier des données Postgresql (mkdir -p $VOLUMES_POSTGRESQL_DATAS)"
mkdir -p $VOLUMES_POSTGRESQL_DATAS
echo " - création du dossier des documents des activités (mkdir -p $VOLUMES_DOCUMENTS_ACTIVITY)"
mkdir -p $VOLUMES_DOCUMENTS_ACTIVITY
echo " - création du dossier des documents publiques  (mkdir -p $VOLUMES_DOCUMENTS_PUBLIC)"
mkdir -p $VOLUMES_DOCUMENTS_PUBLIC
echo " - création du dossier des documents des demandes d'activité  (mkdir -p $VOLUMES_DOCUMENTS_REQUEST)"
mkdir -p $VOLUMES_DOCUMENTS_REQUEST
echo " - création du dossier des documents PCRU  (mkdir -p $VOLUMES_DOCUMENTS_PCRU)"
mkdir -p $VOLUMES_DOCUMENTS_PCRU
echo " - création du dossier de cache doctrine (mkdir -p $VOLUMES_CACHE_DOCTRINE)"
mkdir -p $VOLUMES_CACHE_DOCTRINE
echo " - création du dossier des logs (mkdir -p $VOLUMES_LOG)"
mkdir -p $VOLUMES_LOG
echo " - création du dossier des gabarits  (mkdir -p $VOLUMES_TEMPLATES)"
mkdir -p $VOLUMES_TEMPLATES

if [ ! -f "$VOLUMES_TEMPLATE_MAIL" ]; then
  echo "cp ./data/templates/mail.phtml $VOLUMES_TEMPLATE_MAIL"
  cp ./data/templates/mail.phtml $VOLUMES_TEMPLATE_MAIL
fi

if [ ! -f "$VOLUMES_TIMESHEET_PERSON_MONTH" ]; then
  echo "cp ./data/templates/timesheet_person_month.default.html.php $VOLUMES_TIMESHEET_PERSON_MONTH"
  cp ./data/templates/timesheet_person_month.default.html.php $VOLUMES_TIMESHEET_PERSON_MONTH
fi

if [ ! -f "$VOLUMES_TIMESHEET_PERIOD" ]; then
  echo "cp ./data/templates/timesheet_period.default.html.php $VOLUMES_TIMESHEET_PERIOD"
  cp ./data/templates/timesheet_period.default.html.php $VOLUMES_TIMESHEET_PERIOD
fi

if [ ! -f "$VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS" ]; then
  echo "cp ./data/templates/timesheet_activity_synthesis.default.html.php $VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS"
  cp ./data/templates/timesheet_period.default.html.php $VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS
fi

#VOLUMES_TIMESHEET_PERIOD=${VOLUMES_TEMPLATES}/timesheet_person_month.default.html.php
#VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS=${VOLUMES_TEMPLATES}/timesheet_period.default.html.php
#VOLUMES_TIMESHEET_SPENT_ACTIVITY=${VOLUMES_TEMPLATES}/estimated_spent_activity_.default.html.php
echo "Terminé"
## Templates
#VOLUMES_TEMPLATES=${VOLUME_ROOT}/templates
## Corps des mails
#VOLUMES_TEMPLATE_MAIL=${VOLUMES_TEMPLATES}/mail.phtml
## Feuille de temps (Mois/Personne)
#VOLUMES_TIMESHEET_PERSON_MONTH=${VOLUMES_TEMPLATES}/timesheet_person_month.default.html.php'
##
#VOLUMES_TIMESHEET_PERIOD=${VOLUMES_TEMPLATES}/timesheet_person_month.default.html.php'
#VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS=${VOLUMES_TEMPLATES}/timesheet_period.default.html.php'
#VOLUMES_TIMESHEET_SPENT_ACTIVITY=${VOLUMES_TEMPLATES}/estimated_spent_activity_.default.html.php'