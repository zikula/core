<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookDispatcher
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Hook;

use Zikula\Core\ModUrl;
use Zikula\Core\Hook\Response\DisplayHookResponse;

/**
 * DisplayHook class.
 */
class DisplayHook extends \Zikula_DisplayHook
{
    public function __construct($id, ModUrl $url = null)
    {
        parent::__construct(null, $id, $url);
    }
}
