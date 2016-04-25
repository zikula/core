<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Core\UrlInterface;

/**
 * DisplayHook class.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\DisplayHook
 */
class Zikula_DisplayHook extends Zikula\Bundle\HookBundle\Hook\DisplayHook
{
    public function __construct($name, $id, UrlInterface $url = null)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', array(__CLASS__), E_USER_DEPRECATED));
        $this->setName($name);
        parent::__construct($id, $url);
    }
}
