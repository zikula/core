# Using a customized bootstrap CSS file

## Theme

A **theme** can force the core to use a customized build of Bootstrap css file by setting the 
`bootstrapPath` parameter value in its `theme.yml` file:

```yaml
bootstrapPath: "@ZikulaBootstrapTheme:css/cerulean.min.css"
```

## Full site

A **site administrator** can force the core to use a customized build of Bootstrap css file by setting a parameter
value in `app/config/custom_parameters.yml`:

```yaml
zikula.stylesheet.bootstrap.min.path: "@AcmeFooModule:css/bootstrap.min.css"
```

The recommended location is in `/web`.

```yaml
zikula.stylesheet.bootstrap.min.path: "/bootstrap.min.css"
```

WARNING: setting the value in `custom_parameters.yml` will affect *every* theme on the site.
