---
currentMenu: themes
---
# Using custom Bootstrap and Font Awesome files

## Theme

A **theme** can force the core to use a customized build of Bootstrap CSS file by setting the 
`bootstrapPath` parameter value in its `theme.yaml` file:

```yaml
bootstrapPath: '@AcmeFooTheme:css/bootstrap.min.css'
```

## Full site

A **site administrator** can force the core to use a customized assets setting parameter values
in `/config/services_custom.yaml`:

```yaml
zikula.javascript.bootstrap.min.path: '@AcmeFooModule:js/bootstrap.min.js' 
zikula.stylesheet.bootstrap.min.path: '@AcmeFooModule:css/bootstrap.min.css'
zikula.stylesheet.fontawesome.min.path: '@AcmeFooModule:css/font-awesome.min.css'
```

Note these resolve to `public/` subfolders (see [Locations of Templates and Assets](../Templating/TemplateAndAssetLocations.md)).

Instead of putting it into an extension you can also have it inside `public` directly:

```yaml
zikula.stylesheet.bootstrap.min.path: /custom-bootstrap.min.css
```

**Warning:** setting the value in `/config/services_custom.yaml` will affect *every* theme on the site.
