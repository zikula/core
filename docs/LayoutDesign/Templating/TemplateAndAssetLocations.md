---
currentMenu: templating
---
# Templates and assets

## Template locations

Templates are resolved in the following order:

1. Override on system level: `/templates/bundles/AcmeFooBundle/News/display.html.twig`.
2. Original location: `/src/extensions/Acme/FooBundle/Resources/views/News/display.html.twig`.

## Asset locations

Assets like CSS, images and JavaScript files are resolved in the following order:

1. Override on system level: `/public/overrides/acmefoobundle/js/SomeScript.js`.
2. Original location: `/public/bundles/acmefoo/js/SomeScript.js`.

If the asset file can not be found, it is copied from it's source location to the original public folder.  
For example `/src/extensions/Acme/FooBundle/Resources/public/js/SomeScript.js` will be copied to `/public/bundles/acmefoo/js/SomeScript.js`.
