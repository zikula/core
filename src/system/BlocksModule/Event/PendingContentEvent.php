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

namespace Zikula\BlocksModule\Event;

use Zikula\Bundle\CoreBundle\Collection\Container;

class PendingContentEvent
{
    /**
     * This is a Container of Containers
     * @var Container
     */
    private $container;

    public function __construct(string $containerName, ?\ArrayObject $arrayObject = null)
    {
        $this->container = new Container($containerName, $arrayObject);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function addCollection(Container $collection): void
    {
        $this->container->add($collection);
    }
}
