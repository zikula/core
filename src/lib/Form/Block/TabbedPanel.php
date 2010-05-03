<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
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
     * Panel title
     * @var string
     */
    public $title;
    
    /**
     * Panel selected status
     * @internal
     * @var bool
     */
    public $selected;
    
    /**
     * ID of parent panel set (don't touch)
     * @internal
     */
    public $panelSetId;
    
    /**
     * Panel index (don't touch)
     * @internal
     */
    public $index;
    
    function getFilename()
    {
        return __FILE__;
    }
    
    function create(&$render, &$params)
    {
        $this->selected = false;
    }
    
    function renderBegin(&$render)
    {
        // Locate parent panelset and register with it
        $panelSet = &$this->parentPlugin;
        
        while ($panelSet != null && strcasecmp(get_class($panelSet), 'pnformtabbedpanelset') != 0) {
            $panelSet = &$panelSet->parentPlugin;
        }
        
        if ($panelSet != null) {
            $panelSet->registerTabbedPanel($render, $this, $this->title);
        }
        
        $class = ($this->selected ? '' : 'class="tabsToHide"');
        $html = "<div id=\"{$this->panelSetId}_{$this->index}\"$class>\n";
        return $html;
    }
    
    function renderEnd(&$render)
    {
        $html = "</div>\n";
        return $html;
    }
}

