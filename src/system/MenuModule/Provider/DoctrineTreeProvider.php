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

namespace Zikula\MenuModule\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\MenuModule\Entity\RepositoryInterface\MenuItemRepositoryInterface;
use Zikula\MenuModule\Event\ConfigureMenuEvent;
use Zikula\MenuModule\Loader\PermissionAwareNodeLoader;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class DoctrineTreeProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var PermissionAwareNodeLoader
     */
    protected $nodeLoader;

    /**
     * @var MenuItemRepositoryInterface
     */
    protected $menuItemRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param FactoryInterface $factory the menu factory used to create the menu item
     * @param PermissionApiInterface $permissionApi
     * @param MenuItemRepositoryInterface $menuItemRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        MenuItemRepositoryInterface $menuItemRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->factory = $factory;
        $this->nodeLoader = new PermissionAwareNodeLoader($factory, $permissionApi);
        $this->menuItemRepository = $menuItemRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Retrieves a menu by its name
     *
     * @param string $name
     * @param array $options
     * @return ItemInterface
     * @throws \InvalidArgumentException if the menu does not exists
     */
    public function get($name, array $options = [])
    {
        $node = $this->menuItemRepository->findOneBy(['title' => $name]);
        if (null === $node) {
            throw new \InvalidArgumentException(sprintf('The menu "%s" is not defined.', $name));
        }
        $menu = $this->nodeLoader->load($node);

        $this->eventDispatcher->dispatch(ConfigureMenuEvent::POST_CONFIGURE, new ConfigureMenuEvent($this->factory, $menu, $options));

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
