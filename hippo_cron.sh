#!/usr/bin/env bash

if [ -d /opt/rh/rh-php56 ]; then
    source /opt/rh/rh-php56/enable
fi


function log_msg
{
    echo $1
    NOW=$(date +"%Y_%m_%d__%H_%M_%S")
    if [[ -f /var/log/hippo.log ]]; then
        echo "$NOW : $1" >> ${LOG_FILE}
    fi
}

SCRIPT_DIR="$(cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
export http_proxy=http://proxy.ncbs.res.in:3128
export https_proxy=http://proxy.ncbs.res.in:3128
LOG_FILE=/var/log/hippo.log

log_msg "Running CRON hippo_cron.php"
FILES=`find ${SCRIPT_DIR}/cron_jobs -name "*.php"`
for cronf in $FILES; do
    log_msg "Executing $cronf"
    php -f $cronf
done
