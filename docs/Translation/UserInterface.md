---
currentMenu: translation
---
# UI-based translations

Zikula 3 introduces two new abilities for creating and changing translations.

Both can be accessed in the Settings module at the localisation settings if the environment is set to `dev`.

## Edit in place functionality

Allows to edit translations directly in the context of a page ([demo](https://php-translation.readthedocs.io/en/latest/_images/edit-in-place-demo.gif)).

Edit in place has some limitations you should be aware of:

- It always works for the current locale only; so in order to update translation for multiple languages you need to switch your site's language.
- It can only work with one single configuration. By default this is set to `zikula`, so it works for the core. If you want to use it for a module or a theme, you need to lookup the corresponding configuration name (e.g. `zikulabootstraptheme`) in `/config/dynamic/generated.yml` and use this in `/config/packages/dev/php_translation.yaml` at `translation.edit_in_place.config_name`.

You can utilise HTML formatting options when your translation keys end with the `.html` suffix ([screenshot](https://php-translation.readthedocs.io/en/latest/_images/demo-html-editor.png)).

## Web UI: provides a web interface to add, edit and remove translations

It features a dashboard page ([screenshot](https://php-translation.readthedocs.io/en/latest/_images/webui-dashboard.png)) for the overall progress. When you dive into a translation domain you can use a form to change the translation messages ([screenshot](https://php-translation.readthedocs.io/en/latest/_images/webui-page.png)).

The web UI is able to handle multiple configurations and target languages.
