Using the Php & Symfony's Intl classes
======================================

Zikula expects php's `intl extension` to be installed to facilitate internationalization functions within the code.

http://www.php.net/manual/en/book.intl.php

If it is installed properly in your php build, you can search your `phpinfo` output and you should find something like
`—enable-intl` in the Configure Command and `Internationalization support => enabled` in the output. If these are NOT
found, then polyfill-type solutions are in place from symfony:

http://symfony.com/doc/current/components/intl.html

These will enable the functions to work without error, but the functions will only work for the `en` locale.

Support in enabling this php extension should be obtained through your web provider. This is not something Zikula
can help with. 


Symfony's `Intl` class is used as a wrapper to access
 - Language and Script Names
 - Country Names
 - Locales
 - Currencies

http://symfony.com/doc/current/components/intl.html#accessing-icu-data
