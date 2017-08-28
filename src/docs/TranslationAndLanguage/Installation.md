Installation of translations
============================

Translations must be located in `app/Resources/translations`

Files should be be `<domain>.<locale>.po`

e.g.

    app/Resources/translations/messages.de.po
    app/Resources/translations/routes.de.po
    app/Resources/translations/validators.de.po
    app/Resources/translations/zikula.de.po
    app/Resources/translations/zikula_javascript.de.po

After the files have been placed, you must 'install' them by going to 

General Settings > Localization Settings

and (without changing anything unless you want to), click 'save'

This will alter the 

    src/app/config/dynamic/generated.yml

file in order to indicate to the system that your new locale/translation is available.
