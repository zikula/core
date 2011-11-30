<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Common\HookManager;

use Zikula\Common\EventManager\EventInterface;

/**
 * Hook interface
 */
interface HookInterface extends EventInterface
{
    public function getId();
    public function getCaller();
    public function setCaller($caller);
    public function getAreaId();
    public function setAreaId($areaId);
}
