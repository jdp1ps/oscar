#!/bin/sh

# -------------------------------------------------------------------------------------------------------------------- #
#
# -------------------------------------------------------------------------------------------------------------------- #

# Nom du projet (dossier)
PRJ=$1

# Dossiers
DIR_CURRENT=$(pwd)
DIR_OSCAR=$(realpath $DIR_CURRENT/../)
DIR_SKELETON="$DIR_CURRENT/skeleton"

# Emplacements créés
DIR_DEST="$DIR_CURRENT/local/$PRJ"
DIR_POSTGRESQL_DATAS="$DIR_DEST/databases"

OPT_PROXY="$2"
OPT_PORT_OSCAR=8181
OPT_PORT_ADMINER=8182

# Nom des containers
CONTAINER_OSCAR="oscar_$PRJ"
CONTAINER_POSTGRESQL="oscar_"$PRJ"_postgresql"
CONTAINER_ELASTICSEARCH="oscar_"$PRJ"_elasticsearch"
CONTAINER_GEARMAN="oscar_"$PRJ"_gearman"
CONTAINER_NETWORK="oscar_"$PRJ"_network"
CONTAINER_ADMINER="oscar_"$PRJ"_adminer"

# -------------------------------------------------------------------------------------------------------------------- #
# STEP
echo "### PARAMETRES INSTALLATION"
echo "Destination : $DIR_DEST"
echo "Oscar path : $DIR_OSCAR"
echo "Proxy : $OPT_PROXY"
echo "### "

echo "Suppression de l'ancien dossier"
cmd="rm -Rf $DIR_DEST"
echo "> $cmd"
$($cmd)

echo "Copie du squelette"
cmd="cp -R skeleton $DIR_DEST"
echo "> $cmd"
$($cmd)
echo "Effacement de la configuration d'origine"
cmd="rm -Rf $DIR_DEST/oscar/oscar/config/autoload"
echo "> $cmd"
$($cmd)
echo "Insertion de la configuration établissement"
cmd="cp -R skeleton-etab/* $DIR_DEST"
echo "> $cmd"
$($cmd)

echo "Remplacement des paramètres"
for i in $(find $DIR_DEST -type f); do
  file=$(basename "$i")
  echo "Traitement pour $i ($file)"
  sed -i s%£CONTAINEROSCAR%$PRJ%g "$i"
  sed -i s%£PROXY%$OPT_PROXY%g "$i"
  sed -i s%£DIR_INSTALL%$DIR_DEST%g "$i"
  sed -i s%£PORT_ADMINER%$OPT_PORT_ADMINER%g "$i"
  sed -i s%£PORT_OSCAR%$OPT_PORT_OSCAR%g "$i"
  sed -i s%£DIR_OSCAR%$DIR_OSCAR%g "$i"
  sed -i s%£DIR_POSTGRESQL_DATAS%$DIR_POSTGRESQL_DATAS%g "$i"

  sed -i s%£CONTAINER_OSCAR%$CONTAINER_OSCAR%g "$i"
  sed -i s%£CONTAINER_POSTGRESQL%$CONTAINER_POSTGRESQL%g "$i"
  sed -i s%£CONTAINER_ELASTICSEARCH%$CONTAINER_ELASTICSEARCH%g "$i"
  sed -i s%£CONTAINER_GEARMAN%$CONTAINER_GEARMAN%g "$i"
  sed -i s%£CONTAINER_NETWORK%$CONTAINER_NETWORK%g "$i"
  sed -i s%£CONTAINER_ADMINER%$CONTAINER_ADMINER%g "$i"
done

echo "# For Build / Run"
echo "cd $DIR_DEST"
echo "docker-compose build"
echo "docker-compose up"


