<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ProfileBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Constant as UsersConstant;

class MenuBuilder
{
    use TranslatorTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(
        TranslatorInterface $translator,
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi
    ) {
        $this->setTranslator($translator);
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function createAdminMenu(array $options): ItemInterface
    {
        $user = $options['user'];
        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        if ($this->permissionApi->hasPermission(UsersConstant::MODNAME . '::', '::', ACCESS_EDIT)) {
            $menu->addChild($this->trans('Edit "%name"', ['%name' => $user->getUname()]), [
                'route' => 'zikulausersbundle_useradministration_modify',
                'routeParameters' => ['user' => $user->getUid()],
            ])->setAttribute('icon', 'fas fa-pencil-alt');
        }
        if ($this->permissionApi->hasPermission(UsersConstant::MODNAME . '::', '::', ACCESS_DELETE)) {
            $menu->addChild($this->trans('Delete "%name"', ['%name' => $user->getUname()]), [
                'route' => 'zikulausersbundle_useradministration_delete',
                'routeParameters' => ['user' => $user->getUid()],
            ])->setAttribute('icon', 'fas fa-trash-alt');
        }

        return $menu;
    }
}
