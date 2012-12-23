<?php
/**
 * Copyright 2011 Zikula Foundation.
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
 * A list of authentication methods advertised by modules that have the authentication capability.
 */
class Users_Helper_AuthenticationMethodList extends Zikula_AbstractHelper implements ArrayAccess, Countable, Iterator
{
    /**
     * An internally maintained list of all authentication methods as gathered from modules advertising the 'authentication' capability.
     *
     * Each element of this array contains a map (an associative array) with the elements:
     *  * 'modname' - the name of the module providing the method
     *  * 'method'  - the identifier/name of the authentication method
     *
     * For any given authentication module, a method identifier/name must be unique, however method
     * identifiers/names can be repeated/reused by other authentication modules.
     *
     * @var array
     */
    private $authenticationMethods = array();

    /**
     * An index of the contents of {@link $authenticationMethods} organized by module name.
     *
     * @var array
     */
    private $nameIndex = array();

    /**
     * Used to order and filter the methods in this collection.
     *
     * @var array
     */
    private $orderedListableAuthenticationMethods = array();

    /**
     * The current pointer position of the iterator used to navigate through this collection.
     *
     * @var integer
     */
    private $iteratorPosition = 0;

    /**
     * Creates an instance of this collection, initializeing the list.
     *
     * @param Zikula_AbstractBase $base                                 The parent base for this collection.
     * @param array               $orderedListableAuthenticationMethods Used to order and filter the list.
     *
     * @throws Zikula_Exception_Fatal Thrown if a list of authentication modules cannot be obtained from ModUtil.
     */
    public function __construct(Zikula_AbstractBase $base, array $orderedListableAuthenticationMethods = array(), $filter = Zikula_Api_AbstractAuthentication::FILTER_NONE)
    {
        parent::__construct($base);

        $this->name = 'Users';

        $authenticationModules = ModUtil::getModulesCapableOf('authentication');
        if (!is_array($authenticationModules)) {
            throw new Zikula_Exception_Fatal($this->__('An invalid list of authentication modules was returned by ModUtil::getModulesCapableOf().'));
        }

        foreach ($authenticationModules as $modinfo) {
            $getAuthenticationMethodsArgs = array(
                'filter' => $filter,
            );
            $moduleAuthenticationMethods = ModUtil::apiFunc($modinfo['name'], 'Authentication', 'getAuthenticationMethods', $getAuthenticationMethodsArgs, 'Zikula_Api_AbstractAuthentication');
            if (is_array($moduleAuthenticationMethods) && !empty($moduleAuthenticationMethods)) {
                $this->authenticationMethods = array_merge($this->authenticationMethods, array_values($moduleAuthenticationMethods));
                $this->nameIndex[$modinfo['name']] = array();
            }
        }

        if (empty($this->authenticationMethods) && (($filter == Zikula_Api_AbstractAuthentication::FILTER_NONE) || ($filter == Zikula_Api_AbstractAuthentication::FILTER_ENABLED))) {
            LogUtil::log($this->__('There were no authentication methods available. Forcing the Users module to be used for authentication.'), Zikula_AbstractErrorHandler::CRIT);
            $this->authenticationMethods[] = new Users_Helper_AuthenticationMethod($this->name, 'uname', $this->__('User name'), $this->__('User name and password'));
            $this->nameIndex[$this->name] = array();
        }

        foreach ($this->authenticationMethods as $index => $authenticationMethod) {
            $this->nameIndex[$authenticationMethod->modname][$authenticationMethod->method] = &$this->authenticationMethods[$index];
        }

        if (!empty($orderedListableAuthenticationMethods)) {
            foreach ($orderedListableAuthenticationMethods as $authenticationMethodId) {
                if (isset($this->nameIndex[$authenticationMethodId['modname']])) {
                    if (isset($this->nameIndex[$authenticationMethodId['modname']][$authenticationMethodId['method']])) {
                        $this->orderedListableAuthenticationMethods[] = $this->nameIndex[$authenticationMethodId['modname']][$authenticationMethodId['method']];
                    } else {
                        LogUtil::log($this->__f('The authentication method \'%2$s\' is not a listable method for the module \'%1$s\'. It will be ignored.', array($authenticationMethod['modname'], $authenticationMethod['method'])), Zikula_AbstractErrorHandler::WARN);
                    }
                } else {
                    LogUtil::log($this->__f('The module \'%1$s\' is not a listable authentication module. All methods specified for it will be ignored.', array($authenticationMethod['modname'])), Zikula_AbstractErrorHandler::WARN);
                }
            }

            if (empty($this->orderedListableAuthenticationMethods)) {
                if (isset($this->nameIndex[$this->name])) {
                    $forcedMethod = array(
                        'modname'   => $this->name,
                        'method'    => array_shift(array_keys($this->nameIndex[$this->name])),
                    );
                } else {
                    $forcedMethod = $this->authenticationMethods[0];
                }

                $this->orderedListableAuthenticationMethods[] = $this->nameIndex[$forcedMethod['modname']][$forcedMethod['method']];
                LogUtil::log($this->__f('The set of listable authentication methods did not contain any methods that are currently available. Forcing the \'%2$s\' method defined by the \'%1$s\' module to be listable.', array($forcedMethod['modname'], $forcedMethod['method'])), Zikula_AbstractErrorHandler::WARN);
            }
        } else {
            foreach ($this->authenticationMethods as $index => $authenticationMethod) {
                $this->orderedListableAuthenticationMethods[] = $index;
            }
        }

        // Initialize Iterator
        $this->rewind();
    }

    /**
     * Determine the number of authentication methods within the collection that are enabled for use.
     *
     * @return integer The number of authentication methods in the collection that are enabled for use.
     */
    public function countEnabledForAuthentication()
    {
        $count = 0;
        foreach ($this->authenticationMethods as $authenticationMethod) {
            if ($authenticationMethod->isEnabledForAuthentication()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Determine the number of authentication methods within the collection that are enabled for use.
     *
     * @return integer The number of authentication methods in the collection that are enabled for use.
     */
    public function countEnabledForRegistration()
    {
        $count = 0;
        foreach ($this->authenticationMethods as $authenticationMethod) {
            if ($authenticationMethod->isEnabledForRegistration()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Determine whether a default authentication method is appropriate, and if it is, return it.
     *
     * A default is valid if there is only one enabled authentication method in the list.
     *
     * @return Users_Helper_AuthenticationMethod|void If a default authentication method is appropriate, then that definition; otherwise null.
     *
     * @throws Zikula_Exception_Fatal Thrown if the collection is in an inconsistent state.
     */
    public function getAuthenticationMethodForDefault()
    {
        // If there is more than one authentication method in the list, then no "default" is possible.
        $authenticationMethodForDefault = null;
        if ($this->countEnabledForAuthentication() <= 1) {
            // There is only one (or there is none), so select it.
            foreach ($this->authenticationMethods as $authenticationMethod) {
                if ($authenticationMethod->isEnabledForAuthentication()) {
                    $authenticationMethodForDefault = $authenticationMethod;
                    break;
                }
            }

            if (!$authenticationMethodForDefault) {
                // Nothing in the list at all! Because the constructor forces Users-uname if the list would otherwise be
                // empty this should not happen.
                throw new Zikula_Exception_Fatal($this->__('The authentication method list is in an inconsistent state. No authentication modules.'));
            }
        }

        return $authenticationMethodForDefault;
    }

    /**
     * Indicates whether the specified offset within the collection is occupied by an authentication method.
     *
     * @param integer $offset The offset position to query.
     *
     * @return boolean True if the offset is valid; otherwise false.
     *
     * @throws Zikula_Exception_Fatal Thrown if the offset is not valid.
     */
    public function offsetExists($offset)
    {
        if (is_numeric($offset)) {
            if ((int)$offset == $offset) {
                return isset($this->authenticationMethods[$offset]);
            } else {
                throw new Zikula_Exception_Fatal($this->__f('An invalid numeric offset was received (\'%1$s\').', array($offset)));
            }
        } elseif (is_string($offset)) {
            return isset($this->nameIndex[$offset]);
        } else {
            throw new Zikula_Exception_Fatal($this->__f('An invalid offset was received (\'%1$s\').', array($offset)));
        }
    }

    /**
     * Retrieves the authentication method definition at the specified offset in the collection.
     *
     * @param integer $offset The offset position to retrieve.
     *
     * @return Users_Helper_AuthenticationMethod $value  The authentication method definition at the specified offset.
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            if (is_numeric($offset)) {
                return $this->authenticationMethods[$offset];
            } elseif (is_string($offset)) {
                return $this->nameIndex[$offset];
            }
        }
    }

    /**
     * Sets the values of the record at the specified offset within the collection.
     *
     * @param integer                           $offset The offset position to set.
     * @param Users_Helper_AuthenticationMethod $value  The new Users_Helper_AuthenticationMethod value.
     *
     * @return void
     *
     * @throws Zikula_Exception_Fatal Always thrown; this function is not valid for this collection.
     */
    public function offsetSet($offset, $value)
    {
        throw new Zikula_Exception_Fatal($this->__f('Instances of $1$s are immutable.', array(__CLASS__)));
    }

    /**
     * Unsets the record at the specified offset within the collection.
     *
     * @param integer $offset The offset position to unset.
     *
     * @return void
     *
     * @throws Zikula_Exception_Fatal Always thrown; this function is not valid for this collection.
     */
    public function offsetUnset($offset)
    {
        throw new Zikula_Exception_Fatal($this->__f('Instances of $1$s are immutable.', array(__CLASS__)));
    }

    /**
     * Returns the number of records in the collection.
     *
     * @return integer The number of records in the collection.
     */
    public function count()
    {
        return count($this->orderedListableAuthenticationMethods);
    }

    /**
     * Resets the current iterator position to the beginning of the collection.
     *
     * @return void
     */
    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    /**
     * Retrieves the record pointed to by the current iterator position.
     *
     * @return Users_Helper_AuthenticationMethod The authentication method at the current iterator position.
     */
    public function current()
    {
        $copy = $this->authenticationMethods[$this->orderedListableAuthenticationMethods[$this->iteratorPosition]];

        return $copy;
    }

    /**
     * Retrives the key value of the current iterator position.
     *
     * @return integer The current iterator position.
     */
    public function key()
    {
        return $this->iteratorPosition;
    }

    /**
     * Moves the iterator position to the next record.
     *
     * @return void
     */
    public function next()
    {
        $this->iteratorPosition++;
    }

    /**
     * Indicates whether the current iterator position is valid or not.
     *
     * Iterator interface implementation.
     *
     * @return boolean True if the current iterator position points to a valid record; otherwise false.
     */
    public function valid()
    {
        return isset($this->orderedListableAuthenticationMethods[$this->iteratorPosition])
                && isset($this->authenticationMethods[$this->orderedListableAuthenticationMethods[$this->iteratorPosition]]);
    }
}
