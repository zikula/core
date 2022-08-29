---
currentMenu: themes
---
# Theme system

The `ThemeBundle` allows for creating several themed dashboards. It brings out of the box support for an admin dashboard as well as an user dashboard, but you can define additional ones if you need to.

For each dashboard you can override the base layout template which is named `@ZikulaTheme/Dashboard/layout_<name>.html.twig`, for example `layout_admin.html.twig`, by copying it to `/templates/bundles/ZikulaThemeBundle/`. Furthermore, there are assets you can customise by copying `ZikulaThemeBundle/Resources/public/dashboard/<name>.(css|js)` to `/public/overrides/zikulathemebundle/dashboard/<name>.(css|js)`.

## Additional topics and further references

- [Branding](Branding.md)
- [Theme annotation](ThemeAnnotation.md)
- [Site definition](../Templating/SiteDefinition.md)
- [EasyAdminBundle: Dashboards](https://symfony.com/bundles/EasyAdminBundle/current/dashboards.html)
