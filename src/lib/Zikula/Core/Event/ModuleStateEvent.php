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
     * @var null|array LEGACY. This will only hold $modinfo if $module is not set, because it is a non-Symfony styled
     * module. Your code MUST always use $module and only use $modinfo if $module is not set. This property can be
     * removed at any time.
     *
     * @deprecated
     */
    public $modinfo;

    /**
     * @var null|AbstractModule The module instance. Null for non-Symfony styled modules.
     */
    private $module;

    /**
     * @param null|AbstractModule $module  The module instance. Null for non-Symfony styled modules.
     * @param null|array          $modinfo Only used for non-Symfony styled modules. Can be removed at any time.
     */
    public function __construct(AbstractModule $module = null, $modinfo = null)
    {
        $this->module = $module;
        $this->modinfo = $modinfo;
    }

    /**
     * Get the module instance. Null for non-Symfony styled modules. If this is null, the public property $modinfo
     * will hold module information.
     *
     * @return null|AbstractModule
     */
    public function getModule()
    {
        return $this->module;
    }
}
