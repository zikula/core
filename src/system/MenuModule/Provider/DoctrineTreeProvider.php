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
use Knp\Menu\Loader\NodeLoader;
use Knp\Menu\Provider\MenuProviderInterface;
use Zikula\MenuModule\Entity\RepositoryInterface\MenuItemRepositoryInterface;

class DoctrineTreeProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory = null;

    /**
     * @var NodeLoader
     */
    protected $nodeLoader;

    /**
     * @var MenuItemRepositoryInterface
     */
    protected $menuItemRepository;

    /**
     * @param FactoryInterface $factory the menu factory used to create the menu item
     * @param MenuItemRepositoryInterface $menuItemRepository
     */
    public function __construct(FactoryInterface $factory, MenuItemRepositoryInterface $menuItemRepository)
    {
        $this->factory = $factory;
        $this->nodeLoader = new NodeLoader($factory);
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
        if ($node === null) {
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

        return $node !== null;
    }
}
