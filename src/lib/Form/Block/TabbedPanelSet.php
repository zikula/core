<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Tabbed panel set
 *
 * This plugin is used to create a set of panels with their own tabs for selection.
 * The actual visibility management is handled in JavaScript by setting the CSS styling
 * attribute "display" to "hidden" or not hidden. Default styling of the tabs is rather rudimentary
 * but can be improved a lot with the techniques found at www.alistapart.com.
 * Usage:
 * <code>
 * <!--[formtabbedpanelset]-->
 * <!--[formtabbedpanel title="Tab A"]-->
 * ... content of first tab ...
 * <!--[/formtabbedpanel]-->
 * <!--[formtabbedpanel title="Tab B"]-->
 * ... content of second tab ...
 * <!--[/formtabbedpanel]-->
 * <!--[/formtabbedpanelset]-->
 * </code>
 * You can place any pnForms plugins inside the individual panels. The tabs
 * require some special styling which is handled by the styles in system/Theme/pnstyle/form/style.css.
 * If you want to override this styling then either copy the styles to another stylesheet in the
 * templates directory or change the cssClass attribute to something different than the default
 * class name.
 */
class Form_Block_TabbedPanelSet extends Form_Plugin
{
    /**
     * CSS class name for styling
     * @var string
     */
    public $cssClass = 'linktabs';

    /**
     * Currently selected tab
     * @var int
     */
    public $selectedIndex = 1;

    /**
     * Registered tab titles
     * @var string-array
     * @internal
     */
    public $titles = array();

    /**
     * Internal tab index counter
     * @var int
     */
    public $registeredTabIndex = 1;

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function renderContent(&$render, $content)
    {
        // Beware - working on 1-based offset!


        static $firstTime = true;
        if ($firstTime) {
            PageUtil::addVar('javascript', 'javascript/ajax/prototype.js');
            PageUtil::addVar('javascript', 'system/Theme/pnjavascript/form/pnform_tabbedpanelset.js');
            PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('pnForm'));
            PageUtil::addVar('footer', "<script type=\"text/javascript\">$$('.tabsToHide').invoke('hide')</script>");
        }
        $firstTime = false;

        $idHtml = $this->getIdHtml();

        $html = "<div class=\"{$this->cssClass}\"{$idHtml}><ul><li>&nbsp;</li>\n";

        for ($i = 1, $titleCount = count($this->titles); $i <= $titleCount; ++$i) {
            $title = $this->titles[$i - 1];

            $cssClass = 'linktab';
            $selected = ($i == $this->selectedIndex);

            $title = $render->TranslateForDisplay($title);

            if ($selected) {
                $cssClass .= ' selected';
            }

            $link = "<a href=\"#\" onclick=\"return pnFormTabbedPanelSet.handleTabClick($i,$titleCount,'{$this->id}')\">$title</a>";

            $html .= "<li id=\"{$this->id}Tab_{$i}\" class=\"$cssClass\">$link</li><li>&nbsp;</li>\n";
        }

        $html .= "</ul></div><div style=\"clear: both\"></div>\n";

        $html .= "<input type=\"hidden\" name=\"{$this->id}SelectedIndex\" id=\"{$this->id}SelectedIndex\" value=\"{$this->selectedIndex}\"/>\n";

        return $html . $content;
    }

    // Called by child panels to register themselves
    function registerTabbedPanel(&$render, &$panel, $title)
    {
        $panel->panelSetId = $this->id;
        if (!$render->IsPostBack()) {
            $panel->index = $this->registeredTabIndex++;
            $this->titles[] = $title;
        }
        $panel->selected = ($this->selectedIndex == $panel->index);
    }

    function decode(&$render)
    {
        $this->selectedIndex = (int) FormUtil::getPassedValue("{$this->id}SelectedIndex", 1);
    }
}

