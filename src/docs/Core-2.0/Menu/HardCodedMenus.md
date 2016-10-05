Hard Coded Menus
================

If your custom module uses a menu that never changes or you would like to programatically create
a menu and you would like to make it available within your templates or via a block, you can create
your own as a PHP class.

The [KnpMenuBundle Docs](http://symfony.com/doc/master/bundles/KnpMenuBundle/index.html#method-a-the-easy-way-yay)
outline the basic method and there is no need to repeat that here.

If you wish to load the menu from a Zikula Menu block, set the *Menu Name* to
`MyCustomModule:ClassName:methodName`. Or you can load the menu from within a template as the
docs above state `{{ knp_menu_render('MyCustomModule:ClassName:methodName') }}`

Your menu class must be located in the `/Menu` directory at the root of the module.
