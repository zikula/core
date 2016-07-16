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
 * Zikula_View modifier to parse gettext string.
 *
 * Example
 *
 *   {$var|gt:$zikula_view}
 *
 * @param string      $string The contents to transform
 * @param Zikula_View $view   This Zikula_View object (available as $renderObject in templates)
 *
 * @return string The modified output
 */
function smarty_modifier_gt($string, $view)
{
    if (!$view instanceof Zikula_View) {
        return __('Error! With modifier_gt, you must use the following form for the gettext modifier (\'gt\'): $var|gt:$zikula_view');
    }

    return __($string, $view->getDomain());
}
