#!/usr/bin/env bash
set -x

SCRIPT_DIR="$(cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
php -q -f ${SCRIPT_DIR}/hippo_cron.php
