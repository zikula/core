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
 * Reference class.
 *
 * @deprecated since 1.4.0
 * @see \Symfony\Component\DependencyInjection\Reference
 */
class Zikula_ServiceManager_Reference extends \Symfony\Component\DependencyInjection\Reference
{
    /**
     * Get service ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->__toString();
    }
}
