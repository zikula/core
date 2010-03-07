<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty case block to implement switchs in a template
 *
 * available parameters:
 *  - params.expr  string|numeric to be tested
 *  - content      contents of the block
 *
 * Example
 * <!--[switch expr=$var]-->
 *   <!--[case expr='1']-->
 *     do some stuff for case $var == '1'
 *   <!--[/case]-->
 *   <!--[case expr='2']-->
 *     do some stuff for case $var == '2'
 *   <!--[/case]-->
 *   <!--[case]-->
 *     default stuff
 *   <!--[/case]-->
 * <!--[/switch]-->
 *
 * @author   messju mohr <messju@lammfellpuschen.de>
 * @author   slightly modified and expanded by dasher <dasher@inspiredthinking.co.uk>
 * @see      smarty_block_switch
 * @param    array    $params     All attributes passed to this function from the template
 * @param    string   $content    The content between the block tags
 * @param    object   $smarty     Reference to the Smarty object
 * @return   string   content of the matching case
 */
function smarty_block_case($params, $content, &$smarty, &$repeat)
{
    if (is_null($content)) {
        // handle block open tag

        // find corresponding switch block
        for ($i = count($smarty->_tag_stack) - 1; $i >= 0; $i--) {
            list ($tag_name, $tag_params) = $smarty->_tag_stack[$i];
            if ($tag_name == 'switch')
                break;
        }

        if ($i < 0) {
            // switch block not found
            $smarty->tigger_error(__('smarty_block_case: case not inside a switch block'));
            return;
        }

        if (isset($tag_params['_done']) && $tag_params['_done']) {
            // another case was already found
            $repeat = false;
            return;
        }

        // $tab_params['expr'] & $params['expr'] needs to be evaluated
        // For now - only worry about the expression passed by the case statement

        $testExpression = smarty_block_case_eval($params['expr']);

        if (isset($params['expr']) && ($testExpression != $tag_params['expr'])) {
            // page doesn't match
            $repeat = false;
            return;
        }

        // page found
        $smarty->_tag_stack[$i][1]['_done'] = true;
        return;

    } else {
        // handle block close tag
        return $content;
    }

}

function smarty_block_case_eval($expression = '')
{
    // Evaluates the expression
    $wrapper = "echo {expression} ;";
    $testExpression = str_ireplace("{expression}", $expression, $wrapper);

    ob_start();
    eval(trim($testExpression));
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
