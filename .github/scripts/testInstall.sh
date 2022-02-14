#!/bin/bash
set -e

# setup
mysqlCmd="mysql -h 127.0.0.1 --port 3306 -u root -p${MYSQL_ROOT_PASSWORD}"
${mysqlCmd} -e "CREATE DATABASE ${MYSQL_DATABASE};"

# action
php bin/console zikula:install:start -n --database_host=127.0.0.1 --database_user=root --database_name=${MYSQL_DATABASE} --password=12345678 --email=admin@example.com --router:request_context:host=localhost --router:request_context:base_url='/' -vvv
php bin/console zikula:install:finish -n -vvv

# cleanup
${mysqlCmd} -e "DROP DATABASE ${MYSQL_DATABASE}"
