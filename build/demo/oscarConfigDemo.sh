#!/bin/bash
cr=`echo $'\n.'`
echo "//////////////////////////////////////////////////////////////////////////////////////////////////////////"
echo "///////////////////// Bonjour Vous venez de lancer le script d'instalation d'Oscar ! /////////////////////"
echo "/////////////////////////////////////////////////////////////////////////////////////////////////////////$cr"

#DEMANDE INFOS PROXY ?
read -p  "Faites un choix pour un proxy, Choisir une des options ci-dessous svp $cr
0 = Pas de proxy $cr
1 = Choix du Proxy de unicaen $cr
2 = Saisir votre propre proxy personnalisé (exemple-> http://monproxyperso.xxxx.extension:port) $cr
" -n 1 proxy

if [ -z $proxy ]
then
    echo "$cr Paramètre obligatoire proxy :p !$cr"
    exit
fi

if [ $proxy == "0" ]
then
#PAS DE PROXY
    valeurProxy='noProxy';
elif [ $proxy == "1" ]
then
#PROXY UNICAEN
    valeurProxy='http://proxy.unicaen.fr:3128';
elif [ $proxy == "2" ]
then
#SAISIE MANUELLE
    read -p  "$cr Saississez les infos proxy svp $cr ->" proxyManuel
    valeurProxy=$proxyManuel;
else
    echo "Valeur saisie non conforme ! $cr"
    exit
fi

#DEMANDE INFOS VOLUME POSTGREE
read -p  "$cr Saississez manuellement le path pour monter sur la machine hôte les volumes pour la bdd Postgree SVP $cr
Le chemin utilisé partira de là où vous vous trouvez (pwd) en ajoutant le chemin que vous allez saisir, exemple possible : etablissement/demooscar/datasPostgree  $cr
- Si vous saississez default alors le path par défaut sera le suivant : (path actuel = pwd + )/var/dataPostgree $cr
- Si le répertoire n'existe pas dans votre hôte, l'installation risque d'échouer, oscar va essayer de le créer quand même $cr
-> (saisissez default ou saisissez un chemin non absolu, ne mettez pas de / ni au début ni à la fin :p SVP)$cr-> " pathPostgree

pwdInstall=$(pwd)
if [ -z $pathPostgree ]
then
    #PAS DE SAISIE
    echo "$cr Paramètre obligatoire de chemin, il est impossible d'installer sinon postgree ? :p !$cr"
    exit
elif [ $pathPostgree == "default" ]
then
    #REPERTOIRE DEFAULT
    valeurPath="$pwdInstall/var/dataPostgree";
else
    #VALEUR SAISIE USER
    valeurPath="$pwdInstall/$pathPostgree"
fi
echo $valeurPath

#RECAP CONFIRMATION INFOS
infos="$cr Confirmez votre choix pour configurer proxy et path postgree $cr
Les modifications du fichier de lancement docker (docker-compose.yml vont êtres modifiées en fonction, $cr
La procédure d'instalation va être faite avec ces paramètres ! $cr
Donc si ces informations sont mauvaises la procédure d'instalation peut ne pas fonctionner correctement !.$cr"
details="$cr
- Valeur de votre proxy -> $valeurProxy $cr
- Valeur du path postgree -> $valeurPath $cr"
echo $cr
echo "$infos $details $cr"

#VALIDATION FINALE SINON ABANDON
read -p  "Validez ces informations avec oui ou non : saisissez o pour oui ou n pour non $cr
(si non annulation de la procédure) : $cr ->" -n 1 valid

if [ -z $valid ]
then
    echo "Validation obligatoire ! pas de choix fait Procédure annulée :( $cr"
    exit
fi

#SI NON VALID, ALORS ANNULATION, SINON ON CONTINU SED
if [ $valid == "n" ]
    then
    #ERROR DATAS !
    echo "Les données ne correspondents pas aux attentes et/ou vos attentes pour l'installation d'OSCAR, procédure annulée :( $cr"
    exit
else
  echo "$cr/////////////////////////////////////////////////////////////////////////////////////////////////////////////////$cr"
  echo "Copie à partir du docker-compose.dist.yml pour générer le docker-compose.yml pour déploiement $cr"
  cp -v docker-compose.dist.yml docker-compose.yml
  echo "Changements éffectués pour le docker-compose de déploiement :) $cr"
  sed -i "s#proxy_environnement: .*#proxy_environnement: \"$valeurProxy\"#g" docker-compose.yml
  sed -i "s#pathdatas: .*#pathdatas: \"$valeurPath\"#g" docker-compose.yml
  sed -i "s#/var/datasPostgree#$valeurPath#g" docker-compose.yml

  echo "Résultats des modifications effectués dans le docker-compose :) $cr"
  cat docker-compose.yml | grep "$valeurProxy" && cat docker-compose.yml | grep "$valeurPath"

  echo "$cr Check répertoire hôte $cr"
  if [ -d $valeurPath ]; then
    if [ -L $valeurPath ]; then
      echo "$cr ////////// Erreur votre répertoire est un lien symbolique ! ///////////"
      exit
    else
      echo "$cr Répertoire OK traitement en cours ! $cr"
    fi
  else
      echo "$cr Répertoire non présent ! Essai de création de celui-ci ! $cr
      Si pas de messages d'erreurs, répertoire créer, sinon script stoppé $cr"
      mkdir -p $valeurPath
  fi

  #LANCEMENT PROCEDURE AUTO OU STOP
  read -p  "Voulez-vous lancer la procédure d'instalation ? $cr
  o = Oui $cr
  n = Non (arrêt de la procédure mais le fichier docker-compose est prêt à être exécuté manuellement !)$cr
  Pour rappel : docker-compose up --build -d $cr
  -> " -n 1 startCompil

  if [ -z $startCompil ]
  then
      echo "$cr Paramètre obligatoire :p ! (exit, cependant fichier docker-compose généré prêt à être exploité)$cr"
      exit
  fi

  if [ $startCompil == "n" ]
  then
  #ARRET PROCEDURE
    echo "$cr"
    cat docker-compose.yml
    echo "$cr ////////////// Fichier docker-compose.yml prêt, affichage de son contenu ci-dessus, arrêt de la procédure d'installation.  ////////////// $cr"
    exit
  elif [ $startCompil == "o" ]
  then
  #LANCEMENT PROCEDURE
  #SCRIPTS LANCEMENT docker-compose
  echo "$cr /////// docker-compose compilation lancée ! /////// $cr"
  docker-compose up --build -d
  else
      echo "Valeur de saisie non conforme (o pour oui attendu, ou n pour non attendu, procédure stoppé !)! $cr"
      exit
  fi
fi #END IF VALID
