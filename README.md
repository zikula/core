[![Build Status](https://travis-ci.org/zikula/core.svg?branch=master)](https://travis-ci.org/zikula/core)
[![StyleCI](https://styleci.io/repos/781544/shield?branch=master)](https://styleci.io/repos/781544)

Zikula Core - Application Framework
===================================

Zikula Core is based on Symfony 2.8.x (will be 3.x) as a foundation and includes other technologies including a dynamic
modular development paradigm and Twig-based theming system which allows for quick expansion of Symfony.

For more information visit http://zikula.org/

<a name="requirements"></a>

Requirements
------------

 - Zikula Core requires PHP >= 5.5.9
 - Zikula requires more memory than typical to install. You should set your memory limit in `php.ini`
   to 128 MB for the installation process.
 - Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
 - Zikula requires `AllowOverride All` and the `mod_rewrite` module (be aware the Apache 2.3.9+ has changed
   the default setting for `AllowOverride` to `None`.
 - Additional (advanced) server considerations can be found on
   [the Symfony site](http://symfony.com/doc/current/reference/requirements.html)
 - Zikula also requires other php extensions and configurations. These are checked during the installation
   process and if there are problems, you will be notified. If you discover errors, check with your hosting
   provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
   possibly reconfiguring the php installation by your provider.

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
