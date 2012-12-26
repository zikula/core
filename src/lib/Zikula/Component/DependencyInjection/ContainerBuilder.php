<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_ServiceManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder as BaseContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * ContainerBuilder class.
 */
class ContainerBuilder extends BaseContainerBuilder implements \ArrayAccess
{
    /**
     * Setter for arguments property.
     *
     * @param array $array Array of id=>value.
     *
     * @return void
     */
    public function setArguments(array $array)
    {
        $this->getParameterBag()->clear();
        $this->getParameterBag()->add($array);
    }

    /**
     * Load multiple arguments.
     *
     * @param array $array Array of id=>$value.
     *
     * @return void
     */
    public function loadArguments(array $array)
    {
        foreach ($array as $id => $value) {
            $this->setParameter($id, $value);
        }
    }

    /**
     * Getter for \ArrayAccess interface.
     *
     * @param string $id Argument id.
     *
     * @return mixed Argument value.
     */
    public function offsetGet($id)
    {
        return $this->getParameter($id);
    }

    /**
     * Setter for \ArrayAccess interface.
     *
     * @param string $id    Argument id.
     * @param mixed  $value Argument value.
     *
     * @return void
     */
    public function offsetSet($id, $value)
    {
        $this->setParameter($id, $value);
    }

    /**
     * Has() method on argument property for \ArrayAccess interface.
     *
     * @param string $id Argument id.
     *
     * @return boolean
     */
    public function offsetExists($id)
    {
        return $this->hasParameter($id);
    }

    /**
     * Unset argument by id, implementation for \ArrayAccess.
     *
     * @param string $id Id.
     *
     * @return void
     */
    public function offsetUnset($id)
    {
        if ($this->hasParameter($id)) {
            if (method_exists($this->parameterBag, 'remove')) {
                $this->parameterBag->remove($id);
            } else {
                throw new \BadMethodCallException(
                    sprintf('No remove method in %s, unable to unset %s', get_class($this->parameterBag), $id)
                );
            }
        }
    }

    public function compile()
    {
        parent::compile();

        $this->parameterBag = new ParameterBag($this->parameterBag->all());
    }
}
