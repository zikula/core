---
currentMenu: themes
---
# Theme template overrides

## Theme resource overrides

Placed within the theme `Resources` directory under the target FQ bundle name.

```
Resources/
    config/
    public/
    views/
    ZikulaSpecBundle/
        public/
            css/
                style.css
        views/
            Foo/
                index.html.twig
```

## System resource overrides

Symfony has a system in place to override Resources of any bundle. See 
[Overriding Resources](https://symfony.com/doc/current/bundles/override.html#templates).

**Note that in Zikula, System resource overrides take precedence over Theme resource overrides.**

## Override references

- See `\Zikula\ThemeBundle\EventListener\AddThemePathsToLoaderListener::addThemePaths`
