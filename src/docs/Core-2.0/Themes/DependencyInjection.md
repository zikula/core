DependencyInjection
===================

Just like any Symfony bundle, Zikula themes are allowed to utilize the DIC (Dependency Injection Container). 

reference: http://symfony.com/doc/current/components/dependency_injection/index.html

Status: Optional

The DependencyInjection component of Symfony can be quite complex, but the initial implementation of it can be
done quite simply. Here, the extension file must be named `<Vendor><Name>Extension.php` and simply
creates a loader and loads the `services.yml` file.

----

Filename: `services.yml`

Status: Optional

Description: The DependencyInjection component of Symfony can be quite complex. Several filetypes can be used
(.yml, .xml, etc). Please see the symfony documentation for further information.
http://symfony.com/doc/current/components/dependency_injection/index.html
