Zikula Core - Application Framework
===================================

[![Build Status](https://secure.travis-ci.org/zikula/core.png?branch=master)](http://travis-ci.org/zikula/core)

Zikula Core is a web based application framework, fully extensible by
Modules, plugins and themes.

For more information visit http://zikula.org/

You must run `php composer.phar --dev install` to get the required dependencies.
Composer can be obtained from http://getcomposer.org/.

.. note::

If your PHP binary is not in your command path, please specify the full path to PHP
as in `/path/to/php composer.phar --dev install` or as appropriate for your
environment.

## WARNING

This `master` branch is undergoing heavy refactoring. If you want to
contribute to this branch, please note manual installation is required.

**If you are looking for the stable version, please see the `release-1.3` branch.**

Regularly run composer also to make sure dependencies are up to date.

## PHP Requirements

Zikula requires PHP 5.3.3 and above.

## Manual installation process

  - Install the database manually from `docs/installation.sql`
    The username and password are `admin` / `zikula1`
  - Configuring the database settings in `app/config/database.yml`
  - Install vendors with `php composer.phar install`
  - Copy assets to web directory `app/console assets:install web`

## Change Guide

Please see the [upgrading 1.4 guide](https://github.com/zikula/core/blob/master/docs/UPGRADING-1.4.md)

## Features

The process of switching to Symfony2 requires some temporary refactoring of legacy code
in order to keep things running. In some cases it means removing features because
they will need to be rewritten or handles differently.

Things which will completely change:

  - Translation
  - Templating (to Twig and assetic)
  - Javascript framework, based on jQuery 1.7+ with no prototype support
  - CSS (to Twitter Bootstrap 2.0.2)

## License

The Zikula Core package is licensed under the terms of LGPLv3 (or any later version).

## Contributing

Zikula is an open source, community-driven project. If you'd like to contribute,
please fork the project and submit a "pull request". More information ca be found in the
[wiki](https://github.com/zikula/core/wiki).
