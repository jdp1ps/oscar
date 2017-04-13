#!/bin/bash
################################################################################
#
# SYNCHRONISATION des DONNÉES depuis la BDD Centaure
#
################################################################################

# Mode
mode=development
log=/var/log/oscar-sync-`date +%Y%m%d`.log
apppath=/var/oscar/oscar/trunk/developpement
 
#mode=production

# Synchronisation des personnes
APPLICATION_ENV=$mode php $apppath/public/index.php centaure sync person >> $log

# Synchronisation des organisations
APPLICATION_ENV=$mode php $apppath/public/index.php centaure sync organization >> $log

# Synchronisation des convention centaure
APPLICATION_ENV=$mode php $apppath/public/index.php centaure sync contract >> $log

# Synchronisation des associations Activités <> Personne
APPLICATION_ENV=$mode php $apppath/public/index.php centaure sync activityPerson >> $log

# Synchronisation des associations Activités <> Organisation
APPLICATION_ENV=$mode php $apppath/public/index.php centaure sync activityOrganization >> $log

# Synchronisation des documents
APPLICATION_ENV=$mode php $apppath/public/index.php centaure sync document >> $log



