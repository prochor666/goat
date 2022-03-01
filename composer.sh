!/bin/bash

#
# Composer update script
#
# @author Jan Prochazka, prochor666@gmail.com
# @updated 2.11.2021
# @version 1.0
#

#
# VARIABLE CHECK
#
WEBUSER=$(ls -ld update.sh | awk '{print $3}')
WEBGROUP=$(ls -ld update.sh | awk '{print $4}')
COMPOSER="./composer.phar"

export COMPOSER_ALLOW_SUPERUSER=1

clear

#
# UPDATE COMPOSER
#
echo -e "*******************************"
echo -e "| UPDATING COMPOSER"
echo -e "*******************************/"

$COMPOSER self-update

#
# REPOSITORIES
#
echo -e "*******************************"
echo -e "| SYNCING REPOSITORIES"
echo -e "*******************************/"

$COMPOSER update

#
# BUILD
#
echo -e "/*******************************"
echo -e "| POST ACTIONS: BUILD DISTRO"
echo -e "*******************************/"

#
# FIX PERMISSIONS
#
 echo " - permission fix to $WEBUSER:$WEBGROUP"

chown -R $WEBUSER:$WEBGROUP vendor
chown -R $WEBUSER:$WEBGROUP storage
chown -R $WEBUSER:$WEBGROUP public
chown $WEBUSER:$WEBGROUP .htaccess
chown $WEBUSER:$WEBGROUP .version
chown $WEBUSER:$WEBGROUP *.*

#
# ALL DONE
#
echo -e " "
echo -ne "\n > Update finished\n\n"
