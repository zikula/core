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
 * This panel displays the page render time.
 */
class Zikula_DebugToolbar_Panel_RenderTime implements Zikula_DebugToolbar_PanelInterface
{
    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "rendertime";
    }

    /**
     * Returns the page render time as link name.
     *
     * @return string
     */
    public function getTitle()
    {
        return round($this->getTimeDiff()*1000, 3).' ms';
    }

    /**
     * Panel contains no content panel.
     *
     * @return string null
     */
    public function getPanelTitle()
    {
        return __('Render time');
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
     *  Returns the page render time.
     *
     * @return number
     */
    public function getTimeDiff()
    {
        $start = ServiceUtil::getManager()->getArgument('debug.toolbar.panel.rendertime.start');
        $end =  microtime(true);

        $diff = $end - $start;

        return $diff;
    }

    /**
     * Returns the panel data in raw format.
     *
     * @return number
     */
    public function getPanelData()
    {
        return $this->getTimeDiff();
    }
}
