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
 * Context menu seperator
 *
 * This plugin creates a seperator in a context menu.
 *
 * @param array            $params Parameters passed in the block tag
 * @param Zikula_Form_View $view   Reference to Form render object
 *
 * @return string The rendered output
 */
function smarty_function_formcontextmenuseparator($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_ContextMenu_Separator', $params);
}
