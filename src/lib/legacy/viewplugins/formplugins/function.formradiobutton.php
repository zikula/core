<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Radiobutton plugin
 *
 * Plugin to generate a radiobutton for selecting one-of-X.
 * Usage with fixed number of radiobuttons:
 *
 * <code>
 * {formradiobutton id='yesButton' dataField='ok'} {formlabel __text='Yes' for='yesButton'}<br/>
 * {formradiobutton id='noButton' dataField='ok'} {formlabel __text='No' for='noButton'}
 * </code>
 *
 * The above case sets 'ok' to either 'yesButton' or 'noButton' in the hashtable returned
 * by {@link Zikula_Form_View::getValues()}. As you can see the radiobutton defaults to using the ID for the returned value
 * in the hashtable. You can override this by setting 'value' to something different.
 *
 * You can also enforce a selection:
 *
 * <code>
 * {formradiobutton id='yesButton' dataField='ok' mandatory=true} {formlabel __text='Yes' for='yesButton'}<br/>
 * {formradiobutton id='noButton' dataField='ok' mandatory=true} {formlabel __text='No' for='noButton']-->
 * </code>
 *
 * If you have a list of radiobuttons inside a for/each loop then you can set the ID to something from the data loop
 * like here:
 * <code>
 * {foreach from=$items item=item}
 *   {formradiobutton id=$item.name dataField='item' mandatory=true} {formlabel text=$item.title for=$item.name}
 * {/foreach}
 * </code>
 *
 * @param array            $params Parameters passed in the block tag.
 * @param Zikula_Form_View $view   Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_function_formradiobutton($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_RadioButton', $params);
}
