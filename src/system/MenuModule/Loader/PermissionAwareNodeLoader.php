<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Loader;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Loader\LoaderInterface;
use Knp\Menu\NodeInterface;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class PermissionAwareNodeLoader implements LoaderInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * PermissionAwareNodeLoader constructor.
     * @param FactoryInterface $factory
     * @param PermissionApiInterface $permissionApi
     */
    public function __construct(FactoryInterface $factory, PermissionApiInterface $permissionApi)
    {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function load($data)
    {
        if (!$data instanceof MenuItemEntity) {
            throw new \InvalidArgumentException(sprintf('Unsupported data. Expected Zikula\MenuModule\Entity\MenuItemEntity but got ', is_object($data) ? get_class($data) : gettype($data)));
        }

        $item = $this->factory->createItem($data->getName(), $data->getOptions());
        if (!$this->permissionApi->hasPermission('ZikulaMenuModule::id', '::' . $data->getId(), ACCESS_READ)) {
            $item->setDisplay(false);
            $item->setDisplayChildren(false);
        } else {
            foreach ($data->getChildren() as $childNode) {
                $item->addChild($this->load($childNode));
            }
        }

        return $item;
    }

    public function supports($data)
    {
        return $data instanceof NodeInterface;
    }
}
