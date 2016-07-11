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
 * Insert a CSRF protection nonce.
 *
 * Available parameters:
 *   - assign: Assign rather the output.
 *
 * Example:
 * <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_insert_csrftoken($params, $view)
{
    // NOTE: assign parameter is handled by the smarty_core_run_insert_handler(...) function in lib/vendor/Smarty/internals/core.run_insert_handler.php
    return SecurityUtil::generateCsrfToken($view->getContainer());
}
