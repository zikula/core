# Dependency injection

Just like any Symfony bundle, Zikula themes are allowed to utilize the DIC (Dependency Injection Container). 

Reference: https://symfony.com/doc/current/components/dependency_injection.html

Status: Optional

The DependencyInjection component of Symfony can be quite complex, but the initial implementation of it can be
done quite simply. Here, the extension file must be named `<Vendor><Name>Extension.php` and simply
creates a loader and loads the `services.yaml` file.

----

Filename: `services.yaml`

Status: Optional

Description: The DependencyInjection component of Symfony can be quite complex. Several filetypes can be used
(.yaml, .xml, etc). Please see the symfony documentation for further information.
