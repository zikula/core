[![Build Status](https://travis-ci.org/zikula/core.svg?branch=1.4)](https://travis-ci.org/zikula/core)
[![StyleCI](https://styleci.io/repos/781544/shield)](https://styleci.io/repos/781544)

Zikula Core - Application Framework
===================================

  1. [Introduction](#introduction)
  2. [Requirements](#requirements)
  3. [Before installing](#beforeinstalling)
  4. [Installing](#installing)
  5. [Vagrant installation](#vagrant)
  6. [Contributing](#contributing)

<a name="introduction"></a>
Introduction
------------

Zikula Core-1.5 is based on Symfony 2.8.x as a foundation and includes other technologies including a dynamic
modular development paradigm and Twig-based theming system which allows for quick expansion of Symfony.

For more information visit http://zikula.de/


<a name="requirements"></a>
Requirements
------------

 - Zikula Core requires PHP >= 5.5.9
 - Additional server considerations can be found on
   [the Symfony site](http://symfony.com/doc/current/reference/requirements.html)
 - Zikula requires more memory than typical to install. You should set your memory limit in `php.ini`
   to 128 MB for the installation process.
 - Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
 - Zikula requires `AllowOverride All` and the `mod_rewrite` module (be aware the Apache 2.3.9+ has changed
   the default setting for `AllowOverride` to `None`).
 - Zikula also requires other php extensions and configurations. These are checked during the installation
   process and if there are problems, you will be notified. If you discover errors, check with your hosting
   provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
   possibly reconfiguring the php installation by your provider.
 - Zikula 1.x does **not** support PHP 7.


<a name="beforeinstalling"></a>
Before installing
-----------------

Zikula makes use of [Composer](https://getcomposer.org/) to manage and download all dependencies.
If cloning via github, Composer must be run prior to installation. Run:

    `composer self-update`
    `composer install`

If you store Composer in the root of the Zikula Core checkout, please rename it from `composer.phar` to `composer`
to avoid your IDE reading the package contents.


<a name="installing"></a>
Installing
----------
Once all of the pre-install steps are complete, choose an installation method:

  1) CLI install: `cd` to zikula root and run `php app/console zikula:install:start`
  2) HTTP install: Run `http://yoursiteurl/install` and follow any on-screen prompts.


<a name="vagrant"></a>
Vagrant installation
--------------------
You can use vagrant to easily setup a complete Zikula development environment.
All you need to do is install [Vagrant](https://vagrantup.com) and
[VirtualBox](https://www.virtualbox.org/). Then run `vagrant up` inside the
cloned repository and wait for the machine to boot (first time this can take several minutes).
Then head over to `localhost:8080` and install Zikula.
Database user, password and table are all set to `zikula`.
PHPMyAdmin is accessible from `localhost:8081`.


<a name="contributing"></a>
Contributing
------------

Contributions can be made to Zikula in a number of ways

  1. By using our software
  2. Assisting other users at our [Slack channels](https://zikula.slack.com/)
  3. Creating themes for Zikula.
  4. Authoring additional modules for Zikula. Please see our [developer documentation](https://github.com/zikula/core/tree/1.5/src/docs)
  5. Contributing bug fixes and patches to the Core.

Pull requests are welcome, please see https://github.com/zikula/core/wiki/Contributing.

All pull requests must follow [this template](https://github.com/zikula/core/wiki/Contributing#pull-request-template)
