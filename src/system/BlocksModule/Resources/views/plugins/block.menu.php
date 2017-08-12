<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Smarty menu block to parse recursive menu
 *
 *
 * @param array   $params  All attributes passed to this function from the template
 * @param string  $content The content between the block tags
 * @param Smarty  $smarty  Reference to the {@link Zikula_View} object
 * @param boolean $repeat  Controls block repetition. See {@link http://www.smarty.net/manual/en/plugins.block.functions.php Smarty - Block Functions}
 *
 * @return void|string The content of the matching case
 */
function smarty_block_menu($params, $content, $smarty, &$repeat)
{
    if (!isset($params['from'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_block_menu', 'from']));

        return false;
    }
    if (!isset($params['item'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_block_menu', 'item']));

        return false;
    }

    // find this block in smarty tag stack
    foreach ($smarty->_tag_stack as $key => $tag_stack) {
        if ($tag_stack[0] == 'menu') {
            $menuTagStackKey = $key;
        }
    }
    if (is_null($content)) {
        $smarty->_tag_stack[$menuTagStackKey][1]['_content'] = [];
    }

    $from = $params['from'];
    $item = $params['item'];
    $key   = isset($params['key']) ? $params['key'] : null;
    $index = isset($params['_index']) ? $params['_index'] : 0;
    $total = count($from);
    $repeat = $index < $total;
    $iterator = new ArrayIterator($from);

    try {
        $iterator->seek($index);
        $currentKey = $iterator->key();
    } catch (Exception $e) {
        $currentKey = null;
    }

    try {
        $iterator->seek($index - 1);
        $lastKey = $iterator->key();
    } catch (Exception $e) {
        $lastKey = null;
    }

    if (isset($params['name'])) {
        $menuProps = [
            'index' => $index,
            'iteration' => $index + 1,
            'total' => $total,
            'first' => (bool)is_null($content),
            'last' =>  $index + 1 >= $total
        ];
    }

    if ($repeat || (empty($from) && is_null($content))) {
        $smarty->assign($item, isset($from[$currentKey]) ? $from[$currentKey] : null);
        $smarty->assign('index', $index);
        $smarty->assign('total', $total);
        if (isset($menuProps)) {
            $smarty->assign($params['name'], $menuProps);
        }
        if (isset($key)) {
            $smarty->assign($key, $currentKey);
        }
        $smarty->_tag_stack[$menuTagStackKey][1]['_index'] = $index + 1;
        if (!is_null($content)) {
            $smarty->_tag_stack[$menuTagStackKey][1]['_content'][$lastKey] = $content;
        }
        if (empty($from) && is_null($content)) {
            $repeat = true;
        }
    } else {
        if (empty($from)) {
            $params['_content'] = $content;
            $result = _smarty_block_menu_parseheader($params);
        } else {
            $params['_content'][$lastKey] = $content;
            $result = _smarty_block_menu_parsemenu($params);
        }
        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $result);
        } else {
            return $result;
        }
    }

    return;
}

/**
 * Helper function to parse the menu
 *
 * @param array $params the parameter array passed to the plugin
 */
function _smarty_block_menu_parsemenu($params)
{
    if (isset($params['multilang'])) {
        $tmp = current($params['from']);
        $reflang = key($tmp);
    }

    $tree = [];
    $map  = [];

    foreach ($params['from'] as $i => $item) {
        if (isset($reflang)) {
            $item = $item[$reflang];
        }

        $item['content'] = $params['_content'][$i];

        if (!isset($item['id'])) {
            $item['id'] = 'dummy_'.$i;
        }

        $_node = [
            'item' => $item,
            'nodes' => []
        ];

        if (!isset($item['parentid']) || null === $item['parentid']) {
            $tree[$item['id']] = $_node;
            $path = null;
        } else {
            $path = $map[$item['parentid']];
            $path[] = $item['parentid'];
            $handle = &$tree;
            while (list($key, $value) = each($path)) {
                if (null === $value) {
                    continue;
                }
                $handle = &$handle[$value]['nodes'];
            }
            $handle[$item['id']] = $_node;
        }
        $map[$item['id']] = $path;
    }

    $listClass = isset($params['class']) ? $params['class'] : null;
    $listId    = isset($params['id']) ? $params['id'] : null;
    $listTag   = isset($params['tag']) ? $params['tag'] : 'ul';

    return _smarty_block_menu_parsemenu_html($tree, $listTag, $listClass, $listId);
}

/**
 * Helper function to build the html of the parsed menu
 *
 * @param array  $tree      the menu tree
 * @param string $listTag   the type if tag to wrap the menu
 * @param string $listClass the css class to apply to the list
 * @param string $listId    the css id to apply to the list
 */
function _smarty_block_menu_parsemenu_html($tree, $listTag, $listClass = null, $listId = null)
{
    $html  = '<'.$listTag;
    $html .= !empty($listId) ? ' id="'.$listId.'"' : '';
    $html .= !empty($listClass) ? ' class="'.$listClass.'"' : '';
    $html .= '>';

    foreach ($tree as $tab) {
        if (!empty($tab['nodes'])) {
            $subhtml = _smarty_block_menu_parsemenu_html($tab['nodes'], $listTag);
            $html .= preg_replace('/<\/li>\s*$/Di', $subhtml.'</li>', $tab['item']['content'], 1);
        } else {
            $html .= $tab['item']['content'];
        }
    }

    $html .= '</'.$listTag.'>';

    return $html;
}

/**
 * Helper function to parse the menu header
 *
 * @param array $params the parameter array passed to the plugin
 */
function _smarty_block_menu_parseheader($params)
{
    $listClass = isset($params['class']) ? $params['class'] : null;
    $listId    = isset($params['id']) ? $params['id'] : null;
    $listTag   = isset($params['tag']) ? $params['tag'] : 'ul';

    $html  = '<'.$listTag;
    $html .= !empty($listId) ? ' id="'.$listId.'"' : '';
    $html .= !empty($listClass) ? ' class="'.$listClass.'"' : '';
    $html .= '>';

    $html .= $params['_content'];

    $html .= '</'.$listTag.'>';

    return $html;
}
