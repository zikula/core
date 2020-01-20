<?php

/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Menu\Base;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\RoutesModule\Helper\ControllerHelper;
use Zikula\RoutesModule\Helper\PermissionHelper;

/**
 * This is the extension menu service base class.
 */
abstract class AbstractExtensionMenu implements ExtensionMenuInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var ControllerHelper
     */
    protected $controllerHelper;

    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;

    public function __construct(
        FactoryInterface $factory,
        ControllerHelper $controllerHelper,
        PermissionHelper $permissionHelper
    ) {
        $this->factory = $factory;
        $this->controllerHelper = $controllerHelper;
        $this->permissionHelper = $permissionHelper;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        $contextArgs = ['api' => 'extensionMenu', 'action' => 'get'];
        $allowedObjectTypes = $this->controllerHelper->getObjectTypes('api', $contextArgs);

        $permLevel = self::TYPE_ADMIN === $type ? ACCESS_ADMIN : ACCESS_READ;

        $menu = $this->factory->createItem('zikularoutesmodule' . ucfirst($type) . 'Menu');

        if (self::TYPE_ACCOUNT === $type) {
            return 0 === $menu->count() ? null : $menu;
        }

        $routeArea = self::TYPE_ADMIN === $type ? 'admin' : '';
        if (LinkContainerInterface::TYPE_ADMIN === $type) {
            if ($this->permissionHelper->hasPermission(ACCESS_READ)) {
                $menu->addChild('Frontend', [
                    'route' => 'zikularoutesmodule_route_index',
                ])
                    ->setAttribute('icon', 'fas fa-home')
                    ->setLinkAttribute('title', 'Switch to user area.')
                ;
            }
        } else {
            if ($this->permissionHelper->hasPermission(ACCESS_ADMIN)) {
                $menu->addChild('Backend', [
                    'route' => 'zikularoutesmodule_route_adminindex',
                ])
                    ->setAttribute('icon', 'fas fa-wrench')
                    ->setLinkAttribute('title', 'Switch to administration area.')
                ;
            }
        }
        
        if (
            in_array('route', $allowedObjectTypes, true)
            && $this->permissionHelper->hasComponentPermission('route', $permLevel)
        ) {
            $menu->addChild('Routes', [
                'route' => 'zikularoutesmodule_route_' . $routeArea . 'view'
            ])
                ->setLinkAttribute('title', 'Routes list')
            ;
        }
        if ('admin' === $routeArea && $this->permissionHelper->hasPermission(ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikularoutesmodule_config_config',
            ])
                ->setAttribute('icon', 'fas fa-wrench')
                ->setLinkAttribute('title', 'Manage settings for this application')
            ;
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaRoutesModule';
    }
}
