#!/bin/bash -
#===============================================================================
#
#          FILE: bootstrap.sh
#
#         USAGE: ./bootstrap.sh
#
#   DESCRIPTION: 
#
#       OPTIONS: ---
#  REQUIREMENTS: ---
#          BUGS: ---
#         NOTES: ---
#        AUTHOR: Dilawar Singh (), dilawars@ncbs.res.in
#  ORGANIZATION: NCBS Bangalore
#       CREATED: Sunday 08 April 2018 11:43:08  IST
#      REVISION:  ---
#===============================================================================

set -o nounset                                  # Treat unset variables as an error
sudo apt install python3-venv || echo "Install python3-venv" 
