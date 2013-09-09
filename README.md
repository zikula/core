Zikula Core - Application Framework
===================================

Zikula Core is a web based application framework, fully extensible by
Modules, plugins and themes.

For more information visit http://zikula.org/


Requirements
------------

Zikula Core is only supported on PHP 5.3.3 and up.

Be warned that PHP versions before 5.3.8 are known to be buggy and might not
work for you:

  - before PHP 5.3.4, if you get "Notice: Trying to get property of
    non-object", you've hit a known PHP bug (see
    https://bugs.php.net/bug.php?id=52083 and
    https://bugs.php.net/bug.php?id=50027);

  - before PHP 5.3.8, if you get an error involving annotations, you've hit
    a known PHP bug (see https://bugs.php.net/bug.php?id=55156).

  - PHP 5.3.16 has a major bug in the Reflection subsystem and is not 
    suitable to run Zikula (https://bugs.php.net/bug.php?id=62715)


Installing
----------

Run `composer self-update && composer update`. Composer can be downloaded 
from http://getcomposer.org/

If you store Composer in the root of the Zikula Core checkout, please
rename it from `composer.phar` to `composer` to avoid your IDE reading
the package contents.


Contributing
------------

Pull requests are welcome, please see https://github.com/zikula/core/wiki/Contributing

All pull requests must follow [this template](https://github.com/zikula/core/wiki/Contributing#pull-request-template)
