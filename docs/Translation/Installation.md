# Installation of translations

Translations must be located in `translations`

Files should be be `<domain>.<locale>.po`

e.g.

    translations/messages.de.po
    translations/routes.de.po
    translations/validators.de.po
    translations/zikula.de.po
    translations/zikula_javascript.de.po

After the files have been placed, you must 'install' them by going to _"General Settings > Localisation settings"_.

This will update the `/config/dynamic/generated.yaml` file in order to indicate to the system that your new locale/translation is available.
