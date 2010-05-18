<?php
/**
* Zikula Application Framework
*
* @copyright (c) 2002, Zikula Development Team
* @link http://www.zikula.org
* @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*
* Settings Module
*
* @package      Zikula_System_Modules
* @subpackage   Settings
*/

/**
 * Smarty function to display a list box with a list of active modules
 * either user or admin capable or all modules
 *
 * <!--[timezoneselect type=all]-->
 *
 * @see          function.timezoneselect.php::smarty_function_timezoneselect()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $selected    The selected timezone
 * @return       string      the results of the module function
 */
function smarty_function_timezoneselect($params, &$smarty)
{
    // we'll make use of the html_options plugin to simplfiy this plugin
    require_once $smarty->_get_plugin_filepath('function', 'html_options');

    $timezones = DateUtil::getTimezones();

    if (!isset($params['selected']) || empty($params['selected']) || !isset($timezones[$params['selected']])) {
        $params['selected'] = System::getVar('timezone_offset');
    }

    // get the formatted list
    $tzlist = smarty_function_html_options(array('options'      => $timezones,
                                                 'selected'     => $params['selected'],
                                                 'print_result' => false),
                                           $smarty);

    return $tzlist;
}
