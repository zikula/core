Custom Menu Templates
=====================

The default template for all menus is `Knp/Menu/Resources/views/knp_menu.html.twig`.

ZikulaMenuModule provides two custom overrides:
 - `ZikulaMenuModule:Override:bootstrap_fontawesome.html.twig`
 - `ZikulaMenuModule:Override:actions.html.twig`

both of these templates extend the original.

The first (`bootstrap_fontawesome`) is provided by [https://gist.github.com/nateevans/9958390]
and provides bootstrap and fontawesome functionality built in and should be used as the standard
template unless you are certain these features are not needed (note that some provided attributes
or options will not work without this template).

The second is used within the MenuModule itself to provide the item actions in the admin
interface. It is customized to remove the label and provide a tooltip instead.

You may customize and specify your own template as needed for your custom application.
