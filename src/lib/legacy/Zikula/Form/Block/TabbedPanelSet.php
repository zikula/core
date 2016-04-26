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
 * Tabbed panel set
 *
 * This plugin is used to create a set of panels with their own tabs for selection.
 * The actual visibility management is handled in JavaScript by setting the CSS styling
 * attribute "display" to "hidden" or not hidden. Default styling of the tabs is rather rudimentary
 * but can be improved a lot with the techniques found at www.alistapart.com.
 * Usage:
 * <code>
 * {formtabbedpanelset}
 *   {formtabbedpanel __title='Tab A'}
 *     ... content of first tab ...
 *   {/formtabbedpanel}
 *   {formtabbedpanel __title='Tab B'}
 *     ... content of second tab ...
 *   {/formtabbedpanel}
 * {/formtabbedpanelset}
 * </code>
 * You can place any Zikula_Form_View plugins inside the individual panels. The tabs
 * require some special styling which is handled by the styles in system/ThemeModule/Resources/public/css/form/style.css.
 * If you want to override this styling then either copy the styles to another stylesheet in the
 * templates directory or change the cssClass attribute to something different than the default
 * class name.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Block_TabbedPanelSet extends Zikula_Form_AbstractPlugin
{
    /**
     * CSS class name for styling.
     *
     * @var string
     */
    public $cssClass = '';

    /**
     * Currently selected tab.
     *
     * @var integer
     */
    public $selectedIndex = 1;

    /**
     * Registered tab titles.
     *
     * @var array
     * @internal
     */
    public $titles = [];

    /**
     * Internal tab index counter.
     *
     * @var integer
     */
    public $registeredTabIndex = 1;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    /**
     * RenderContent event handler.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param string           $content The content to handle.
     *
     * @return string The (optionally) modified content.
     */
    public function renderContent(Zikula_Form_View $view, $content)
    {
        $html = "<ul id=\"{$this->id}\" class=\"nav nav-tabs formtabbedpanelset {$this->cssClass}\">\n";

        for ($i = 1, $titleCount = count($this->titles); $i <= $titleCount; ++$i) {
            $title = $this->titles[$i - 1];

            $title = $view->translateForDisplay($title);

            if ($this->selectedIndex == $i) {
                $cssClass = 'class="active"';
            } else {
                $cssClass = '';
            }

            $link = "<a href=\"#{$this->id}-tab{$i}\" data-toggle=\"tab\" onclick=\"jQuery('#{$this->id}SelectedIndex').val({$i})\">{$title}</a>";

            $html .= "<li $cssClass>{$link}</li>\n";
        }

        $html .= "</ul>\n";

        $html .= "<input type=\"hidden\" name=\"{$this->id}SelectedIndex\" id=\"{$this->id}SelectedIndex\" value=\"{$this->selectedIndex}\" />\n";

        return $html .'<div class="tab-content">'.$content.'</div>';
    }

    /**
     * Register a tab panel.
     *
     * Called by child panels to register themselves.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param Zikula_Form_Plugin_TabbedPanel &$panel Panel object.
     * @param string $title Panel title.
     *
     * @return void
     */
    public function registerTabbedPanel(Zikula_Form_View $view, &$panel, $title)
    {
        $panel->panelSetId = $this->id;

        if (!$view->isPostBack()) {
            $panel->index = $this->registeredTabIndex++;
            $this->titles[] = $title;
        }

        $panel->selected = ($this->selectedIndex == $panel->index);
    }

    /**
     * Decode event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function decode(Zikula_Form_View $view)
    {
        $this->selectedIndex = (int)$this->request->request->get("{$this->id}SelectedIndex", 1);
    }
}
