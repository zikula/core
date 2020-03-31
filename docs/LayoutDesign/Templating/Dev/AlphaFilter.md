---
currentMenu: templating
---
# AlphaFilter

In large result sets that are alphanumerically based, it is often helpful to display a filter selector that can
quickly link to results beginning with that letter or number. Zikula provides a quick method to do so.
In order to utilize Zikula's AlphaFilter, the following steps should be followed:

### In the controller
```php
use Zikula\Bundle\CoreBundle\Filter\AlphaFilter;
// ...

return [
    'templateParam' => $value,
    'alpha' => new AlphaFilter('mycustomroute', $routeParameters, $currentLetter),
];
```

### In the template

```twig
{{ include(alpha.template) }}
```

### Options

By default, the filter does not display digits. In order to enable them, add a fourth argument to the constructor:

```php
new AlphaFilter('mycustomroute', $routeParameters, $currentLetter, true);
```

The template can be customized by overriding `@Core/Filter/AlphaFilter.html.twig` in all the normal ways.
You can also simply set your own custom template in the controller `$myAlphaFilter->setTemplate('@MyBundle/Custom/template.html.twig');`
