Zikula Core - Application Framework
===================================

  1. [Introduction](#introduction)
  2. [Requirements](#requirements)
  3. [Before installing](#beforeinstalling)
  4. [Installing](#installing)
  5. [Contributing](#contributing)

<a name="introduction" />
Introduction
------------

Zikula Core is a web based application framework, fully extensible by
Modules, plugins and themes.

For more information visit http://zikula.org/ and read our [user manual](https://github.com/zikula/zikula-docs/tree/master/Users%20Manual).

<a name="requirements" />
Requirements
------------
Before installing Zikula it's important to ensure that the hosting server environment
meets the requirements of the new core release. Zikula Core 1.4.0 has the following 
requirements

|               | Minimum       | Recommended  |
| ------------- |:-------------:| :-----------:|
| PHP           | 5.3.3         | 5.5          |


Please note that PHP versions before 5.3.8 are known to be buggy and might not
work for you:

  - before PHP 5.3.4, if you get "Notice: Trying to get property of
    non-object", you've hit a known PHP bug (see
    https://bugs.php.net/bug.php?id=52083 and
    https://bugs.php.net/bug.php?id=50027);

  - before PHP 5.3.8, if you get an error involving annotations, you've hit
    a known PHP bug (see https://bugs.php.net/bug.php?id=55156).

  - PHP 5.3.16 has a major bug in the Reflection subsystem and is not 
    suitable to run Zikula (https://bugs.php.net/bug.php?id=62715)

Zikula requires more memory than typical to install. You should set your memory limit in `php.ini` to 128 MB for the
installation process.

<a name="beforeinstalling" />
Before installing
-----------------

Zikula makes use of [composer](http://getcomposer.org/) to manage and download
all dependencies. Composer must be run prior to installing a site using Zikula.
Run `composer self-update && composer update`. 

If you store Composer in the root of the Zikula Core checkout, please
rename it from `composer.phar` to `composer` to avoid your IDE reading
the package contents.

<a name="installing" />
Installing
----------
Once all of the pre-install steps are complete Run `http://yoursiteurl/install.php` 
and follow any on-screen prompts.

<a name="contributing" />
Contributing
------------

Contributions can be made to Zikula in a number of ways

  1. By using our software
  2. Assisting other users at the [user community site](http://zikula.org/index.php/forum/)
  3. Creating themes for Zikula.
  4. Authoring additional modules for Zikula. Please see our [developer documentation](https://github.com/zikula/core/tree/1.4/src/docs/en/dev)
  5. Contributing bug fixes and patches to the Core. 
  
Pull requests are welcome, please see https://github.com/zikula/core/wiki/Contributing.

All pull requests must follow [this template](https://github.com/zikula/core/wiki/Contributing#pull-request-template)

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/zikula/core/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

