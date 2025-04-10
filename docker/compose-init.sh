#!/bin/bash

source .env

dossiers=(
  "📁 Données POSTGRESQL:$VOLUMES_POSTGRESQL_DATAS"
  "📁 Documents des activités:$VOLUMES_DOCUMENTS_ACTIVITY"
  "📁 Documents public:$VOLUMES_DOCUMENTS_PUBLIC"
  "📁 Documents des demandes d'activités:$VOLUMES_DOCUMENTS_REQUEST"
  "📁 Documents PCRU:$VOLUMES_DOCUMENTS_PCRU"
  "📁 Proxy Cache Doctrine:$VOLUMES_CACHE_DOCTRINE"
  "📁 LOGS (oscar):$VOLUMES_LOG"
  "📁 Dossier temporaire (oscar):$VOLUMES_TMP"
  "📁 Dossier de configuration (oscar):$VOLUMES_CONFIG"
  "📁 Dossier des gabarits:$VOLUMES_TEMPLATES"
)

echo "🔧 Création de l'arborescence..."

for ligne in "${dossiers[@]}"; do
  IFS=":" read -r msg dossier <<< "$ligne"

  if [[ -e "$dossier" ]]; then
    echo " - $msg ($dossier) existe déjà, rien à faire."
  else
    mkdir -p "$dossier"
    echo " - $msg ($dossier) créé ✅"
  fi
done

################################################## Fichiers de template
fichiers=(
  "Corps des mail:$VOLUMES_TEMPLATE_MAIL:./data/templates/mail.phtml"
  "Feuille de temps (personne):$VOLUMES_TIMESHEET_PERSON_MONTH:./data/templates/timesheet_person_month.default.html.php"
  "Feuille de temps (période):$VOLUMES_TIMESHEET_PERIOD:./data/templates/timesheet_period.default.html.php"
  "Feuille de temps (synthèse):$VOLUMES_TIMESHEET_ACTIVITY_SYNTHESIS:./data/templates/timesheet_activity_synthesis.default.html.php"
  "Logo:$VOLUMES_TEMPLATES/logo.png:/data/templates/logo.example.png"

)

echo "🔧 Création des gabarits..."

# Copie des templates
for ligne in "${fichiers[@]}"; do
  IFS=":" read -r msg cible source <<< "$ligne"

  # Nettoyage des blancs éventuels
  cible="$(echo "$cible" | xargs)"
  source="$(echo "$source" | xargs)"

  if [[ -z "$cible" || -z "$source" ]]; then
    echo "⚠️  Ligne invalide, on saute : $ligne"
    continue
  fi

  if [[ -e "$cible" ]]; then
    echo " - 📄 $cible existe déjà, rien à faire."
  else
    if [[ -f "$source" ]]; then
      cp "$source" "$cible"
      echo "- 📄 $cible créé à partir de $source ✅"
    else
      echo "❌ Source manquante pour $cible : $source introuvable"
    fi
  fi
done

################################################## Fichiers de configuration
fichiers=(
  "$VOLUMES_CONFIG/local.php:config/autoload/local.docker.php.dist"
  "$VOLUMES_CONFIG/oscar.yml:config/autoload/oscar.yml.dist"
  "$VOLUMES_CONFIG/unicaen-app.local.php:config/autoload/unicaen-app.local.php.docker-dist"
  "$VOLUMES_CONFIG/unicaen-auth.local.php:config/autoload/unicaen-auth.local.php.docker-dist"
  "$VOLUMES_CONFIG/unicaen-signature.local.php:config/autoload/unicaen-signature.local.php"
)

echo "🔧 Fichiers de configuration..."

for ligne in "${fichiers[@]}"; do
  IFS=":" read -r cible source <<< "$ligne"

  # Nettoyage des blancs éventuels
  cible="$(echo "$cible" | xargs)"
  source="$(echo "$source" | xargs)"

  if [[ -z "$cible" || -z "$source" ]]; then
    echo "⚠️ Ligne invalide, on saute : $ligne"
    continue
  fi

  if [[ -e "$cible" ]]; then
    echo " - 📄 $cible existe déjà, rien à faire."
  else
    if [[ -f "$source" ]]; then
      cp "$source" "$cible"
      echo " - 📄 $cible créé à partir de $source ✅"
    else
      echo "❌ Source manquante pour $cible : $source introuvable"
    fi
  fi
done


echo " - Fichier de configuration éditable (touch $VOLUMES_CONFIG/oscar-editable.yml)"
touch "$VOLUMES_CONFIG/oscar-editable.yml"

chmod -R 777 $VOLUMES_LOG
chmod -R 777 $VOLUMES_TMP
chmod -R 777 "$VOLUMES_CONFIG/oscar-editable.yml"
chmod -R 777 $VOLUMES_DOCUMENTS
chmod -R 777 $VOLUMES_CACHE_DOCTRINE

echo "Terminé"