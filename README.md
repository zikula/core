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

For more information visit http://zikula.org/


<a name="requirements"></a>
Requirements
------------
Before installing Zikula it's important to ensure that the hosting server environment meets the requirements
of the new core release. Zikula Core 1.x has the following requirements:

|               | Minimum       |
| ------------- |:-------------:|
| PHP           | 5.5.9         |


Please note:

 - Zikula requires `AllowOverride All` and the `mod_rewrite` module (be aware the Apache 2.3.9+ has changed
   the default setting for `AllowOverride` to `None`.
 - Additional (advanced) server considerations can be found on
   [the Symfony site](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)
 - Zikula requires more memory than typical to install. You should set your memory limit in `php.ini` to 128 MB for the
   installation process.


<a name="beforeinstalling"></a>
Before installing
-----------------

Zikula makes use of [composer](http://getcomposer.org/) to manage and download all dependencies.
If cloning via github, composer must be run prior to installation. Run:

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
  2. Assisting other users at the [user community site](http://zikula.org/forum/)
  3. Creating themes for Zikula.
  4. Authoring additional modules for Zikula. Please see our [developer documentation](https://github.com/zikula/core/tree/1.4/src/docs/Core-2.0)
  5. Contributing bug fixes and patches to the Core.

Pull requests are welcome, please see https://github.com/zikula/core/wiki/Contributing.

All pull requests must follow [this template](https://github.com/zikula/core/wiki/Contributing#pull-request-template)
