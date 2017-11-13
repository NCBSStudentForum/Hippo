#!/bin/bash -
#===============================================================================
#
#          FILE: schedule.sh
#
#         USAGE: ./schedule.sh
#
#   DESCRIPTION: 
#
#       OPTIONS: ---
#  REQUIREMENTS: ---
#          BUGS: ---
#         NOTES: ---
#        AUTHOR: Dilawar Singh (), dilawars@ncbs.res.in
#  ORGANIZATION: NCBS Bangalore
#       CREATED: 02/05/2017 05:27:07 PM
#      REVISION:  ---
#===============================================================================

set -o nounset                              # Treat unset variables as an error

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

#if [ -f /opt/rh/python27/enable ]; then
#    source /opt/rh/python27/enable 
#fi

#python2.7  $DIR/schedule_aws_groupwise.py
python2.7  $DIR/schedule_aws.py
