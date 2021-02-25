---
currentMenu: templating
---
# Templates and Assets

## Template locations

Templates are resolved in the following order:

1. Override on system level: `/templates/bundles/AcmeFooModule/News/display.html.twig`.
2. Override on theme level: `/src/extensions/Acme/CustomTheme/Resources/AcmeFooModule/views/News/display.html.twig`.
3. Original location: `/src/extensions/Acme/FooModule/Resources/views/News/display.html.twig`.

Further information: [Theme template overrides](../Themes/TemplateOverrides.md).

## Asset locations

Assets like CSS, images and JavaScript files are resolved in the following order:

1. Override on system level: `/public/overrides/acmefoomodule/js/SomeScript.js`.
2. Override on theme level: `/public/themes/zikulabootstraptheme/acmefoomodule/js/SomeScript.js`.
3. Original location: `/public/modules/acmefoo/js/SomeScript.js`.

If the asset file can not be found, it is copied from it's source location to the original public folder.  
For example `/src/extensions/Acme/FooModule/Resources/public/js/SomeScript.js` will be copied to `/public/modules/acmefoo/js/SomeScript.js`.
