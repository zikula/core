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

namespace Zikula\ExtensionsModule\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Zikula\ExtensionsModule\AbstractModule;

/**
 * Class ModuleStateEvent
 */
class ModuleStateEvent extends Event
{
    /**
     * @var null|AbstractModule The module instance. Null when Module object is not available
     */
    private $module;

    /**
     * An array of info for the module. Possibly a result of calling $extensionEntity->toArray().
     *
     * @var null|array
     */
    private $modInfo;

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
