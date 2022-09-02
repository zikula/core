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

namespace Zikula\PermissionsBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\PermissionsBundle\Entity\Permission;

class MenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly bool $lockAdminRule,
        private readonly int $adminRuleId
    ) {
    }

    public function createAdminActionsMenu(array $options): ItemInterface
    {
        /** @var Permission $permission */
        $permission = $options['permission'];

        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild('Insert permission rule before this one', [
            'uri' => '#'
        ])
            ->setAttribute('icon', 'fas fa-plus')
            ->setLinkAttributes(['class' => 'create-new-permission insertBefore pointer tooltips'])
        ;

        if (!$this->lockAdminRule || $this->adminRuleId !== $permission->getPid()) {
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
