<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Form
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Category selector.
 *
 * This plugin creates a category selector using a series of checkboxes.
 * 
 * @param array       $params  Parameters passed in the block tag.
 * @param Form_Render &$render Reference to Form render object.
 * 
 * @return string The rendered output.
 */
function smarty_function_formcategorycheckboxlist($params, &$render)
{
    return $render->registerPlugin('Form_Plugin_CategoryCheckboxList', $params);
}
