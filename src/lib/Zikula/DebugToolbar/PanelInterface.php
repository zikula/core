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
 * Interface for debug toolbar panels.
 */
interface Zikula_DebugToolbar_PanelInterface
{
    /**
     * Returns the id of this panel.
     *
     * The id will be used to create html ids and thus allow panel acces via javascript.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns the name of the link.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the title of this panel.
     *
     * Return an empty string if this panel does not need an content panel.
     *
     * @return string
     */
    public function getPanelTitle();

    /**
     * Returns the HTML code of this panel.
     *
     * Return an empty string if this panel does not need an content panel.
     *
     * @return string
     */
    public function getPanelContent();

    /**
     * Returns the panel data in raw format.
     *
     * @return mixed
     */
    public function getPanelData();
}
