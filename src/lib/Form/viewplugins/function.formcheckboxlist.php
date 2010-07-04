<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Checkbox list
 *
 * Renders a list of checkboxes with the supplied items.
 * Usefull for selecting multiple items.
 *
 * You can set the items directly like this:
 * <code>
 * <!--[formcheckboxlist id="mylist" items=$items]-->
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends pnFormHandler
 * {
 *   function initialize(&$render)
 *   {
 *       $items = array( array('text' => 'A', 'value' => '1'),
 *                       array('text' => 'B', 'value' => '2'),
 *                       array('text' => 'C', 'value' => '3') );
 *
 *       $render->assign('items', $items); // Supply items
 *       $render->assign('mylist', 2);     // Supply selected value
 *   }
 * }
 * </code>
 * Or you can set them indirectly using the plugin's databased features:
 * <code>
 * <!--[formcheckboxlist id="mylist"]-->
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends pnFormHandler
 * {
 *   function initialize(&$render)
 *   {
 *       $items = array( array('text' => 'A', 'value' => '1'),
 *                       array('text' => 'B', 'value' => '2'),
 *                       array('text' => 'C', 'value' => '3') );
 *
 *       $render->assign('mylistItems', $items);  // Supply items
 *       $render->assign('mylist', 2);            // Supply selected value
 *   }
 * }
 * </code>
 *
 * The resulting dataset is a list of strings representing the selected
 * values. So when you do a $data = $render->getValues(); you will
 * get a dataset like this:
 *
 * <code>
 *   array('xxx' => 'valueXX',
 *         'checkboxes' => array('15','17','22','34'),
 *         'yyy' => 'valueYYY')
 * </code>
 *
 * @param array       $params  Parameters passed in the block tag.
 * @param Form_Render &$render Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formcheckboxlist($params, &$render)
{
    return $render->registerPlugin('Form_Plugin_CheckboxList', $params);
}
