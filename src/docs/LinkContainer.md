# LinkContainer

The LinkContainer system is a method to store links that the extension can utilize in various User Interfaces.
In conjunction with the twig function `{{ moduleLinks() }}`.

The LinkContainer class must implement `\Zikula\Core\LinkContainer\LinkContainerInterface`.

Currently, LinkContainerCollector directly supports three types of links: `admin`, `user`, `account`. However, *any*
type of link can be used. For example, a module could create a `bar` type of link and use the Collector service to 
collect them and utilize them in the module.

See the `LinkContainerInterface` and `LinkContainerCollector` for more information.

## General link array structure

Links in the module's LinkContainer should be and array structured like so:
```php
[
    'url'   => $this->router->generate('acmefoomodule_bar_baz'),
    'text' => $this->translator->__('link text'),
    'icon'  => 'user'
]
```

The `icon` parameter is the 'suffix' portion of a font-awesome icon identifier (the part after `fa-`).

## Account links

Account links should be structured exactly like the general structure for Core-2.0 compatibility. However, some
backward compatibility for the icon is built in if required. If the icon value contains a dot (`.`) then the system
will attempt to resolve the full value to an image file placed in the module's `Resources/public/images` directory.
For example:
```php
[
    'url'   => $this->router->generate('acmefoomodule_bar_baz'),
    'text' => $this->translator->__('link text'),
    'icon'  => 'myimage.png'
]
```

will resolve the icon to `FooModule/Resources/public/images/myimage.png` and render a standard `img` element. 

Note that in previous versions, the AccountApi used 'title' as the array key and not 'text'. 
