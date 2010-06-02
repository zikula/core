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
 *   <!--[formtabbedpanel title="Tab A"]-->
 *     ... content of first tab ...
 *   <!--[/formtabbedpanel]-->
 *   <!--[formtabbedpanel title="Tab B"]-->
 *     ... content of second tab ...
 *   <!--[/formtabbedpanel]-->
 * <!--[/formtabbedpanelset]-->
 * </code>
 * You can place any pnForms plugins inside the individual panels. The tabs
 * require some special styling which is handled by the styles in system/Theme/style/form/style.css.
 * If you want to override this styling then either copy the styles to another stylesheet in the
 * templates directory or change the cssClass attribute to something different than the default
 * class name.
 *
 * @package pnForm
 * @subpackage Plugins
 */
function smarty_block_formtabbedpanelset($params, $content, &$render)
{
    return $render->registerBlock('Form_Block_TabbedPanelSet', $params, $content);
}
