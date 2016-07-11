<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
     * An array of info for the module. Possibly a result of calling legacy ModUtil::getInfo()
     * or $extensionEntity->toArray().
     * Property will be converted to PRIVATE at Core-2.0.
     *
     * @var null|array
     */
    public $modinfo;

    /**
     * @var null|AbstractModule The module instance. Null for non-Symfony styled modules
     *   or when Module object is not available.
     */
    private $module;

    /**
     * @param null|AbstractModule $module The module instance.
     * @param null|array $modinfo
     */
    public function __construct(AbstractModule $module = null, $modinfo = null)
    {
        $this->module = $module;
        $this->modinfo = $modinfo;
    }

    /**
     * Get the module instance.
     *
     * @return null|AbstractModule
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return array|null
     */
    public function getModInfo()
    {
        return $this->modinfo;
    }
}
