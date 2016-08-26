Using Routes in Themes
======================

"Hard coding" a route (link) into a theme is typically not a good idea. This is because a
module could be disabled or uninstalled and this will break your theme. If you **must**
hard code a route/link into your theme, you should wrap it in a check for the availability
of the module like so:

    {% if modAvailable('MyCustomModule') %}{{ path('mycustommodule_controller_method') }}{% endif %}
