#!/usr/bin/env bash
set -x
set -e
if [ -d /opt/rh/rh-php56/ ]; then
    source /opt/rh/rh-php56/enable 
fi

export http_proxy=http://proxy.ncbs.res.in:3128 
export https_proxy=http://proxy.ncbs.res.in:3128 
LOG_FILE=/var/log/hippo.log

function log_msg
{
    NOW=$(date +"%Y_%m_%d__%H_%M_%S")
    if [[ -f /var/log/hippo.log ]]; then
        echo "$NOW : $1" >> ${LOG_FILE}
    fi
}

SCRIPT_DIR="$(cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

php -f ${SCRIPT_DIR}/hippo_cron.php
# Now update the calendar. every six hours.
HOUR=`date +%H`
n=$((HOUR%6))
if [[ $n -eq 0 ]]; then
    log_msg "MOD 6 is zero."
    MINS=`date +%M`
    if [[ $MINS -gt -5 && $MINS -lt 10 ]]; then
        log_msg "Updating google calendar."
        php -f ${SCRIPT_DIR}/synchronize_calendar.php
    fi
fi
