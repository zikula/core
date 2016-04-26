<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Tabbel panel element
 *
 * Use this with the {@link Zikula_Form_Plugin_TabbedPanel}.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Block_TabbedPanel extends Zikula_Form_AbstractPlugin
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
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        $this->selected = false;
    }

    /**
     * RenderBegin event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output.
     */
    public function renderBegin(Zikula_Form_View $view)
    {
        // Locate parent panelset and register with it
        $panelSet = $this->parentPlugin;

        while ($panelSet != null && !($panelSet instanceof Zikula_Form_Block_TabbedPanelSet)) {
            $panelSet = $panelSet->parentPlugin;
        }

        if ($panelSet != null) {
            $panelSet->registerTabbedPanel($view, $this, $this->title);
        }

        $active = ($this->selected ? ' active' : '');

        $html = "<div id=\"{$this->panelSetId}-tab{$this->index}\" class=\"tab-pane$active\">\n";

        return $html;
    }

    /**
     * RenderEnd event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output.
     */
    public function renderEnd(Zikula_Form_View $view)
    {
        $html = "</div>\n";

        return $html;
    }
}
