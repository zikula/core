Zikula Core - Application Framework
===================================

[![Build Status](https://secure.travis-ci.org/zikula/core.png?branch=master)](http://travis-ci.org/zikula/core)

## Zikula? What's that?

Zikula Core is an open-source PHP web application framework, fully extensible by modules, plugins and themes. Currently under development on the `master` branch is Zikula Core 2.0, which uses components from Symfony2 and will be an evolutionary step forward for anyone wanting to run a high-performance and quality website.

**The current stable version of Zikula is available on the `release-1.3` branch.**  
Zikula Core 1.3 is appropriate for use in production environments, while Zikula Core 2.0 is **not** production-ready (at all).

Please visit [Zikula.org](http://zikula.org) for more information about Zikula.

## PHP Requirements and License

  - Zikula requires PHP 5.3.3 or greater.
  - Zikula is licensed under the terms of the LGPLv3 license (or any later version).

## Manual Installation Required for the `master` Branch

Due to heavy refactoring and development work currently underway on the `master` branch, no installer is available and you will need to follow these steps to install Zikula from the `master` branch:

  - Install the database manually from `docs/installation.sql`. The admin account username and password are `admin` / `zikula1`.
  - Configure the database settings in `app/config/database.yml`
  - In the `core` directory of your checkout:
    - Install the necessary vendors by [installing Composer](http://getcomposer.org/) and running `php composer.phar --dev install`
    - Create the needed asset bundles by running `php app/console assets:install web`

If your PHP binary is not in your command path, specify the full path to it in the above commands in place of just using `php`. Example: `/path/to/php composer.phar --dev install`

Remember to run Composer periodically to keep the dependencies up to date.

## What's Changing in Zikula Core 2.0?

For complete upgrading instructions, please see the [upgrading guide](https://github.com/zikula/core/blob/master/docs/UPGRADING-2.0.md).

Changing the core compoents to Symfony2 will impact all areas of the project and thus there will be some temporary changes to keep things running while development is in progress. Some features may need to be removed temporarily in order to rewrite them.

Besides major changes to the file structure and modules, the following areas are going to be completely changed from the previous model:

  - Translation
  - Templating (replacing smarty with Twig and implementing Assetic)
  - Javascript (removing prototype, changing everything to jQuery)
  - CSS (adopting Twitter Bootstrap)

To monitor developer discussion on these issues, please check out the [zikula-dev mailing list](https://groups.google.com/group/zikula-dev).

## Contributing

Zikula relies on community contributions. If you'd like to contribute, please follow the directions on the [Contributing](https://github.com/zikula/core/wiki/Contributing) page of the wiki. Thank you!