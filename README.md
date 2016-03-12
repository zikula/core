[![Build Status](https://travis-ci.org/zikula/core.svg?branch=1.4)](https://travis-ci.org/zikula/core)
[![StyleCI](https://styleci.io/repos/781544/shield)](https://styleci.io/repos/781544)

Zikula Core - Application Framework
===================================

  1. [Introduction](#introduction)
  2. [Requirements](#requirements)
  3. [Before installing](#beforeinstalling)
  4. [Installing](#installing)
  5. [Contributing](#contributing)

<a name="introduction"></a>
Introduction
------------

Zikula Core is a web based application framework, fully extensible by modules, plugins and themes.

For more information visit http://zikula.org/


<a name="requirements"></a>
Requirements
------------
Before installing Zikula it's important to ensure that the hosting server environment meets the requirements
of the new core release. Zikula Core 1.4.0 has the following requirements:

|               | Minimum       | Recommended  |
| ------------- |:-------------:| :-----------:|
| PHP           | 5.4.1         | 5.5          |


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


<a name="contributing"></a>
Contributing
------------

Contributions can be made to Zikula in a number of ways

  1. By using our software
  2. Assisting other users at the [user community site](http://zikula.org/forum/)
  3. Creating themes for Zikula.
  4. Authoring additional modules for Zikula. Please see our [developer documentation](https://github.com/zikula/core/tree/1.4/src/docs/en/dev)
  5. Contributing bug fixes and patches to the Core. 
  
Pull requests are welcome, please see https://github.com/zikula/core/wiki/Contributing.

All pull requests must follow [this template](https://github.com/zikula/core/wiki/Contributing#pull-request-template)
