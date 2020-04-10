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

namespace Zikula\MenuModule\Provider;

use InvalidArgumentException;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
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
     * @throws InvalidArgumentException if the menu does not exists
     */
    public function get(string $name, array $options = []): ItemInterface
    {
        $node = $this->menuItemRepository->findOneBy(['title' => $name]);
        if (null === $node) {
            throw new InvalidArgumentException(sprintf('The menu "%s" is not defined.', $name));
        }
        $menu = $this->nodeLoader->load($node);

        $this->eventDispatcher->dispatch(new ConfigureMenuEvent($this->factory, $menu, $options), ConfigureMenuEvent::POST_CONFIGURE);

        return $menu;
    }

    public function has(string $name, array $options = []): bool
    {
        $node = $this->menuItemRepository->findOneBy(['title' => $name]);

        return null !== $node;
    }
}
