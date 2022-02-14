#!/bin/bash
set -e

# setup
mysql -e 'CREATE DATABASE zk_test;'

# action
php bin/console zikula:install:start -n --database_user=root --database_name=zk_test --password=12345678 --email=admin@example.com --router:request_context:host=localhost --router:request_context:base_url='/' -vvv
php bin/console zikula:install:finish -n -vvv

# cleanup
mysql -e 'DROP DATABASE zk_test'
