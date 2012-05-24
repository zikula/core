<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_DebugToolbar
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This panel displays the current memory usage.
 */
class Zikula_DebugToolbar_Panel_Memory implements Zikula_DebugToolbar_PanelInterface
{
    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "memory";
    }

    /**
     * Returns the memory usage as link name.
     *
     * @return string
     */
    public function getTitle()
    {
        if (function_exists('memory_get_usage')) {
            $totalMemory = sprintf('%.1f', (memory_get_usage() / 1024));

            return $totalMemory.' KB';
        }
    }

    /**
     * Panel contains no content panel.
     *
     * @return string null
     */
    public function getPanelTitle()
    {
        return __('Memory');
    }

    /**
     * Panel contains no content panel.
     *
     * @return string null
     */
    public function getPanelContent()
    {
        return null;
    }

    /**
     * Returns the panel data in raw format.
     *
     * @return number
     */
    public function getPanelData()
    {
        return memory_get_usage();
    }
}
