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


function smarty_block_browserhack($params, $content, Renderer &$render)
{
    if ($content) {
        if (!isset($params['condition'])) {
            $render->trigger_error(__('browserhack block: condition param is required, non specified.'));
        }

        $condition = $params['condition'];
        $output = "<!--[$condition]>$content<![endif]-->";
        if (isset($params['assign'])) {
            $render->assign($params['assign'], $output);
        } else {
            return $output;
        }
    }
}
