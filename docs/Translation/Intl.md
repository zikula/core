---
currentMenu: translation
---
# Using the PHP and Symfony's Intl classes

Zikula expects PHP's [intl extension](http://www.php.net/manual/en/book.intl.php) to be installed to facilitate internationalization functions within the code.

If it is installed properly in your PHP build, you can search your `phpinfo` output and you should find something like
`—enable-intl` in the Configure Command and `Internationalization support => enabled` in the output.

If these are NOT found, then the [Intl component](https://symfony.com/doc/current/components/intl.html) from Symfony
will be used instead as a polyfill. These will enable the functions to work without error, but the functions will only work for the `en` locale.

Support in enabling this PHP extension should be obtained through your web provider. This is not something Zikula can help with. 

Symfony's `Intl` class is used as a wrapper to access

- Country Names
- Currencies
- Language and Script Names
- Locales
- Timezones

For further information read [Accessing ICU Data](https://symfony.com/doc/current/components/intl.html#accessing-icu-data).
