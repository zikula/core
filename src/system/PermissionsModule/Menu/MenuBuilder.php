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

namespace Zikula\PermissionsModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Entity\PermissionEntity;

class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        FactoryInterface $factory,
        VariableApiInterface $variableApi
    ) {
        $this->factory = $factory;
        $this->variableApi = $variableApi;
    }

    public function createAdminActionsMenu(array $options): ItemInterface
    {
        /** @var PermissionEntity $permission */
        $permission = $options['permission'];
        $lockAdmin = $this->variableApi->get('ZikulaPermissionsModule', 'lockadmin', 1);
        $adminPermId = $this->variableApi->get('ZikulaPermissionsModule', 'adminid', 1);
        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild('Insert permission rule before this one', [
            'uri' => '#'
        ])
            ->setAttribute('icon', 'fas fa-plus')
            ->setLinkAttributes(['class' => 'create-new-permission insertBefore pointer tooltips'])
        ;

        if (!$lockAdmin || $adminPermId !== $permission->getPid()) {
            $menu->addChild('Edit this permission rule', [
                'uri' => '#'
            ])
                ->setAttribute('icon', 'fas fa-pencil-alt')
                ->setLinkAttributes(['class' => 'edit-permission pointer tooltips'])
            ;

            $menu->addChild('Delete this permission rule', [
                'uri' => '#'
            ])
                ->setAttribute('icon', 'fas fa-trash-alt')
                ->setLinkAttributes(['class' => 'delete-permission pointer tooltips'])
            ;
        }

        $menu->addChild('Check a users permission', [
            'uri' => '#'
        ])
            ->setAttribute('icon', 'fas fa-key')
            ->setLinkAttributes(['class' => 'test-permission pointer tooltips'])
        ;

        return $menu;
    }
}
