---
currentMenu: templating
---
# Templates and Assets

## Template locations

Templates are resolved in the following order:

1. Override on system level: `/templates/bundles/AcmeFooBundle/News/display.html.twig`.
2. Override on theme level: `/src/extensions/Acme/CustomTheme/Resources/AcmeFooBundle/views/News/display.html.twig`.
3. Original location: `/src/extensions/Acme/FooBundle/Resources/views/News/display.html.twig`.

Further information: [Theme template overrides](../Themes/TemplateOverrides.md).

## Asset locations

Assets like CSS, images and JavaScript files are resolved in the following order:

1. Override on system level: `/public/overrides/acmefoobundle/js/SomeScript.js`.
2. Override on theme level: `/public/themes/zikuladefaultthemebundle/acmefoobundle/js/SomeScript.js`.
3. Original location: `/public/bundles/acmefoo/js/SomeScript.js`.

If the asset file can not be found, it is copied from it's source location to the original public folder.  
For example `/src/extensions/Acme/FooBundle/Resources/public/js/SomeScript.js` will be copied to `/public/bundles/acmefoo/js/SomeScript.js`.
