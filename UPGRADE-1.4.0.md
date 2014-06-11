Upgrading Zikula
================

  1. [Introduction](#introduction)
  2. [Requirements](#requirements)
  3. [Test Environment](#testenv)
  4. [Before upgrading](#beforeupgrading)
  5. [Upgrading](#upgrading)
  6. [Notes](#notes)

<a name="introduction" />
Introduction
------------
Zikula Core 1.4.0 introduces a lot of forward compatibility for new features
that will come in Zikula 1.5.0.

For more information visit http://zikula.org/ and read our [user manual](https://github.com/zikula/zikula-docs/tree/master/Users%20Manual).

<a name="requirements" />
Requirements
------------
Before upgrading Zikula it's important to ensure that the hosting server environment
meets the requirements of the new core release. Zikula Core 1.4.0 has the following 
requirements

|               | Minimum       | Recommended  |
| ------------- |:-------------:| :-----------:|
| PHP           | 5.3.3         | 5.5          |

<a name="testenv" />
Test Environment
----------------
The Zikula team strongly recommend having a duplicate testing environment of the live 
site in which all changes including upgrades are tested on before application to the 
live site.

<a name="beforeupgrading" />
Before upgrading
----------------
Prior to any upgrade ensure that a reliable backup of all files and the database
is taken.

Zikula makes use of [composer](http://getcomposer.org/) to manage and download
all dependencies. Composer must be run prior to installing a site using Zikula.
Run `composer self-update && composer update`. 

If you store Composer in the root of the Zikula Core checkout, please
rename it from `composer.phar` to `composer` to avoid your IDE reading
the package contents.

<a name="upgrading" />
Upgrading
---------
The following process should be followed for all upgrades even smaller point releases.

  - Before uploading the new files please delete the `plugins/`, `lib/`, `system/`,
    `themes/` and `ztemp/` folders entirely (replace any custom themes afterwards).
  - Delete your `config/config.php` and `config/personal_config.php` keeping a note
    of your database settings.
  - Upload new files.
  - Copy your new`config/config.php` and `config/personal_config.php` and update
    `config/personal_config.php` with your database settings.
  - Copy `app/config/parameters.yml` to `app/config/custom_parameters.yml` and update
    values with your database settings. Also set `installed` to `true`.
  - Make `app/cache` and `app/logs` writable.
  - Run `http://yoursiteurl/upgrade.php` and follow any on-screen prompts.
  - Go to the ExtensionsModule and install the ZikulaRoutesModule.

<a name="notes" />
Notes
-----
  - As of 1.4.0 `ztemp` is now located in the `app/cache/<kernel-mode>/ztemp` location automatically.
