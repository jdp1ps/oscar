#/bin/bash

########################################################################
# Synchronise le répertoire courant avec celui spécifié en argument,
# à l'aide de l'outil 'rsync'.
# Une simple simulation peut être faite.
# 
# NB: Des fichiers peuvent être exclus de la synchro.
########################################################################

EXCLUSIONS=""
EXCLUSIONS="$EXCLUSIONS --exclude /public/.htaccess"
EXCLUSIONS="$EXCLUSIONS --exclude /docs/*"
EXCLUSIONS="$EXCLUSIONS --exclude /logs/*"
EXCLUSIONS="$EXCLUSIONS --exclude /tests"
EXCLUSIONS="$EXCLUSIONS --exclude *~"
EXCLUSIONS="$EXCLUSIONS --exclude .svn/"
EXCLUSIONS="$EXCLUSIONS --exclude .git/"
EXCLUSIONS="$EXCLUSIONS --exclude /composer.lock"
EXCLUSIONS="$EXCLUSIONS --exclude /*.log"
#EXCLUSIONS="$EXCLUSIONS --exclude /vendor/*"
EXCLUSIONS="$EXCLUSIONS --exclude /config/autoload/*local.php"
EXCLUSIONS="$EXCLUSIONS --exclude /temp/*"

LOG_FILE='./deploy.log'

# Execute getopt
ARGS=`getopt -o "t:s:" -l "target:,simulation:" -n "getopt.sh" -- "$@"`
 
# Bad arguments
if [ $? -ne 0 ];
then
  exit 1
fi

# A little magic
eval set -- "$ARGS"
 
# Now go through all the options
while true;
do
  case "$1" in
    -t|--target)
    #--------------
      if [ -n "$2" ];
      then
        echo "Target : $2"
        target="$2"
      fi
      shift 2;;
 
    -s|--simulation)
    #---------------
      if [ -n "$2" ];
      then
        echo "Simulation : $2"
        simul=$2
      fi
      shift 2;;
 
    --)
      shift
      break;;
  esac
done

if [ ! $target ]; then
    echo "Target (ex: gauthierb@dev.unicaen.fr:/var/www/closer) ?"
    read target
fi
if [ ! $simul ]; then
    echo "Simulation (y/n) ? "
    read simul
fi

sourceDir="."
doit="y"

echo
echo "------------------------------------------------------------------------------------------------------"
echo "S Y N C H R O N I S A T I O N"
echo "------------------------------------------------------------------------------------------------------"

targetDir=$target

echo "Source directory : $sourceDir"
echo "Target directory : $target"

# simulation
if [ $simul = "y" ]; then
	echo
	echo "SIMULATION :"
	echo "------------"
	rsync -avzn --perms --delete $EXCLUSIONS -e ssh $sourceDir $targetDir
        exit
fi

# logging
ts=`date +%d/%m/%Y-%H:%M:%S`
echo "------------------------------------------------------------------------------------------------------" >> $LOG_FILE
echo "$ts : $0 $*" >> $LOG_FILE

# synchronisation
if [ $doit = "y" ]; then
	echo
	echo "SYNCHRONISATION :"
	echo "-----------------"
	rsync -avz --perms --delete $EXCLUSIONS -e ssh --log-file=$LOG_FILE $sourceDir $targetDir
fi

echo "------------------------------------------------------------------------------------------------------"
echo 
