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
 * Smarty switch block
 *
 * available parameters:
 *  - params.expr  variable to be tested
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
 * @author   very slightly modified by dasher <dasher@inspiredthinking.co.uk>
 * @link     http://phpinsider.com/smarty-forum/viewtopic.php?t=11121
 * @param    array    $params     All attributes passed to this function from the template
 * @param    string   $content    The content between the block tags
 * @param    object   $smarty     Reference to the Smarty object
 * @return   string   content of the matching case
 */
function smarty_block_switch($params, $content, &$smarty, &$pages)
{
    if (is_null($content) && !array_key_exists('expr', $params)) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('smarty_block_switch', 'expr')));
    }

    return $content;
}
