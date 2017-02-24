#!/usr/bin/env bash

SCRIPT_DIR="$(cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
php  -q ${SCRIPT_DIR}/hippo_cron.php
