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
 * Browserhack block.
 *
 * Sample:
 * {browserhack condition="if lte IE 8"}something goes here{/browserhack}
 * {browserhack condition="if lte IE 8" assign="var"}something goes here{/browserhack}
 *
 * @param array       $params  Array with keys 'condition' whatever goes in the browserhack,
 *                             and 'assign' to assign rather than display.
 * @param string      $content Content of the block.
 * @param Zikula_View $view    Instance of Zikula_View object.
 *
 * @return string|void
 */
function smarty_block_browserhack($params, $content, Zikula_View $view)
{
    if ($content) {
        if (!isset($params['condition'])) {
            $view->trigger_error(__('browserhack block: condition param is required, none specified.'));
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
