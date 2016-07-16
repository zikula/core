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
 * Category selector
 *
 * This plugin creates a category selector using a dropdown list.
 * The selected value of the base dropdown list will be set to ID of the selected category.
 *
 * @param array            $params Parameters passed in the block tag
 * @param Zikula_Form_View $view   Reference to Form render object
 *
 * @return string The rendered output
 */
function smarty_function_formcategoryselector($params, $view)
{
    return $view->registerPlugin('Zikula_Form_Plugin_CategorySelector', $params);
}
