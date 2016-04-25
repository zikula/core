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

use ModUtil;
use System;
use Zikula\Core\Exception\FatalErrorException;

/**
 * Defines one valid authentication method.
 */
class AuthenticationMethodHelper extends \Zikula_AbstractHelper
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
     * @var string
     */
    protected $method;

    /**
     * Indicates whether this method is currently enabled for use or not.
     *
     * @var boolean
     */
    protected $enabledForAuthentication;

    /**
     * Indicates whether this method can be enabled for use as an authentication method for registration.
     *
     * This flag indicates the method's basic capability, and should not be changed through configuration.
     *
     * NOTE: The two core authentication methods, user name and email address should NOT set this flag!
     * This is for other modules that define authentication methods that can be used in the registration
     * process. THE TWO CORE METHODS ARE HANDLED DIFFERENTLY.
     *
     * @var boolean
     */
    protected $capableOfRegistration;

    /**
     * Indicates whether this method can be used as an authentication method for registration.
     *
     * This flag is controlled by configuration. To indicate whether the method is capable of being
     * used for registration in the first place, see $capableOfRegistration.
     *
     * NOTE: The two core authentication methods, user name and email address should NOT set this flag!
     * This is for other modules that define authentication methods that can be used in the registration
     * process. THE TWO CORE METHODS ARE HANDLED DIFFERENTLY.
     *
     * @var boolean
     */
    protected $enabledForRegistration;

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
     * Either a path to a picture from Zikula root or the name of a FontAwesome icon representing the authentication provider.
     *
     * @var string
     */
    protected $icon;

    /**
     * Whether or not to skip the login form fields page.
     *
     * @var bool
     */
    protected $skipLoginFormFieldsPage;

    /**
     * Construct an instance of the method definition.
     *
     * @param string  $modname               The name of the authentication module that defines the method.
     * @param string  $method                The name of the method.
     * @param string  $shortDescription      The brief description.
     * @param string  $longDescription       The more complete description.
     * @param boolean $capableOfRegistration True if the method is an external authentication method that can be used with the registration process; otherwise false.
     * @param boolean $skipLoginFormFields   Whether or not to skip the login form fields page, defaults to false.
     */
    public function __construct($modname, $method, $shortDescription, $longDescription, $capableOfRegistration = false, $icon = false, $skipLoginFormFields = false)
    {
        $this->setModule($modname);
        $this->setMethod($method);
        $this->setShortDescription($shortDescription);
        $this->setLongDescription($longDescription);
        $this->setIcon($icon);

        $this->skipLoginFormFieldsPage = $skipLoginFormFields;
        $this->enabledForAuthentication = true;
        $this->capableOfRegistration = (bool)$capableOfRegistration;
        $this->enabledForRegistration = $this->capableOfRegistration;
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
     * @throws \InvalidArgumentException Thrown if the module name is not valid.
     */
    private function setModule($modname)
    {
        $modname = trim($modname);
        if (System::isInstalling() && ($modname == 'ZikulaUsersModule')) {
            $this->modname = $modname;
        } elseif (!empty($modname) && is_string($modname) && ModUtil::available($modname, true) && ModUtil::isCapable($modname, 'authentication')) {
            $this->modname = $modname;
        } else {
            throw new \InvalidArgumentException($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
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
     * @throws \InvalidArgumentException Thrown if the name is not valid.
     */
    private function setMethod($method)
    {
        $method = trim($method);
        if (!empty($method) && is_string($method) && preg_match('/\\w+/', $method)) {
            $this->method = $method;
        } else {
            throw new \InvalidArgumentException($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
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
     * @throws \InvalidArgumentException Thrown if the description is invalid.
     */
    private function setShortDescription($shortDescription)
    {
        $shortDescription = trim($shortDescription);
        if (!empty($shortDescription) && is_string($shortDescription)) {
            $this->shortDescription = $shortDescription;
        } else {
            throw new \InvalidArgumentException($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
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
     * @throws \InvalidArgumentException Thrown if the description is invalid.
     */
    private function setLongDescription($longDescription)
    {
        $longDescription = trim($longDescription);
        if (!empty($longDescription) && is_string($longDescription)) {
            $this->longDescription = $longDescription;
        } else {
            throw new \InvalidArgumentException($this->__f('An invalid \'%1$s\' parameter was received (\'%2$s\').', array(
                'longDescription',
                empty($longDescription) ? 'NULL' : $longDescription)
            ));
        }
    }

    /**
     * Get the authentication method's icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the authentication method's icon.
     *
     * @param string $icon
     */
    private function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Check whether or not the icon is a FontAwesome icon.
     *
     * @return bool
     */
    public function isFontAwesomeIcon()
    {
        if (strpos($this->icon, '/') !== false || strpos($this->icon, 'fa-') !== 0 || empty($this->icon)) {
            return false;
        }

        return true;
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
     * Retrieve whether the defined method is capable of being enabled for use as an external registration method.
     *
     * @return boolean True if the method can be enabled for use with registration; otherwise false.
     */
    public function isCapableOfRegistration()
    {
        return $this->capableOfRegistration;
    }

    /**
     * Retrieve whether the defined method is currently enabled for use as an external registration method.
     *
     * @return boolean True if the method can be used for registration; otherwise false.
     */
    public function isEnabledForRegistration()
    {
        return $this->capableOfRegistration && $this->enabledForRegistration;
    }

    /**
     * Enable this authentication method for use in the registration process as an external registration method.
     *
     * @return void
     *
     * @throws FatalErrorException
     */
    public function enableForRegistration()
    {
        if ($this->capableOfRegistration) {
            $this->enabledForRegistration = true;
        } else {
            throw new FatalErrorException($this->__('The authentication method is not capable of being used for registration.'));
        }
    }

    /**
     * Disable this authentication method for use in the registration process as an external registration method.
     *
     * @return void
     */
    public function disableForRegistration()
    {
        $this->enabledForRegistration = false;
    }

    /**
     * Check whether or not the login form fields page should be skipped.
     *
     * @return bool True if the login form fields page should be skipped.
     */
    public function getSkipLoginFormFieldsPage()
    {
        return $this->skipLoginFormFieldsPage;
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
     * @throws FatalErrorException Thrown if the name is not valid.
     */
    public function __get($name)
    {
        switch ($name) {
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
            case 'capableOfRegistration':
            case 'capable_of_registration':
                return $this->isCapableOfRegistration();
                break;
            case 'enabledForRegistration':
            case 'enabled_for_registration':
                return $this->isEnabledForRegistration();
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
                throw new FatalErrorException($message);
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
     * @throws FatalErrorException Thrown if the name is not valid.
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'capableOfRegistration':
            case 'capable_of_registration':
            case 'enabledForRegistration':
            case 'enabled_for_registration':
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
                throw new FatalErrorException($message);
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
     * @throws FatalErrorException Thrown if the name is not valid, the property does not have a defined setter, or the property is not granted property setter access.
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'enabledForAuthentication':
            case 'enabled_for_authentication':
                if ($value) {
                    $this->enableForAuthentication();
                } else {
                    $this->disableForAuthentication();
                }
                break;
            case 'enabledForRegistration':
            case 'enabled_for_registration':
                if ($value) {
                    $this->enableForRegistration();
                } else {
                    $this->disableForRegistration();
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
                throw new FatalErrorException($message);
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
                throw new FatalErrorException($message);
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
     * @throws FatalErrorException Thrown if the name is not valid, the property does not have a defined setter, or the property is not granted property setter access.
     */
    public function __unset($name)
    {
        $trace = debug_backtrace();
        switch ($name) {
            case 'enabledForAuthentication':
            case 'enabled_for_authentication':
            case 'enabledForRegistration':
            case 'enabled_for_registration':
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
        throw new FatalErrorException($message);
    }
}
