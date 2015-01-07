Upgrading Zikula
================

  1. [Introduction](#introduction)
  2. [Requirements](#requirements)
  3. [Test Environment](#testenv)
  4. [Before upgrading](#beforeupgrading)
  5. [Upgrading](#upgrading)
  6. [Notes](#notes)


<a name="introduction"></a>
Introduction
------------

Zikula Core 1.4.0 introduces a lot of forward compatibility for new features that will come in Zikula 2.0.0.

For more information visit http://zikula.org/ and read our
[user manual](https://github.com/zikula/zikula-docs/tree/master/Users%20Manual).


<a name="requirements"></a>
Requirements
------------

Before upgrading Zikula it's important to ensure that the hosting server environment meets the requirements
of the new core release. Zikula Core 1.4.0 has the following requirements

|               | Minimum       | Recommended  |
| ------------- |:-------------:| :-----------:|
| PHP           | 5.3.8         | 5.5          |

 - Please note that PHP version `5.3.16` is known to be buggy and will not work.
 - Zikula requires more memory than typical to install. You should set your memory limit in `php.ini`
   to 128 MB for the installation process.
 - Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
 - Zikula also requires other php extensions and configurations. These are checked during the upgrade
   process and if there are problems, you will be notified. If you discover errors, check with your hosting
   provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
   possibly reconfiguring the php installation by your provider.


<a name="testenv"></a>
Test Environment
----------------

The Zikula team strongly recommend having a duplicate testing environment of the live site in which all
changes including upgrades are tested on before application to the live site.


<a name="beforeupgrading"></a>
Before upgrading
----------------

***Prior to any upgrade ensure that a reliable backup of all files and the database is taken.***

###If you obtained Zikula 1.4.0 from cloning the repo at Github

*note: This method is not recommended for non-developers*

Zikula makes use of [composer](http://getcomposer.org/) to manage and download all dependencies.
Composer must be run prior to installing a site using Zikula. Run `composer self-update` and `composer update`.

If you store Composer in the root of the Zikula Core checkout, please rename it from `composer.phar` to
`composer` to avoid your IDE reading the package contents.

###If you obtained Zikula 1.4.0 from the CI server or zikula.org

All the dependencies and requirements are included in this package, so there is no need to use composer at all.


<a name="upgrading"></a>
Upgrading
---------

The following process should be followed for all upgrades even small point releases (e.g. `1.4.x`).

  - Backup all your files and database. Keep a note of your database settings from `config.php` (or
    `personal_config.php`
  - Before uploading the new files, delete **all files** in your web root (typically `public_html` or `httpdocs`).
  - Upload the new package.
  - Copy your new `config/config.php` to `config/personal_config.php` and update
    `config/personal_config.php` with your database settings.
  - Copy `app/config/parameters.yml` to `app/config/custom_parameters.yml` and update
    values with your database settings. Also set `installed` to `true`.
  - Make `app/cache` and `app/logs` writable. (**Zikula WILL NOT install without this critical step**)
  - Run `http://yoursiteurl/upgrade` and follow any on-screen prompts.
  - Return any 1.3.x-compatible modules, themes and plugins to the appropriate directory and run each
    upgrade independently.


<a name="notes"></a>
Notes
-----

  - As of 1.4.0 `ztemp` is now located in the `app/cache/<kernel-mode>/ztemp` location automatically.
  - the old `upgrade.php` has been replaced by simply `/upgrade`
