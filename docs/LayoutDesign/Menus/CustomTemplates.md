---
currentMenu: menus
---
# Custom menu templates

The default template for all menus is `Knp/Menu/Resources/views/knp_menu.html.twig`.

ZikulaThemeBundle provides two custom overrides:

- `@ZikulaThemeBundle/Menu/bootstrap_fontawesome.html.twig`
- `@ZikulaThemeBundle/Menu/actions.html.twig`

Both of these templates extend the original.

The first (`bootstrap_fontawesome`) is provided by [this gist](https://gist.github.com/nateevans/9958390)
and provides bootstrap and fontawesome functionality built in (we updated that for Bootstrap 4 and Font Awesome 5 a bit).
This one should be used as the standard template unless you are certain these features are not needed
(note that some provided attributes or options will not work without this template).

The second is used within the MenuBundle itself to provide the item actions in the admin
interface. It is customized to remove the label and provide a tooltip instead.

You may customize and specify your own template as needed for your custom application.
