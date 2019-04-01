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

namespace Zikula\Core\Event;

use Symfony\Component\EventDispatcher\Event;
use Zikula\Core\AbstractModule;

/**
 * Class ModuleStateEvent
 */
class ModuleStateEvent extends Event
{
    /**
     * An array of info for the module. Possibly a result of calling $extensionEntity->toArray().
     *
     * @var null|array
     */
    private $modInfo;

    /**
     * @var null|AbstractModule The module instance. Null when Module object is not available
     */
    private $module;

    public function __construct(AbstractModule $module = null, array $modInfo = null)
    {
        $this->module = $module;
        $this->modInfo = $modInfo;
    }

    public function getModule(): ?AbstractModule
    {
        return $this->module;
    }

    public function getModInfo(): ?array
    {
        return $this->modInfo;
    }
}
