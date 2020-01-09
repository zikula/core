<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Entity\PermissionEntity;

class MenuBuilder
{
    use TranslatorTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        TranslatorInterface $translator,
        FactoryInterface $factory,
        VariableApiInterface $variableApi
    ) {
        $this->setTranslator($translator);
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
        $menu->addChild($this->trans('Insert permission rule before %s', ['%s' => $permission->getPid()]), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-plus')
            ->setLinkAttributes(['class' => 'create-new-permission insertBefore pointer tooltips']);

        if (!$lockAdmin || $adminPermId !== $permission->getPid()) {
            $menu->addChild($this->trans('Edit permission %s', ['%s' => $permission->getPid()]), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-pencil-alt')
                ->setLinkAttributes(['class' => 'edit-permission pointer tooltips']);

            $menu->addChild($this->trans('Delete permission %s', ['%s' => $permission->getPid()]), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-trash-alt')
                ->setLinkAttributes(['class' => 'delete-permission pointer tooltips']);
        }

        $menu->addChild($this->trans('Check a users permission'), [
                'uri' => '#'
            ])->setAttribute('icon', 'fa fa-key')
            ->setLinkAttributes(['class' => 'test-permission pointer tooltips']);

        return $menu;
    }
}
