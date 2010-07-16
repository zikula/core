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
 * Web form label.
 *
 * Use this to create labels for your input fields in a web form. Example:
 * <code>
 *   <!--[formlabel text="Title" for="title"]-->:
 *   <!--[formtextinput id="title"]-->
 * </code>
 * The rendered output is an HTML label element with the "for" value
 * set to the supplied id. In addition to this, the pnFormLabel plugin also sets
 * "myLabel" on the "pointed-to" plugin to the supplied label text. This enables
 * the validation summary to display the label text.
 *
 * @param array       $params  Parameters passed in the block tag.
 * @param Form_View &$render Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formlabel($params, &$render)
{
    return $render->registerPlugin('Form_Plugin_Label', $params);
}
