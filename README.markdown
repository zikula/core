Zikula Core - Application Framework
===================================

[![Build Status](https://secure.travis-ci.org/zikula/core.png?branch=master)](http://travis-ci.org/zikula/core)

Zikula Core is a web based application framework, fully extensible by
Modules, plugins and themes.

For more information visit http://zikula.org/

You must run `php composer.phar install` to get the required dependencies.
Composer can be obtained from http://getcomposer.org/.

## WARNING

This `master` branch is undergoing heavy refactoring. If you want to
contribute to this branch, please note manual installation is required.

Regularly run composer also to make sure dependencies are up to date.

## Manual installation process

Please use the `docs/installation.sql` file to manually install the database.
The administrative login is "Admin" and "zikula1"

You must specify the database credentials in `app/config/database.yml`.

## Change Guide

Please see the [upgrading 1.4 guide](https://github.com/zikula/core/blob/master/docs/UPGRADING-1.4.md)

## License

The Zikula Core package is licensed under the terms of LGPLv3 or any later
version.
