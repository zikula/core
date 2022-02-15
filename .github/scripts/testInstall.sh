#!/bin/bash
set -e

# setup
mysqlCmd="mysql -h 127.0.0.1 --port 3306 -u root -p12345678"
${mysqlCmd} -e "CREATE DATABASE zk_test;"

# action
php bin/console zikula:install:start -n --database_host=127.0.0.1 --database_user=root --database_name=zk_test --database_password=12345678 --password=12345678 --email=admin@example.com --router:request_context:host=localhost --router:request_context:base_url='/' -vvv
php bin/console zikula:install:finish -n -vvv

# cleanup
${mysqlCmd} -e "DROP DATABASE zk_test"
