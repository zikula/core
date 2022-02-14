#!/bin/bash
set -e

FROM_VERSION=$1
DUMP_FILE=$2

# setup
mysql -e 'CREATE DATABASE zk_test;'
mysql zk_test < "tests/test_dbs/${DUMP_FILE}"

# start with fresh copy of .env.local
cp .env .env.local
cp tests/services_custom.yaml config/services_custom.yaml
'sed -i -E "s/core_installed_version:(.*)/core_installed_version: ''${FROM_VERSION}''/" config/services_custom.yaml'

# action
php bin/console zikula:pre-upgrade
php bin/console zikula:upgrade -n --username=admin --password=12345678 --locale=en --router:request_context:host=localhost --router:request_context:scheme=http --router:request_context:base_url='/' -vvv

# cleanup
mysql -e 'DROP DATABASE zk_test'
rm -rf .env.local
rm -rf config/services_custom.yaml
