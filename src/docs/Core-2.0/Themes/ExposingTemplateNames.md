Exposing Template Names
=======================

In past versions of zikula, the user was able to expose template names inside the html source by triggering a flag
in the theme settings at `/theme/admin/config`.

This functionality is modified for Core-2.0 compatible Twig-based themes.

Now template names are exposed in the HTML source automatically by a listener
`Zikula\ThemeModule\EventListener\TemplateNameExposeListener` whenever the `env` parameter is set to `dev` inside
the `app/config/custom_parameters.yml` file.
