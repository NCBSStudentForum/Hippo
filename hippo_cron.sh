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
