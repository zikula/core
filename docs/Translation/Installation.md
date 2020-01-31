# Installation of translations

Translations must be located in `translations`.

Files should be be named `<domain>.<locale>.<extension>`.

Examples:

```
translations/messages.de.yaml
translations/routes.de.po
translations/validators.de.xlf
```

After the files have been placed, you must 'install' them by going to _"General Settings > Localisation settings"_.

This will update the `/config/dynamic/generated.yaml` file in order to indicate to the system that your new locale/translation is available.
