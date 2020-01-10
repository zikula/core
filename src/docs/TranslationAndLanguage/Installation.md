# Installation of translations

Translations must be located in `translations`

Files should be be `<domain>.<locale>.po`

e.g.

    translations/messages.de.po
    translations/routes.de.po
    translations/validators.de.po
    translations/zikula.de.po
    translations/zikula_javascript.de.po

After the files have been placed, you must 'install' them by going to 

General Settings > Localization Settings

and (without changing anything unless you want to), click 'save'

This will alter the 

    src/app/config/dynamic/generated.yml

file in order to indicate to the system that your new locale/translation is available.
