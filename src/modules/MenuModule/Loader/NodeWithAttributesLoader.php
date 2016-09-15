<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Loader;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Loader\LoaderInterface;
use Zikula\MenuModule\Entity\MenuItemEntity;
use Zikula\MenuModule\NodeWithAttributesInterface;

class NodeWithAttributesLoader implements LoaderInterface
{
    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function load($data)
    {
        if (!$data instanceof NodeWithAttributesInterface) {
            throw new \InvalidArgumentException(sprintf('Unsupported data. Expected Zikula\MenuModule\NodeWithAttributesInterface but got ', is_object($data) ? get_class($data) : gettype($data)));
        }

        /** @var MenuItemEntity $data */
        $item = $this->factory->createItem($data->getName(), $data->getOptions());
        if ($data->hasAttributes()) {
            $method = $data->getParent() ? 'setAttributes' : 'setChildrenAttributes';
            $item->$method($data->getAttributes());
        }

        foreach ($data->getChildren() as $childNode) {
            $item->addChild($this->load($childNode));
        }

        return $item;
    }

    public function supports($data)
    {
        return $data instanceof NodeWithAttributesInterface;
    }
}
