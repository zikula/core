---
currentMenu: templating
---
# Asset combination

Zikula Core provides functionality to combine both CSS and JavScript files into one cached file in your twig template
which can improve performance of your website. Additionally, one can further minify the css files and also compress the
response to even further improve performance.

To enable the features, modify these values in the `/config/packages/zikula_theme.yaml` file 
(create the file if needed):

```yaml
zikula_theme:
    asset_manager:
        combine: true # bool
        lifetime: '1 day' # string like '1 day' or '1 hour'
        compress: true # bool
        minify: true # bool
```

If `asset_manager.combine` is false, none of the other parameters have any effect.

Please note: Asset combination only occurs in the `prod` environment. If `env` is set to another value, it is disabled.
