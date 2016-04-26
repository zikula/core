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
 * Retrieve and display the site's charset.
 *
 * Available attributes:
 *  - assign    (string)    the name of a template variable to assign the
 *                          output to, instead of returning it to the template. (optional)
 *
 * Example:
 *
 * <samp><meta http-equiv="Content-Type" content="text/html; charset={charset}"></samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string The value of the charset.
 */
function smarty_function_charset($params, Zikula_View $view)
{
    $return = ZLanguage::getEncoding();

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
