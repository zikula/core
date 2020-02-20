---
currentMenu: dev-extensions
---
# Extension Connections

Extensions may be connected in some way. For example, hooks can provide one method of connecting two extensions together.
When extensions are connected, it may be useful to provide menu items to that module from its own Admin UI. Zikula provides
a method to do so as a sub-menu to a 'Connections' parent menu item.

## Connections Menu

Related classes:

- `\Zikula\ExtensionsModule\Event\ConnectionsMenuEvent`
- `\Zikula\ExtensionsModule\Listener\ExtensionConnectionsListener`

### How to add a child menu item to the Connections Menu

Create a subscriber class for the `ConnectionsMenuEvent::class` event

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Event\ConnectionsMenuEvent;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExampleFooSubscriber implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi
    ) {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConnectionsMenuEvent::class => 'addMenuItem'
        ];
    }

    public function addMenuItem(ConnectionsMenuEvent $event): void
    {
        if (!$this->permissionApi->hasPermission($event->getExtensionName() . '::', '::', ACCESS_ADMIN)) {
            return;
        }

        if ('ZikulaUsersModule' === $event->getExtensionName()) {
            // only add to menu for the Users module
            $event->addChild($this->translator->trans('MyFooExtension Connection'), [
                'route' => 'acmefoomodule_admin_foo',
                'routeParameters' => ['moduleName' => $event->getExtensionName()]
            ]);
        }
    }
}
```
