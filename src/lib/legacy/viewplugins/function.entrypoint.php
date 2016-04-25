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
 * Zikula_View function to return the entry point to the site as configured in the settings
 *
 * This function returns the value of the config var entrypoint
 *
 * Available parameters:
 *   - none
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The value of the config var entrypoint.
 */
function smarty_function_entrypoint($params, Zikula_View $view)
{
    return System::getVar('entrypoint', 'index.php');
}
