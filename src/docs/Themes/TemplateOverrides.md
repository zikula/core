Theme Template Overrides
========================

**Theme resource overrides** are placed here under the target FQ Module name.

```
    Resources/
        config/
        public/
        views/
        ZikulaSpecModule/
            public/
                css/
                    style.css
            views/
                Foo/
                    index.html.twig
```

#### System Resource Overrides
Symfony has a system in place to override Resources of any Bundle. See 
[Overriding Resources](http://symfony.com/doc/current/cookbook/bundles/inheritance.html#overriding-resources-templates-routing-etc)
**Note that in Zikula, System Resource overrides take precedence over Theme Resource overrides.**

##### Override references
 - see `\Zikula\Bundle\CoreBundle\EventListener\ThemeListener::setUpThemePathOverrides` (for @ZikulaFoo/Bar/index.html.twig type notation)
 - see `\Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel::locateResource` (for ZikulaFooBundle:Bar:index.html.twig type notation)
