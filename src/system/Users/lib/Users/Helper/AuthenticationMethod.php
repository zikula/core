<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Defines one valid authentication method.
 */
class Users_Helper_AuthenticationMethod extends Zikula_AbstractHelper
{
    /**
     * The authentication module name that defines the method.
     *
     * @var string
     */
    protected $modname;

    /**
     * The name of the defined authentication method.
     *
     * @var type 
     */
    protected $method;

    /**
     * Indicates whether this method is currently enabled for use or not.
     *
     * @var boolean
     */
    protected $enabledForAuthentication;

    /**
     * A brief description of the method.
     *
     * @var string
     */
    protected $shortDescription;

    /**
     * A more complete description of the method.
     *
     * @var string
     */
    protected $longDescription;

    /**
     * Construct an instance of the method definition.
     *
     * @param string $modname          The name of the authentication module that defines the method.
     * @param string $method           The name of the method.
     * @param string $shortDescription The brief description.
     * @param string $longDescription  The more complete description.
     */
    public function __construct($modname, $method, $shortDescription, $longDescription)
    {
        $this->setModule($modname);
        $this->setMethod($method);
        $this->setShortDescription($shortDescription);
        $this->setLongDescription($longDescription);

        $this->enabledForAuthentication = true;
    }

    /**
     * Retrieve the authentication module name for the defined method.
     *
     * @return string The authentication module name defining the method.
     */
    public function getModule()
    {
        return $this->modname;
    }

    /**
     * Sets the authentication module name for this method.
     *
     * @param string $modname The name of the authentication module that defines this method.
     * 
     * @return void
     * 
     * @throws Zikula_Exception_Fatal Thrown if the module name is not valid.
     */
    private function setModule($modname)
    {
        $modname = trim($modname);
        if (!empty($modname) && is_string($modname) && ModUtil::available($modname) && ModUtil::isCapable($modname, 'authentication')) {
            $this->modname = $modname;
        } else {
            throw new Zikula_Exception_Fatal($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
                'modname',
                empty($modname) ? 'NULL' : $modname)
            ));
        }
    }

    /**
     * Retrieve the authentication method name.
     *
     * @return string The name of the authentication method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the name of the defined authentication method.
     *
     * @param string $method The name of the authentication method.
     * 
     * @return void
     * 
     * @throws Zikula_Exception_Fatal Thrown if the name is not valid.
     */
    private function setMethod($method)
    {
        $method = trim($method);
        if (!empty($method) && is_string($method) && preg_match('/\\w+/', $method)) {
            $this->method = $method;
        } else {
            throw new Zikula_Exception_Fatal($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
                'method',
                empty($method) ? 'NULL' : $method)
            ));
        }
    }

    /**
     * Retrieve the brief description of the authentication method.
     *
     * @return string The method's brief description.
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set the brief description for the defined authentication method.
     *
     * @param string $shortDescription The method's brief descrption.
     * 
     * @return void
     * 
     * @throws Zikula_Exception_Fatal Thrown if the description is invalid.
     */
    private function setShortDescription($shortDescription)
    {
        $shortDescription = trim($shortDescription);
        if (!empty($shortDescription) && is_string($shortDescription)) {
            $this->shortDescription = $shortDescription;
        } else {
            throw new Zikula_Exception_Fatal($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
                'shortDescription',
                empty($shortDescription) ? 'NULL' : $shortDescription)
            ));
        }
    }

    /**
     * Retrieve the more complete description of the authentication method.
     *
     * @return string The method's more complete description.
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * Set the more complete description for the defined authentication method.
     *
     * @param string $longDescription The method's more complete descrption.
     * 
     * @return void
     * 
     * @throws Zikula_Exception_Fatal Thrown if the description is invalid.
     */
    private function setLongDescription($longDescription)
    {
        $longDescription = trim($longDescription);
        if (!empty($longDescription) && is_string($longDescription)) {
            $this->longDescription = $longDescription;
        } else {
            throw new Zikula_Exception_Fatal($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
                'longDescription',
                empty($longDescription) ? 'NULL' : $longDescription)
            ));
        }
    }

    /**
     * Retrieve whether the defined method is currently enabled for use.
     *
     * @return boolean True if the method can be used for authentication; otherwise false.
     */
    public function isEnabledForAuthentication()
    {
        return $this->enabledForAuthentication;
    }

    /**
     * Enable this authentication method for use.
     * 
     * @return void
     */
    public function enableForAuthentication()
    {
        $this->enabledForAuthentication = true;
    }

    /**
     * Disable this authentication method for use.
     * 
     * @return void
     */
    public function disableForAuthentication()
    {
        $this->enabledForAuthentication = false;
    }

    /**
     * Provides internal attribute access for protected and private properties that have defined accessors.
     * 
     * Some aliases are also recognized for certain properties.
     *
     * @param string $name The property name.
     * 
     * @return mixed The value of the specified property.
     * 
     * @throws Zikula_Exception_Fatal Thrown if the name is not valid.
     */
    public function __get($name)
    {
        switch($name) {
            case 'modname':
            case 'module':
                return $this->getModule();
                break;
            case 'method':
                return $this->getMethod();
                break;
            case 'shortDescription':
            case 'short_description':
                return $this->getShortDescription();
                break;
            case 'longDescription':
            case 'long_description':
                return $this->getLongDescription();
                break;
            case 'enabledForAuthentication':
            case 'enabled_for_authentication':
                return $this->isEnabledForAuthentication();
                break;
            default:
                $trace = debug_backtrace();
                // NO I18N for $function!
                $message = $this->__f('Attempt to retrieve undefined property via %1$s: %2$s in $3$s on line %4$d.', array(
                    '__get()',
                    (string)$name,
                    $trace[0]['file'],
                    (int)$trace[0]['line'],
                ));
                throw new Zikula_Exception_Fatal($message);
                break;
        }
    }

    /**
     * Enable internal isset attribute access for a subset of properties.
     * 
     * Certain aliases are also recognized.
     *
     * @param string $name The name of the property.
     * 
     * @return boolean True if the field is not null; otherwise false.
     * 
     * @throws Zikula_Exception_Fatal Thrown if the name is not valid.
     */
    public function __isset($name)
    {
        switch($name) {
            case 'enabledForAuthentication':
            case 'enabled_for_authentication':
            case 'modname':
            case 'module':
            case 'method':
            case 'shortDescription':
            case 'short_description':
            case 'longDescription':
            case 'long_description':
                return true;
                break;
            default:
                $trace = debug_backtrace();
                // NO I18N for $function!
                $message = $this->__f('Attempt to determine if undefined property is set via %1$s: %2$s in $3$s on line %4$d.', array(
                    '__isset()',
                    (string)$name,
                    $trace[0]['file'],
                    (int)$trace[0]['line'],
                ));
                throw new Zikula_Exception_Fatal($message);
                break;
        }
    }

    /**
     * Enable internal attribute setter access for a subset of properties.
     * 
     * Certain aliases are also recognized.
     *
     * @param string $name  The name of the property.
     * @param mixed  $value The value to set.
     * 
     * @return void
     * 
     * @throws Zikula_Exception_Fatal Thrown if the name is not valid, the property does not have a defined setter, or the property is not granted property setter access.
     */
    public function __set($name, $value)
    {
        switch($name) {
            case 'enabledForAuthentication':
            case 'enabled_for_authentication':
                if ($value) {
                    $this->enableForAuthentication();
                } else {
                    $this->disableForAuthentication();
                }
                break;
            case 'modname':
            case 'module':
            case 'method':
            case 'shortDescription':
            case 'short_description':
            case 'longDescription':
            case 'long_description':
                $trace = debug_backtrace();
                // NO I18N for $function!
                $message = $this->__f('Attempt to modify immutable property via %1$s: %2$s in $3$s on line %4$d.', array(
                    '__set()',
                    (string)$name,
                    $trace[0]['file'],
                    (int)$trace[0]['line'],
                ));
                throw new Zikula_Exception_Fatal($message);
                break;
            default:
                $trace = debug_backtrace();
                // NO I18N for $function!
                $message = $this->__f('Attempt to modify undefined property via %1$s: %2$s in $3$s on line %4$d.', array(
                    '__set()',
                    (string)$name,
                    $trace[0]['file'],
                    (int)$trace[0]['line'],
                ));
                throw new Zikula_Exception_Fatal($message);
                break;
        }
    }

    /**
     * Enable internal attribute setter access for a subset of properties.
     * 
     * Certain aliases are also recognized.
     *
     * @param string $name The name of the property.
     * 
     * @return void
     * 
     * @throws Zikula_Exception_Fatal Thrown if the name is not valid, the property does not have a defined setter, or the property is not granted property setter access.
     */
    public function __unset($name)
    {
        $trace = debug_backtrace();
        switch($name) {
            case 'enabledForAuthentication':
            case 'enabled_for_authentication':
                // NO I18N for $function!
                $message = $this->__f('Attempt to unset required property via %1$s: %2$s in $3$s on line %4$d.', array(
                    '__unset()',
                    (string)$name,
                    $trace[0]['file'],
                    (int)$trace[0]['line'],
                ));
                break;
            case 'modname':
            case 'module':
            case 'method':
            case 'shortDescription':
            case 'short_description':
            case 'longDescription':
            case 'long_description':
                // NO I18N for $function!
                $message = $this->__f('Attempt to unset immutable property via %1$s: %2$s in $3$s on line %4$d.', array(
                    '__unset()',
                    (string)$name,
                    $trace[0]['file'],
                    (int)$trace[0]['line'],
                ));
                break;
            default:
                // NO I18N for $function!
                $message = $this->__f('Attempt to unset undefined property via %1$s: %2$s in $3$s on line %4$d.', array(
                    '__unset()',
                    (string)$name,
                    $trace[0]['file'],
                    (int)$trace[0]['line'],
                ));
                break;
        }
        throw new Zikula_Exception_Fatal($message);
    }
}