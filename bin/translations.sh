#!/usr/bin/env bash
set -e

SCRIPT_PATH=$(dirname "$(realpath $0)")
cd ${SCRIPT_PATH}/..

php -dxdebug.max_nesting_level=500 bin/console translation:extract --force --format=yaml --sort=asc en
php -dxdebug.max_nesting_level=500 bin/console translation:extract --force --format=yaml --sort=asc de
php bin/console zikula:translation:keytovalue
