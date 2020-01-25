# ExtensionMenu

The ExtensionMenu system is a method to store menus that the extension can utilize in various User Interfaces.
In conjunction with the twig function `{{ knp_menu_render() }}`.

The ExtensionMenu class must implement `\Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface`. The menu class should
be placed in a `Menu` directory at the root of the module. Symfony's autowiring and autoconfiguration will take care
of the required tagging, so there is nothing further to do.

Currently, ExtensionMenuCollector directly supports three types of menus: `admin`, `user`, `account` (please use the
constants defined in `ExtensionMenuInterface`). However, *any*
type of menu can be created. For example, a module could create a `bar` type of menu and use the Collector service to 
collect them and utilize them in the module.

See the `ExtensionMenuInterface` and `ExtensionMenuCollector` and examples in all system modules for more information.

## General structure

Menus in the module's ExtensionMenu should be structured like so:
```php
    $menu = $this->factory->createItem('fooAdminMenu');
    if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
        $menu->addChild('list', [
            'route' => 'zikulafoomodule_menu_list',
        ])->setAttribute('icon', 'fas fa-list');
    }
```

The `icon` attribute is the a font-awesome icon identifier. 

## KnpMenuBundle

While it is true that system fully utilizes the KnpMenuBundle system, **it is not required** to manually tag and provide 
a menu alias for the ExtensionMenu like you might do for another Knp menu you create for your module. However, you
_could_ do so if you have need to use the menu elsewhere in your module.
