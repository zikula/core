<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula display hook response class.
 *
 * Hook handlers should return one of these.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\DisplayHookResponse
 */
class Zikula_Response_DisplayHook extends Zikula\Bundle\HookBundle\Hook\DisplayHookResponse
{
    public function __construct($area, Zikula_View $view, $template)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        $response = $view->fetch($template);
        parent::__construct($area, $response);
    }
}
