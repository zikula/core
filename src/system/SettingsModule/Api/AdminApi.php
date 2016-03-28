<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
  *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SettingsModule\Api;

use Zikula_View_Theme;
use Zikula_View;

/**
 * API functions used by administrative controllers
 * @deprecated remove at Core-2.0
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Clear all compiled and cache directories.
     *
     * This function simply calls the theme and renderer modules to refresh the entire site.
     *
     * @return boolean true.
     */
    public function clearallcompiledcaches()
    {
        Zikula_View_Theme::getInstance()->clear_all_cache();
        Zikula_View_Theme::getInstance()->clear_compiled();
        Zikula_View_Theme::getInstance()->clear_cssjscombinecache();
        Zikula_View::getInstance()->clear_all_cache();
        Zikula_View::getInstance()->clear_compiled();

        $cacheClearer = $this->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony');

        return true;
    }
}
