Asset Combination
=================

Zikula Core provides functionality to combine both CSS and JavScript files into one cached file in your twig template
which can improve performance of your website. Additionally, one can further minify the css files and also compress the
response to even further improve performance.

To enable the features, modify these parameters in the `app/config/custom_parameters.yml` file:

| parameter                     | possible values
| ----------------------------- | --------------------------------
| zikula_asset_manager.combine  | true or false (enables combination)
| zikula_asset_manager.lifetime | string like "1 day" or "1 hour"
| zikula_asset_manager.compress | true or false
| zikula_asset_manager.minify   | true or false

If `zikula_asset_manager.combine` is false, none of the other parameters have any effect.

Please note: Asset combination only occurs in the `prod` environment. If `env` is set to another value, it is disabled.
