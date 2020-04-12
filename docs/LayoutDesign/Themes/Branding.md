---
currentMenu: themes
---
# Branding

Zikula offers some functions for branding related features.

## Site definition

First, you can include the following things in Twig templates:

- Site name: `{{ siteName() }}`.
- Site slogan: `{{ siteSlogan() }}`.
- Logo: `{{ siteImagePath('logo') }}`.
- Logo for mobile devices: `{{ siteImagePath('mobileLogo') }}`.
- Icon: `{{ siteImagePath('icon') }}`.

All these things are determined by the _Site definition_.

To customise this you have two options:

1. For easy injection of custom images you can just [override them](../Templating/TemplateAndAssetLocations.md).
2. For more advanced customisation you can subclass the site definition and add your own logic. You can place this wherever you want, including your custom theme. For further information please read the [Site definition docs](../../Configuration/Settings/Dev/SiteDefinition.md) at the bottom.

## View images

You can see an overview of all currently used images when calling `/theme/config/config` at your site.

## Additional assets

If you add `{{ siteBranding() }}` to the `<head>` section in your theme it embeds the `system/ThemeModule/Resources/views/Engine/manifest.html.twig`. This references a web application manifests and further images for a more sophisticated branding. Of course you can override this template like any other one if you need custom markup.

Note these assets are located directly in `/public` which means they cannot be adjusted with overriding, but must be overwritten. Background is that it is recommended to put these things directly into the root folder of a page. To generate your own images it is recommended to use [favicon generator](https://realfavicongenerator.net/).

## Branding vs. Themes

The idea behind site definitions and branding functionality is that themes should not make any assumptions about branding. Both topics should not be coupled with each other. This has two benefits:

1. A theme can be better reused for several pages.
2. A page can centrally provide several themes with the same branding.

If a site has for example different themes for spring, summer, autumn and winter, it always uses the same logo, favicon, etc. - and even if it doesn't, it can still implement the logic in its own site definition without redundantly adjusting the themes.
