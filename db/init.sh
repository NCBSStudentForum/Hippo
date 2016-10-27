#!/bin/bash - 
#===============================================================================
#
#          FILE: init.sh
# 
#         USAGE: ./init.sh 
# 
#   DESCRIPTION: 
# 
#       OPTIONS: ---
#  REQUIREMENTS: ---
#          BUGS: ---
#         NOTES: ---
#        AUTHOR: Dilawar Singh (), dilawars@ncbs.res.in
#  ORGANIZATION: NCBS Bangalore
#       CREATED: Thursday 30 June 2016 04:23:04  IST
#      REVISION:  ---
#===============================================================================

set -o nounset                              # Treat unset variables as an error
python ./init_db.py 
sudo chown -c -R dilawars:www *.sqlite 
sudo chmod g+w *.sqlite

