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
 * This panel displays the zikula version.
 */
class Zikula_DebugToolbar_Panel_Version implements Zikula_DebugToolbar_PanelInterface
{
    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "version";
    }

    /**
     * Returns the zikula version as linke name.
     *
     * @return string
     */
    public function getTitle()
    {
        return Zikula_Core::VERSION_NUM;
    }

    /**
     * Panel contains no content panel.
     *
     * @return string null
     */
    public function getPanelTitle()
    {
        return null;
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
     * @return string
     */
    public function getPanelData()
    {
        return Zikula_Core::VERSION_NUM;
    }
}
