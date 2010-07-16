<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Form_Block
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Tabbel panel element
 *
 * Use this with the {@link pnFormTabbedPanelSet}.
 */
class Form_Block_TabbedPanel extends Form_Plugin
{
    /**
     * Panel title.
     *
     * @var string
     */
    public $title;

    /**
     * Panel selected status.
     *
     * @var boolean
     */
    public $selected;

    /**
     * ID of parent panel set (don't touch).
     *
     * @var string
     */
    public $panelSetId;

    /**
     * Panel index (don't touch).
     *
     * @var string
     */
    public $index;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Form_View $render Reference to Form render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Form_Plugin
     * @return void
     */
    function create($render, &$params)
    {
        $this->selected = false;
    }

    /**
     * RenderBegin event handler.
     *
     * @param Form_View $render Reference to Form render object.
     *
     * @return string The rendered output.
     */
    function renderBegin($render)
    {
        // Locate parent panelset and register with it
        $panelSet = $this->parentPlugin;

        while ($panelSet != null && !($panelSet instanceof Form_Block_TabbedPanelSet)) {
            $panelSet = $panelSet->parentPlugin;
        }

        if ($panelSet != null) {
            $panelSet->registerTabbedPanel($render, $this, $this->title);
        }

        $class = ($this->selected ? '' : 'class="tabsToHide"');
        $html = "<div id=\"{$this->panelSetId}_{$this->index}\"$class>\n";
        return $html;
    }

    /**
     * RenderEnd event handler.
     *
     * @param Form_View $render Reference to Form render object.
     *
     * @return string The rendered output.
     */
    function renderEnd($render)
    {
        $html = "</div>\n";
        return $html;
    }
}

