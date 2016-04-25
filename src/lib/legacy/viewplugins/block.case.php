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
 * Zikula_View case block to implement switchs in a template.
 *
 * Available attributes:
 *  - expr (string|numeric) the value to be tested against the expr provided in
 *    the {@link smarty_block_switch() switch} tag.
 *
 * Example:
 * <pre>
 * {switch expr=$var}
 *   {case expr='1'}
 *     do some stuff for case $var == '1'
 *   {/case}
 *   {case expr='2'}
 *     do some stuff for case $var == '2'
 *   {/case}
 *   {case}
 *     default stuff
 *   {/case}
 * {/switch}
 * </pre>
 *
 * @param array       $params  All attributes passed to this function from the template.
 * @param string      $content The content between the block tags.
 * @param Zikula_View $view    Reference to the {@link Zikula_View} object.
 * @param boolean     &$repeat Controls block repetition. See {@link http://www.smarty.net/manual/en/plugins.block.functions.php Smarty - Block Functions}.
 *
 * @see    smarty_block_switch
 *
 * @return void|string The content of the matching case.
 */
function smarty_block_case($params, $content, Zikula_View $view, &$repeat)
{
    if (is_null($content)) {
        // handle block open tag

        // find corresponding switch block
        for ($i = count($view->_tag_stack) - 1; $i >= 0; $i--) {
            list($tag_name, $tag_params) = $view->_tag_stack[$i];
            if ($tag_name == 'switch') {
                break;
            }
        }

        if ($i < 0) {
            // switch block not found
            $view->tigger_error(__('smarty_block_case: case not inside a switch block'));

            return;
        }

        if (isset($tag_params['_done']) && $tag_params['_done']) {
            // another case was already found
            $repeat = false;

            return;
        }

        // $tab_params['expr'] & $params['expr'] needs to be evaluated
        // For now - only worry about the expression passed by the case statement

        if (isset($params['expr']) && ($params['expr'] != $tag_params['expr'])) {
            // page doesn't match
            $repeat = false;

            return;
        }

        // page found
        $view->_tag_stack[$i][1]['_done'] = true;

        return;
    } else {
        // handle block close tag
        return $content;
    }
}
