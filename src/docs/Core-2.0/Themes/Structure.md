Theme File Structure
====================

Reference: http://symfony.com/doc/current/cookbook/bundles/best_practices.html

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
            theme.yml (required)
            services.yml (required if services are used)
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
