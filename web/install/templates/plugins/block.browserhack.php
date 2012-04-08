<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Browserhack block.
 *
 * Sample:
 * {browserhack condition="if lte IE 7"}something goes here{/browserhack}
 * {browserhack condition="if lte IE 7" assign="var"}something goes here{/browserhack}
 *
 * @param array       $params  Array with keys 'condition' whatever goes in the browserhack,
 *                                 and 'assign' to assign rather than display.
 * @param string      $content Content of the block.
 * @param Zikula_View $view Instance of Zikula_View object.
 *
 * @return string|void
 */
function smarty_block_browserhack($params, $content, $view)
{
    if ($content) {
        if (!isset($params['condition'])) {
            $view->trigger_error(__('browserhack block: condition param is required, non specified.'));
        }

        $condition = $params['condition'];
        $output = "<!--[$condition]>$content<![endif]-->";
        if (isset($params['assign'])) {
            $view->assign($params['assign'], $output);
        } else {
            return $output;
        }
    }
}
