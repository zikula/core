---
currentMenu: themes
---
# Theme file structure

[Reference in Symfony docs](https://symfony.com/doc/current/bundles/best_practices.html).

```
SpecTheme/
    Controller/
        FooController.php
    DependencyInjection/
        SpecThemeExtension.php (required if services are used)
    Listener/
        FooListener.php
    Resources/
        config/
            theme.yaml (required)
            services.yaml (required if services are used)
        docs/
            index.rst
        meta/
            LICENSE
        public/
            css/
            images/
            js/
        translations/
            messages.en.pot
            zikulaspectheme.pot
        views/
            Block/
                block.html.twig
            Body/
                2col.html.twig
            Include/
                footer.html.twig
                header.html.twig
            admin.html.twig
            home.html.twig
            master.html.twig
    Twig/
        SpecThemeExtension.php (required to create filters and functions)
    vendor/
    ZikulaSpecTheme.php (required)
    CHANGELOG.md
    README.md
    composer.json (required)
    phpunit.xml.dist
```
