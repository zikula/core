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
Modify these values in the `/config/packages/zikula_theme.yaml` file (create the file if needed):

```yaml
zikula_theme:
    bootstrap:
        css_path: '/bootstrap/css/bootstrap.min.css'
        js_path: '/bootstrap/js/bootstrap.bundle.min.js'
    font_awesome_path: '/font-awesome/css/all.min.css'
```

Note these resolve to `public/` subfolders (see [Locations of Templates and Assets](../Templating/TemplateAndAssetLocations.md)).

Instead of putting it into an extension you can also have it inside `public` directly:

```yaml
zikula_theme:
    bootstrap:
        css_path: /custom-bootstrap.min.css
```

**Warning:** setting these values will affect *every* theme on the site.
