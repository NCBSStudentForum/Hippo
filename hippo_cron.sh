#!/usr/bin/env bash
set -x
set -e
SCRIPT_DIR="$(cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
if [ -d /opt/rh/rh-php56/ ]; then
    source /opt/rh/rh-php56/enable 
fi
if [ -f /var/log/hippo.log ]; then
    echo "Runnung $0: " >> /var/log/hippo.log
fi
php -f ${SCRIPT_DIR}/hippo_cron.php
exit_status=$?
if [ ! $exit_status -eq 0 ]
    echo "Failed to run CRON job. Automatic notification will fail." > /tmp/__alert 
    python ${SCRIPT_DIR}/sendmail.py -s "Failed to run cron job" \
        -i /tmp/__altert -t "hippo@lists.ncbs.res.in"
fi
