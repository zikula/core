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

namespace Zikula\MenuModule\Loader;

use InvalidArgumentException;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
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

    public function __construct(FactoryInterface $factory, PermissionApiInterface $permissionApi)
    {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function load($data): ItemInterface
    {
        if (!$data instanceof MenuItemEntity) {
            throw new InvalidArgumentException(sprintf('Unsupported data. Expected Zikula\MenuModule\Entity\MenuItemEntity but got %s', is_object($data) ? get_class($data) : gettype($data)));
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

    public function supports($data): bool
    {
        return $data instanceof NodeInterface;
    }
}
