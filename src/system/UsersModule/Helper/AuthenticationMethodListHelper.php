<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Symfony\Bridge\Monolog\Logger;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;

/**
 * A list of authentication methods advertised by modules that have the authentication capability.
 */
class AuthenticationMethodListHelper implements \ArrayAccess, \Countable, \Iterator
{
    use TranslatorTrait;
    private $initialized = false;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var CapabilityApiInterface
     */
    private $capabilityApi;

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
    private $authenticationMethods = [];

    /**
     * An index of the contents of {@link $authenticationMethods} organized by module name.
     *
     * @var array
     */
    private $nameIndex = [];

    /**
     * Used to order and filter the methods in this collection.
     *
     * @var array
     */
    private $orderedListableAuthenticationMethods = [];

    /**
     * The current pointer position of the iterator used to navigate through this collection.
     *
     * @var integer
     */
    private $iteratorPosition = 0;

    /**
     * AuthenticationMethodListHelper constructor.
     * @param Logger $logger
     * @param CapabilityApiInterface $capabilityApi
     * @param TranslatorInterface $translator
     */
    public function __construct(Logger $logger, CapabilityApiInterface $capabilityApi, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->capabilityApi = $capabilityApi;
        $this->setTranslator($translator);
    }

    /**
     * initializing the list.
     *
     * @param array                $orderedListableAuthenticationMethods Used to order and filter the list.
     * @param int                  $filter                               Filter to apply when getting methods
     *
     * @throws FatalErrorException Thrown if a list of authentication modules cannot be obtained from CapabilityApiInterface.
     */
    public function initialize(array $orderedListableAuthenticationMethods = [], $filter = \Zikula_Api_AbstractAuthentication::FILTER_NONE)
    {
        if ($this->initialized) {
            return;
        }
        $authenticationModules = $this->capabilityApi->getExtensionsCapableOf(CapabilityApiInterface::AUTHENTICATION);
        if (!is_array($authenticationModules)) {
            throw new FatalErrorException($this->__('An invalid list of authentication modules was returned by CapabilityApiInterface::getExtensionsCapableOf().'));
        }

        foreach ($authenticationModules as $modinfo) {
            $getAuthenticationMethodsArgs = array(
                'filter' => $filter,
            );
            $moduleAuthenticationMethods = \ModUtil::apiFunc($modinfo['name'], 'Authentication', 'getAuthenticationMethods', $getAuthenticationMethodsArgs, 'Zikula_Api_AbstractAuthentication');
            if (is_array($moduleAuthenticationMethods) && !empty($moduleAuthenticationMethods)) {
                $this->authenticationMethods = array_merge($this->authenticationMethods, array_values($moduleAuthenticationMethods));
                $this->nameIndex[$modinfo['name']] = [];
            }
        }

        if (empty($this->authenticationMethods) && (($filter == \Zikula_Api_AbstractAuthentication::FILTER_NONE) || ($filter == \Zikula_Api_AbstractAuthentication::FILTER_ENABLED))) {
            $this->logger->addWarning($this->__('There were no authentication methods available. Forcing the Users module to be used for authentication.'));
            $this->authenticationMethods[] = new AuthenticationMethodHelper('ZikulaUsersModule', 'uname', $this->__('User name'), $this->__('User name and password'));
            $this->nameIndex['ZikulaUsersModule'] = [];
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
                        $this->logger->addWarning($this->__f('The authentication method \'%2$s\' is not a listable method for the module \'%1$s\'. It will be ignored.', array('%1$s' => $authenticationMethod['modname'], '%2$s' => $authenticationMethod['method'])));
                    }
                } else {
                    $this->logger->addWarning($this->__f('The module \'%1$s\' is not a listable authentication module. All methods specified for it will be ignored.', array('%1$s' => $authenticationMethod['modname'])));
                }
            }

            if (empty($this->orderedListableAuthenticationMethods)) {
                if (isset($this->nameIndex['ZikulaUsersModule'])) {
                    $forcedMethod = array(
                        'modname'   => 'ZikulaUsersModule',
                        'method'    => array_shift(array_keys($this->nameIndex['ZikulaUsersModule'])),
                    );
                } else {
                    $forcedMethod = $this->authenticationMethods[0];
                }

                $this->orderedListableAuthenticationMethods[] = $this->nameIndex[$forcedMethod['modname']][$forcedMethod['method']];
                $this->logger->addWarning($this->__f('The set of listable authentication methods did not contain any methods that are currently available. Forcing the \'%2$s\' method defined by the \'%1$s\' module to be listable.', array('%1$s' => $forcedMethod['modname'], '%2$s' => $forcedMethod['method'])));
            }
        } else {
            foreach ($this->authenticationMethods as $index => $authenticationMethod) {
                $this->orderedListableAuthenticationMethods[] = $index;
            }
        }

        // Initialize Iterator
        $this->rewind();
        $this->initialized = true;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
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
     * @return AuthenticationMethodHelper|void If a default authentication method is appropriate, then that definition; otherwise null.
     *
     * @throws FatalErrorException Thrown if the collection is in an inconsistent state.
     */
    public function getAuthenticationMethodForDefault()
    {
        // If there is more than one authentication method in the list, then first is selected.
        $authenticationMethodForDefault = null;
        foreach ($this->authenticationMethods as $authenticationMethod) {
            if ($authenticationMethod->isEnabledForAuthentication()) {
                $authenticationMethodForDefault = $authenticationMethod;
                break;
            }
        }

        if (!$authenticationMethodForDefault) {
            // Nothing in the list at all! Because the constructor forces Users-uname if the list would otherwise be
            // empty this should not happen.
            throw new FatalErrorException($this->__('The authentication method list is in an inconsistent state. No authentication modules.'));
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
     * @throws FatalErrorException Thrown if the offset is not valid.
     */
    public function offsetExists($offset)
    {
        if (is_numeric($offset)) {
            if ((int)$offset == $offset) {
                return isset($this->authenticationMethods[$offset]);
            } else {
                throw new FatalErrorException($this->__f('An invalid numeric offset was received (\'%s\').', array('%s' => $offset)));
            }
        } elseif (is_string($offset)) {
            return isset($this->nameIndex[$offset]);
        } else {
            throw new FatalErrorException($this->__f('An invalid offset was received (\'%s\').', array('%s' => $offset)));
        }
    }

    /**
     * Retrieves the authentication method definition at the specified offset in the collection.
     *
     * @param integer $offset The offset position to retrieve.
     *
     * @return AuthenticationMethodHelper $value  The authentication method definition at the specified offset.
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
     * @param AuthenticationMethodHelper $value  The new Users_Helper_AuthenticationMethod value.
     *
     * @return void
     *
     * @throws FatalErrorException Always Thrown; this function is not valid for this collection.
     */
    public function offsetSet($offset, $value)
    {
        throw new FatalErrorException($this->__f('Instances of %s are immutable.', array('%s' => __CLASS__)));
    }

    /**
     * Unsets the record at the specified offset within the collection.
     *
     * @param integer $offset The offset position to unset.
     *
     * @return void
     *
     * @throws FatalErrorException Always Thrown; this function is not valid for this collection.
     */
    public function offsetUnset($offset)
    {
        throw new FatalErrorException($this->__f('Instances of %s are immutable.', array('%s' => __CLASS__)));
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
     * @return AuthenticationMethodHelper The authentication method at the current iterator position.
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
