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
 * Zikula_View function to get the language direction.
 *
 * Example
 * <html dir="{langdirection}">
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return   string   the language direction
 */
function smarty_function_langdirection($params, Zikula_View $view)
{
    return ZLanguage::getDirection();
}
