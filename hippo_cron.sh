#!/usr/bin/env bash
set -x

SCRIPT_DIR="$(cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
if [ -f /opt/rh/rh-php56/ ]; then
    source /opt/rh/rh-php56/enable 
fi
php -f ${SCRIPT_DIR}/hippo_cron.php
