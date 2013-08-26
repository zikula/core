<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package XXXX
 * @subpackage XXXX
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Url class.
 *
 * @deprecated since Core 1.3.6
 * @see Zikula\Core\ModUrl
 */
class Zikula_ModUrl extends Zikula\Core\ModUrl
{
    function __construct($application, $controller, $action, $language, array $args=array(), $fragment=null)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        parent::__construct($application, $controller, $action, $language, $args, $fragment);
    }
}
