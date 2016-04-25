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
 * Zikula_ServiceManager_Argument container class.
 *
 * This class contains an argument id which references a stored parameter.
 *
 * @deprecated since 1.4.0
 * @see \Symfony\Component\DependencyInjection\Parameter
 */
class Zikula_ServiceManager_Argument extends \Symfony\Component\DependencyInjection\Parameter
{
    /**
     * Get id property.
     *
     * @return string Argument id.
     */
    public function getId()
    {
        return $this->__toString();
    }
}
