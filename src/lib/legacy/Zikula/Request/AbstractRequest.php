<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

/**
 * Request class.
 *
 * @deprecated
 */
abstract class Zikula_Request_AbstractRequest extends Request
{
    /**
     * Get current module name from the request stack.
     *
     * @deprecated since 1.4.0
     *
     * @return string
     */
    public function getModuleName()
    {
        return FormUtil::getPassedValue('module', '');
    }

    /**
     * Get current controller name from the request stack.
     *
     * @deprecated since 1.4.0
     *
     * @return string
     */
    public function getControllerName()
    {
        return FormUtil::getPassedValue('func', '');
    }

    /**
     * Get current action name from the request stack.
     *
     * @deprecated since 1.4.0
     *
     * @return string
     */
    public function getActionName()
    {
        return FormUtil::getPassedValue('type', '');
    }

    /**
     * Add request to the stack.
     *
     * @deprecated since 1.4.0
     *
     * @param string $module     Module controller name.
     * @param string $controller Controller type.
     * @param string $action     Action name.
     *
     * @return void
     */
    public function addRequest($module, $controller, $action)
    {
        $this->requests->append(array('module' => $module, 'controller' => $controller, 'action' => $action));
    }

    /**
     * Getter for request property.
     *
     * @deprecated since 1.4.0
     *
     * @return object ArrayIterator.
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Getter for args property.
     *
     * @deprecated since 1.4.0
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->attributes->all();
    }

    /**
     * Setter for args property.
     *
     * @param array $args Array of args.
     *
     * @deprecated since 1.4.0
     *
     * @return void
     */
    public function setArgs(array $args)
    {
        $this->attributes->$args;
    }

    /**
     * Get arg by key.
     *
     * @param string $key     Key to get.
     * @param string $default Default if not set.
     *
     * @deprecated since 1.4.0
     *
     * @return mixed
     */
    public function getArg($key, $default = null)
    {
        return $this->attributes->get($key, $default);
    }

    /**
     * Set single key in args property.
     *
     * @param string $key   Key.
     * @param mixed  $value Value.
     *
     * @deprecated since 1.4.0
     *
     * @return void
     */
    public function setArg($key, $value)
    {
        $this->attributes->set($key, $value);
    }

    /**
     * Check if args has $key.
     *
     * @param string $key Key.
     *
     * @deprecated since 1.4.0
     *
     * @return boolean
     */
    public function hasArg($key)
    {
        return $this->attributes->has($key);
    }

    /**
     * Unset $key from args property.
     *
     * @param string $key Key.
     *
     * @deprecated since 1.4.0
     *
     * @return void
     */
    public function unsetArg($key)
    {
        $this->attributes->remove($key);
    }
}
