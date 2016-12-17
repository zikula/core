[![Build Status](https://travis-ci.org/zikula/core.svg?branch=master)](https://travis-ci.org/zikula/core)
[![StyleCI](https://styleci.io/repos/781544/shield?branch=master)](https://styleci.io/repos/781544)
[![Code Climate](https://codeclimate.com/github/zikula/core/badges/gpa.svg)](https://codeclimate.com/github/AzureAD/omniauth-azure-activedirectory/badges/gpa.svg)
[![Code Climate Coverage](https://codeclimate.com/github/zikula/core/badges/coverage.svg)](https://codeclimate.com/github/zikula/core/badges/coverage.svg)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cc7028a5-80d5-4835-a4a4-0a179a690487/mini.png)](https://insight.sensiolabs.com/projects/cc7028a5-80d5-4835-a4a4-0a179a690487/mini.png)
[![Scrutinizer](https://scrutinizer-ci.com/g/zikula/core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zikula/core/badges/quality-score.png?b=master)
[![Scrutinizer Coverage](https://scrutinizer-ci.com/g/zikula/core/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/zikula/core/badges/coverage.png?b=master)

Zikula Core - Application Framework
===================================

Zikula Core is an Application Framework which extends Symfony 2.8.x (will be 3.x) and includes technologies
fostering a dynamic modular development paradigm and Twig-based theming system which allows for rapid
website and application development. See the [Features](https://github.com/zikula/core/blob/master/src/docs/FEATURES.md)
document for more information.

Zikula also features an [MDSD](https://en.wikipedia.org/wiki/Model-driven_engineering) tool for rapid prototyping
and module development called [ModuleStudio](http://modulestudio.de/en/) or MOST.

Zikula can quickly become a Content Management System utilizing community-driven modules.

For more information visit http://zikula.org/

Requirements
------------

 - Zikula Core requires PHP >= 5.5.9
 - Additional server considerations can be found on
   [the Symfony site](http://symfony.com/doc/current/reference/requirements.html)
 - Zikula requires more memory than typical to install. You should set your memory limit in `php.ini`
   to 128 MB for the installation process.
 - Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
 - Zikula requires `AllowOverride All` and the `mod_rewrite` module (be aware the Apache 2.3.9+ has changed
   the default setting for `AllowOverride` to `None`.
 - Zikula also requires other php extensions and configurations. These are checked during the installation
   process and if there are problems, you will be notified. If you discover errors, check with your hosting
   provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
   possibly reconfiguring the php installation by your provider.


Documentation
-------------

Please see our [developer documentation](https://github.com/zikula/core/tree/master/src/docs/Core-2.0)


Contributing
------------

Contributions can be made to Zikula in a number of ways

  1. By using our software!
  2. Assisting other users at the [user community site](http://zikula.org/forum/)
  3. Creating themes for Zikula.
  4. Authoring additional modules for Zikula.
  5. Contributing bug fixes and patches to the Core.

Pull requests are welcome, please see https://github.com/zikula/core/wiki/Contributing.
