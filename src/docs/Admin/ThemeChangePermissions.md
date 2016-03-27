Allowing Themes to Change via Permissions
=========================================

Upon installation, a permissions rule is set up to allow any user to change the theme for three "utility" themes:

    ZikulaThemeModule::ThemeChange    :(ZikulaRssTheme|ZikulaPrinterTheme|ZikulaAtomTheme):    ACCESS_COMMENT

This means that Rss Feeds will work properly for users that are not logged in, etc.

This rule can be optionally eliminated or changed based on the needs of the site.

For a theme to be accessible via GET (e.g. `index.php?theme=ZikulaRssTheme`) the user must have `ACCESS_COMMENT`
permissions access.
