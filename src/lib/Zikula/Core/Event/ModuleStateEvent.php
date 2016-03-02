<?php
/**
 * Copyright 2009 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
