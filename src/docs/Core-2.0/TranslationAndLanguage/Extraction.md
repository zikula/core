Translation Extraction
======================

Extract all the core translations

    php -dmemory_limit=2G app/console translation:extract template --output-format=po --output-dir=app/Resources/translations --enable-extractor=jms_i18n_routing --dir=system --dir=lib/Zikula/Bundle

Use native symfony translation extraction (via the jms_translation_bundle).

    php app/console translation:extract en --bundle=AcmeDemoModule --output-format=pot

The files will automatically be extracted to the `/Resources/translations` directory.

See the help file for more information:

    php app/console translation:extract -h
