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
     * @var AbstractModule
     */
    private $module;

    /**
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module)
    {
        $this->module = $module;
    }

    /**
     * @return AbstractModule
     */
    public function getModule()
    {
        return $this->module;
    }
}
