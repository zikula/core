<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Zikula\MenuModule\Entity\RepositoryInterface\MenuItemRepositoryInterface;
use Zikula\MenuModule\Loader\PermissionAwareNodeLoader;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class DoctrineTreeProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory = null;

    /**
     * @var PermissionAwareNodeLoader
     */
    protected $nodeLoader;

    /**
     * @var MenuItemRepositoryInterface
     */
    protected $menuItemRepository;

    /**
     * @param FactoryInterface $factory the menu factory used to create the menu item
     * @param PermissionApiInterface $permissionApi
     * @param MenuItemRepositoryInterface $menuItemRepository
     */
    public function __construct(FactoryInterface $factory, PermissionApiInterface $permissionApi, MenuItemRepositoryInterface $menuItemRepository)
    {
        $this->factory = $factory;
        $this->nodeLoader = new PermissionAwareNodeLoader($factory, $permissionApi);
        $this->menuItemRepository = $menuItemRepository;
    }

    /**
     * Retrieves a menu by its name
     *
     * @param string $name
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     * @throws \InvalidArgumentException if the menu does not exists
     */
    public function get($name, array $options = [])
    {
        $node = $this->menuItemRepository->findOneBy(['title' => $name]);
        if (null === $node) {
            throw new \InvalidArgumentException(sprintf('The menu "%s" is not defined.', $name));
        }
        $menu = $this->nodeLoader->load($node);

        return $menu;
    }

    /**
     * Checks whether a menu exists in this provider
     *
     * @param string $name
     * @param array $options
     * @return bool
     */
    public function has($name, array $options = [])
    {
        $node = $this->menuItemRepository->findOneBy(['title' => $name]);

        return null !== $node;
    }
}
