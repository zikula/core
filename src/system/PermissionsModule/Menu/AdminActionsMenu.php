<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\PermissionsModule\Entity\PermissionEntity;

class AdminActionsMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorTrait;

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function menu(FactoryInterface $factory, array $options)
    {
        $this->setTranslator($this->container->get('translator'));
        /** @var PermissionEntity $permission */
        $permission = $options['permission'];
        $lockAdmin = $this->container->get('zikula_extensions_module.api.variable')->get('ZikulaPermissionsModule', 'lockadmin', 1);
        $adminPermId = $this->container->get('zikula_extensions_module.api.variable')->get('ZikulaPermissionsModule', 'adminid', 1);
        $menu = $factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild($this->__f('Insert permission rule before %s', ['%s' => $permission->getPid()]), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-plus')
            ->setLinkAttributes(['class' => 'create-new-permission insertBefore pointer tooltips']);

        if (!$lockAdmin || $adminPermId != $permission->getPid()) {
            $menu->addChild($this->__f('Edit permission %s', ['%s' => $permission->getPid()]), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-pencil')
                ->setLinkAttributes(['class' => 'edit-permission pointer tooltips']);

            $menu->addChild($this->__f('Delete permission %s', ['%s' => $permission->getPid()]), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-trash-o')
                ->setLinkAttributes(['class' => 'delete-permission pointer tooltips']);
        }

        $menu->addChild($this->__('Check a users permission'), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-key')
            ->setLinkAttributes(['class' => 'test-permission pointer tooltips']);

        return $menu;
    }
}
