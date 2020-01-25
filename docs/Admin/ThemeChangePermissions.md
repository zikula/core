# Allowing themes to change via permissions

Upon installation, a permissions rule is set up to allow any user to change the theme for three "utility" themes:

```
ZikulaThemeModule::ThemeChange    :(ZikulaRssTheme|ZikulaPrinterTheme|ZikulaAtomTheme):    ACCESS_COMMENT
```

This means that RSS feeds will work properly for users that are not logged in, etc.

This rule can be optionally eliminated or changed based on the needs of the site.
