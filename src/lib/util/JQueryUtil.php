<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * JQueryUtil
 */
class JQueryUtil
{
    /**
     * Load jQuery Theme CSS file
     * 
     * @param string $name 
     */
    public static function loadTheme($name)
    {
        if (is_dir("javascript/jquery-ui/themes/$name")) {
            if (System::isDevelopmentMode()) {
                PageUtil::addVar("stylesheet", "javascript/jquery-ui/themes/$name/jquery-ui.css");
            } else {
                PageUtil::addVar("stylesheet", "javascript/jquery-ui/themes/$name/minified/jquery-ui.min.css");
            }
        }
    }
}
