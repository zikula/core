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
 * Zikula display hook response class.
 *
 * Hook handlers should return one of these.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\DisplayHookResponse
 */
class Zikula_Response_DisplayHook extends Zikula\Bundle\HookBundle\Hook\DisplayHookResponse
{
    public function __construct($area, Zikula_View $view, $template)
    {
        @trigger_error('Old hook class is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Class %s is deprecated.', [__CLASS__], E_USER_DEPRECATED));
        $response = $view->fetch($template);
        parent::__construct($area, $response);
    }
}
