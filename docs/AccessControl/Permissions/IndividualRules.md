---
currentMenu: permissions
---
# Using individual / additional components

Should it become necessary to perform individual checks - for example in a custom [theme](../../LayoutDesign/Themes/README.md) - individual components can be used at any time. The permissions system is not limited to those components that have been announced by extensions.

So it is possible any time to create rules for a `MySpecial:Individual:Check` component and check for that in a template like this:

```twig
{% if hasPermission('MySpecial:Individual:Check', '::', 'ACCESS_READ') %}
    <h3>This is only for some VIP</h3>
{% endif %}
```
