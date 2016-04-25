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
 * Tabbed panel set.
 *
 * This plugin is used to create a set of panels with their own tabs for selection.
 * The actual visibility management is handled in JavaScript by setting the CSS styling
 * attribute "display" to "hidden" or not hidden. Default styling of the tabs is rather rudimentary
 * but can be improved a lot with the techniques found at www.alistapart.com.
 * Usage:
 * <code>
 * {formtabbedpanelset}
 *   {formtabbedpanel title='Tab A'}
 *     ... content of first tab ...
 *   {/formtabbedpanel}
 *   {formtabbedpanel title='Tab B'}
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
 * @param array            $params  Parameters passed in the block tag.
 * @param string           $content Content of the block.
 * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
 *
 * @return string The rendered output.
 */
function smarty_block_formtabbedpanelset($params, $content, $view)
{
    return $view->registerBlock('Zikula_Form_Block_TabbedPanelSet', $params, $content);
}
