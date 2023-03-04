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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuBuilder
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly FactoryInterface $factory,
        private readonly Security $security
    ) {
    }

    public function createAdminMenu(array $options): ItemInterface
    {
        $user = $options['user'];
        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        if ($this->security->isGranted('ROLE_EDITOR')) {
            $menu->addChild($this->translator->trans('Edit "%name"', ['%name' => $user->getUname()]), [
                'route' => 'zikulausersbundle_useradministration_modify',
                'routeParameters' => ['user' => $user->getUid()],
            ])->setAttribute('icon', 'fas fa-pencil-alt');
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menu->addChild($this->translator->trans('Delete "%name"', ['%name' => $user->getUname()]), [
                'route' => 'zikulausersbundle_useradministration_delete',
                'routeParameters' => ['user' => $user->getUid()],
            ])->setAttribute('icon', 'fas fa-trash-alt');
        }

        return $menu;
    }
}
