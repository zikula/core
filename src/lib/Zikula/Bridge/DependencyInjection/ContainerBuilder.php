<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bridge\DependencyInjection;

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
     * @param array $array Array of id=>value
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
     * @param array $array Array of id=>$value
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
     * @param string $id Argument id
     *
     * @return mixed Argument value
     */
    public function offsetGet($id)
    {
        return $this->getParameter($id);
    }

    /**
     * Setter for \ArrayAccess interface.
     *
     * @param string $id    Argument id
     * @param mixed  $value Argument value
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
     * @param string $id Argument id
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
     * @param string $id Id
     *
     * @return void
     */
    public function offsetUnset($id)
    {
        if (!$this->hasParameter($id)) {
            return;
        }

        if (method_exists($this->parameterBag, 'remove')) {
            $this->parameterBag->remove($id);
        } else {
            throw new \BadMethodCallException(
                sprintf('No remove method in %s, unable to unset %s', get_class($this->parameterBag), $id)
            );
        }
    }

    public function compile()
    {
        parent::compile();

        $this->parameterBag = new ParameterBag($this->parameterBag->all());
    }
}
