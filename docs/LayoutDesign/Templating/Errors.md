---
currentMenu: templating
---
# Errors

When the environment variable `APP_DEBUG` is set to `0`, Zikula will catch and render all exceptions as an error page.
Very much like [Symfony](https://symfony.com/doc/current/controller/error_pages.html),
the developer can override the templates to customize the user experience.

The original template is `@ZikulaThemeModule/Exception/error.html.twig`

This template can be overridden in the usual manner - both in the theme and in the `/templates` directories.

In addition, the renderer will attempt to locate an error-specific template according to this pattern:

    `sprintf('@ZikulaThemeModule/Exception/error%s.html.twig', $statusCode)`
    
    e.g. `@ZikulaThemeModule/Exception/error404.html.twig`

This should also be located in the theme or `/templates` directories as desired.


note: When `APP_DEBUG=1`, Symfony's own error handler will display the full trace on the error instead.
