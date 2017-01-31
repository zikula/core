Upgrading Zikula
================

  1. [Introduction](#introduction)
  2. [Requirements](#requirements)
  3. [Test Environment](#testenv)
  4. [Before upgrading](#beforeupgrading)
  5. [Upgrading](#upgrading)


<a name="introduction"></a>

Introduction
------------

Zikula 1.4 can only upgrade from Zikula 1.3.6 or higher. Please upgrade your core installation to this version
before proceeding with this upgrade process.

Zikula Core 1.4 introduces a lot of forward compatibility for new features that will come in Zikula 2.0.0.

For more information visit http://zikula.org/ and read our
[documentation](https://github.com/zikula/core/tree/1.4/src/docs).


<a name="requirements"></a>

Server/Environment Requirements
-------------------------------

Before upgrading Zikula it's important to ensure that the hosting server environment meets the requirements
of the new core release. Zikula Core 1.4.x has the following requirements

|               | Minimum       | Recommended  |
| ------------- |:-------------:| :-----------:|
| PHP           | 5.4.1         | >=5.5 <7     |

 - Zikula requires more memory than typical to install. You should set your memory limit in `php.ini`
   to 128 MB for the installation process.
 - Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
 - Zikula requires `AllowOverride All` and the `mod_rewrite` module (be aware the Apache 2.3.9+ has changed
   the default setting for `AllowOverride` to `None`.
 - Additional (advanced) server considerations can be found on
   [the Symfony site](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)
 - Zikula also requires other php extensions and configurations. These are checked during the upgrade
   process and if there are problems, you will be notified. If you discover errors, check with your hosting
   provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
   possibly reconfiguring the php installation by your provider.


<a name="testenv"></a>

Test Environment
----------------

The Zikula team strongly recommends having a duplicate testing environment of the live site in which all
changes including upgrades are tested on before application to the live site.


<a name="beforeupgrading"></a>

Before upgrading
----------------

***Prior to any upgrade ensure that you have created a reliable backup of all files and the database.***

#### FOR DEVELOPERS ONLY: Clone Zikula/Core from the repo at Github. Use the 1.4 branch.

Zikula makes use of [composer](http://getcomposer.org/) to manage and download all dependencies.
Composer must be run prior to installing or upgrading a site using Zikula. Run `composer self-update` and `composer update`.

If you store Composer in the root of the Zikula Core checkout, please rename it from `composer.phar` to
`composer` to avoid your IDE reading the package contents.

#### FOR NORMAL USERS: Download the current release from [http://www.zikula.org/](http://www.zikula.org/)

All the dependencies and requirements are included in this package.


<a name="upgrading"></a>

Upgrading
---------

The following process should be followed for all upgrades even small point releases (e.g. `1.4.x`).

  - Backup all your files and database. Keep a note of your database settings from `config.php` or
    `personal_config.php`.
  - Make a note of your 'startpage' settings as they must be cleared in the upgrade process.
  - Before uploading the new files, delete **all files** in your web root (typically `public_html` or `httpdocs`).
  - Upload the new package and unpack the archive.
    - **Please read** the [INSTALL docs](INSTALL-1.4.md#upload) for detailed information on proper uploading.
    - Note 1: One common issue when installing is that the `app/cache` and `app/logs` directories must be writable both by the 
      web server and the command line user. See Symfony's [Setting up or Fixing File Permissions](http://symfony.com/doc/2.8/setup/file_permissions.html) 
      to see potential solutions to this problem when installing from the CLI.
    - Note 2: If you have `mod_suexec` installed for Apache the CLI will run into permission problems. (If you are not sure 
      check your phpinfo.) `mod_suexec` often is used in shared hosting environments. In this case, the CLI installer is not 
      recommended, please use the Web Installer.

#### If upgrading from Core-1.3.x:

  - Make a copy of `config/config.php` and rename it to `config/personal_config.php` -- update the database settings 
    values of this file with yours taken from your old 'config.php' file. * NOTE: you should now have both `config.php`
    AND `personal_config.php` in your `config/` folder. Make sure to set permissions on `config.php` to 400.
  - Make a copy of `app/config/parameters.yml` and rename it to `app/config/custom_parameters.yml` -- update the values
    of this file with your **database settings**. Set `installed: true`. All database values of "~" should be replaced
    with their proper values -- In most cases, 'database_port', 'database_path', and 'database_socket' should be left
    as '~'.
    * NOTE: you should now have both `parameters.yml` AND `custom_parameters.yml` in your `app/config/` folder.
    The upgrade will not work unless both of these files are present.
  - Additional notes
    - As of 1.4.0 `ztemp` is now located in the `app/cache/<kernel-mode>/ztemp` location automatically.
    - the old `upgrade.php` has been replaced by simply `/upgrade` but you should be automatically redirected to this
      url when visiting your main page.

#### If upgrading from Core-1.4.x:

  - Copy your previous installation's `config/personal_config.php` and `app/config/custom_parameters.yml` to their same
    respective locations in your new installation. **There is no need to update any values within these files.**

#### Continue:

  - Copy your backup `/userdata` and your **theme** to your new installation. The folders of your theme should be
    in the exact same place as your backup.
  - Copy your additional modules to the appropriate directory. **DO NOT include the old Profile and Legal module**
    when copying them into your new installation, as new versions of these are provided (and their location may differ).
  - **Upgrade: (do one or the other)**
    - Via Web: launch `http://yoursiteurl/` (you will be redirected to `/upgrade`) and follow any on-screen prompts.
    - Via CLI:
      - Access your main zikula directory (`/src` if a Github clone) and run this command:

         ```Shell
         $ php app/console zikula:upgrade
         ```

  - Follow the prompts and complete that step. When you are finished, Open your browser and login!

