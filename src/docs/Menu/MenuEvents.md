Menu Events
===========

Each menu configured in the menu module's UI dispatches the following event:

`\Zikula\MenuModule\Event\ConfigureMenuEvent::POST_CONFIGURE`

Modules can use this event to extend or amend the menus. Read [this tutorial](https://symfony.com/doc/master/bundles/KnpMenuBundle/events.html) to learn how to do it.
