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
 * Smarty function to create a tabbed panel.
 *
 * @param array            $params  Parameters passed in the block tag.
 * @param string           $content Content of the block.
 * @param Zikula_Form_View $render  Reference to Form render object.
 *
 * @return string The rendered output.
 */
function smarty_block_formtabbedpanel($params, $content, $render)
{
    return $render->registerBlock('Zikula_Form_Block_TabbedPanel', $params, $content);
}
