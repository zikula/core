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
            index.md
        public/
            css/
            images/
            js/
        translations/
            messages+intl-icu.en.yaml
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
    LICENSE
    README.md
    composer.json (required)
    phpunit.xml.dist
```
