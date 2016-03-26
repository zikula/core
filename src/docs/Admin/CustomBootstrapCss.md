Using Customized Bootstrap CSS file
===================================

Theme
-----

A **theme** can force the core to use a customized build of Bootstrap css file by setting the 
`bootstrapPath` parameter value in its `theme.yml` file:

    bootstrapPath: themes/BootstrapTheme/Resources/public/css/cerulean.min.css


Full Site
---------

A **site administrator** can force the core to use a customized build of Bootstrap css file by setting a parameter
value in `app/config/custom_parameters.yml`:

    zikula.stylesheet.bootstrap.min.path: /path/to/my/bootstrap.min.css

The recommended location is in `/web`

    zikula.stylesheet.bootstrap.min.path: /web/bootstrap.min.css

WARNING: setting the value in `custom_parameters.yml` will affect *every* theme on the site.
