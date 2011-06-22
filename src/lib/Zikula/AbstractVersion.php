<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula Version base class.
 */
abstract class Zikula_AbstractVersion implements ArrayAccess
{
    /**
     * The module name, computed from the implementing class name.
     *
     * @var string
     */
    protected $name;

    /**
     * The display name for the module.
     *
     * @var string
     */
    protected $displayname;

    /**
     * The string used in the URL to access the module.
     *
     * @var string
     */
    protected $url;

    /**
     * A brief description of the module.
     *
     * @var string
     */
    protected $description;

    /**
     * The module version number.
     *
     * NOTE: It is highly recommended to use version numbers compatible with {@link version_compare()}.
     *
     * @var string
     */
    protected $version = 0;

    /**
     * An array of security schemas used by the module.
     *
     * Displayed by the Permissions module to assist the user, example:
     * <code>
     * array(
     *     'ModName::'          => 'ItemName::',
     *     'ModName::Component' => '::',
     * );
     * </code>
     *
     * @var array
     */
    protected $securityschema = array();

    /**
     * Module dependencies.
     *
     * @var array
     */
    protected $dependencies = array();

    /**
     * Module capabilities.
     *
     * @var array
     */
    protected $capabilities = array();

    /**
     * Gettext language domain, computed from {@link ZLanguage::getModuleDomain()}.
     *
     * @var string
     */
    protected $domain;

    /**
     * The module type, computed from the module's base directory.
     *
     * Values:
     * <ul>
     *   <li>ModUtil::TYPE_MODULE</li>
     *   <li>ModUtil::TYPE_SYSTEM</li>
     * </ul>
     *
     * @var integer
     */
    protected $type;

    /**
     * The state of the module, set when the Modules module regenerates its list.
     *
     * @var integer
     */
    protected $state;

    /**
     * For legacy handling, an alias to the module {@link $name name}.
     *
     * @var string
     */
    protected $directory;

    /**
     * The minimum core version supported by the module.
     *
     * @var string
     */
    protected $core_min = '';

    /**
     * The maximum core version supported by the module.
     *
     * @var string
     */
    protected $core_max = '';

    /**
     * A list of names this module used to be known as.
     *
     * @var array
     */
    protected $oldnames;

    /**
     * The base directory for the module, computed.
     *
     * @var string
     */
    protected $baseDir;

    /**
     * The base directory for this module's libraries, computed.
     *
     * @var string
     */
    protected $libBaseDir;

    /**
     * The system base directory, computed.
     *
     * @var string
     */
    protected $systemBaseDir;

    /**
     * A {@link ReflectionObject} instance for this instance of the class.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Hook subscriber bundles.
     *
     * @var array Indexed array of Zikula_Version_HookSubscriberBundle
     */
    protected $hookSubscriberBundles = array();

    /**
     * Hook provider bundles.
     *
     * @var array Indexed array of Zikula_Version_HookProviderBundle
     */
    protected $hookProviderBundles = array();

    //abstract public function getMetaData();

    /**
     * Build a new instance.
     */
    public function __construct()
    {
        $this->systemBaseDir = realpath('.');
        $this->reflection = new ReflectionObject($this);
        $p = explode('_', get_class($this));
        $this->name = $p[0];
        $this->directory = $this->name; // legacy handling
        $this->baseDir = realpath(dirname($this->reflection->getFileName()).'/../..');
        $this->libBaseDir = realpath($this->baseDir . '/lib/' . $this->name);
        $this->type = ModUtil::getModuleBaseDir($this->name) == 'system' ? ModUtil::TYPE_SYSTEM : ModUtil::TYPE_MODULE;
        if ($this->type == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }
        Zikula_ClassProperties::load($this, $this->getMetaData());

        // Load configuration of any hook bundles.
        $this->setupHookBundles();
    }

    /**
     * Return certain data elements from this class as an array.
     *
     * @return array An array containing the name, description, display name, url, version, capabilities, dependencies,
     *                  type, directory and security schema.
     */
    public function toArray()
    {
        $meta = array();
        $meta['name'] = $this->name;
        $meta['description'] = $this->description;
        $meta['displayname'] = $this->displayname;
        $meta['url'] = $this->url;
        $meta['version'] = $this->version;
        $meta['capabilities'] = $this->capabilities;
        $meta['dependencies'] = $this->dependencies;
        $meta['type'] = $this->type;
        $meta['directory'] = $this->directory;
        $meta['securityschema'] = $this->securityschema;
        $meta['core_min'] = $this->core_min;
        $meta['core_max'] = $this->core_max;
        return $meta;
    }

    /**
     * Translate a string using gettext.
     *
     * @param string $msgid The string to translate.
     *
     * @return string The translated string.
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    /**
     * Translate a string using the sprintf version of gettext.
     *
     * @param string $msgid  The string to translate.
     * @param array  $params The parameters to substitute into the string.
     *
     * @return string The translated string with variables substituted.
     */
    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    /**
     * Retrieve the module base directory.
     *
     * @return string The directory.
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * Return the base directory for the module's libraries.
     *
     * @return string The directory.
     */
    public function getLibBaseDir()
    {
        return $this->libBaseDir;
    }

    /**
     * Return the system base directory.
     *
     * @return string The directory.
     */
    public function getSystemBaseDir()
    {
        return $this->systemBaseDir;
    }

    /**
     * Retrieve the module name.
     *
     * @return string The name of the module.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retrieve the display name for the module.
     *
     * @return string The display name.
     */
    public function getDisplayName()
    {
        return $this->displayname;
    }

    /**
     * Set the module's display name.
     *
     * @param string $displayName The display name.
     *
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->displayname = $displayName;
    }

    /**
     * Return the string used in the module's URL.
     *
     * @return string The URL fragment.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the string used in URLs to access the module.
     *
     * @param string $url The URL fragment.
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Retrieve the module's brief description.
     *
     * @return string The description.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set a brief description of the module.
     *
     * @param string $description The description.
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Retrieve the module's version number.
     *
     * @return string The version.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the module's version.
     *
     * @param string $version The version string, in the format a.b.c where a, b and c are digit sequences.
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if $version does not match the regular expression #\d+\.\d+\.\d+#.
     */
    public function setVersion($version)
    {
        if (!preg_match('#\d+\.\d+\.\d+#', $version)) {
            throw new InvalidArgumentException($this->__f('Version numbers must be in the format "a.b.c" in class %s', get_class($this)));
        }
        $this->version = $version;
    }

    /**
     * Retrieve the security schema.
     *
     * @return array The security schema.
     */
    public function getSecuritySchema()
    {
        return $this->securityschema;
    }

    /**
     * Set the security schema.
     *
     * Example:
     * <code>
     * $moduleVersion->setSecuritySchema(array(
     *     'ModName::'          => 'ItemName::',
     *     'ModName::Component' => '::',
     * ));
     * </code>
     *
     * @param array $securitySchema The schema.
     *
     * @return void
     */
    public function setSecuritySchema($securitySchema)
    {
        $this->securityschema = $securitySchema;
    }

    /**
     * Retrieve the module dependencies.
     *
     * @return array The dependency array.
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Set the dependencies on other modules.
     *
     * @param array $dependencies Teh dependencies.
     *
     * @return void
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Retrieve the module's advertised capabilities.
     *
     * @return array The capabilities of the module.
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * Set the module's capabilities.
     *
     * The capabilities array is in the form: array(capability => array(version => capabilityVersion ...
     *
     * Example:
     * <code>
     * $capabilities = array(
     *     'authentication' => array('version' => '1.0')
     * );
     * $moduleVersion->setCapabilities($capabilities);
     * </code>
     *
     * @param array $capabilities The module's advertised capabilities.
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if $capabilities does not conform to the specified structure.
     */
    public function setCapabilities($capabilities)
    {
        if (!is_array($capabilities) || !$capabilities) {
            throw new InvalidArgumentException(__f('Capabilities properties must be an array in the form array(capability => array(version => string, ... => ... in %s', get_class($this)));
        }
        foreach ($capabilities as $key => $capability) {
            if (is_integer($key) || !is_array($capability) || !$capability) {
                throw new InvalidArgumentException(__f('Capabilities properties must be an array in the form array(capability => array(version => string, ... => ... in %s', get_class($this)));
            }
            foreach ($capability as $capkey => $cap) {
                if (is_integer($capkey)) {
                    throw new InvalidArgumentException(__f('Capabilities properties must be an array in the form array(capability => array(version => string, ... => ... in %s', get_class($this)));
                }
            }
        }
        $this->capabilities = $capabilities;
    }

    /**
     * Retrieve the minimum core version string.
     *
     * @return string The minimum acceptable core version.
     */
    public function getCore_min()
    {
        return $this->core_min;
    }

    /**
     * Set the minimum acceptable core version for this module version.
     *
     * @param string $core_min The minimum version.
     *
     * @return void
     */
    public function setCore_min($core_min)
    {
        $this->core_min = $core_min;
    }

    /**
     * Retrieve the highest core version this module version will operate with.
     *
     * @return string The highest acceptable core version.
     */
    public function getCore_max()
    {
        return $this->core_max;
    }

    /**
     * Set the maximum acceptable core version with which this module version will operate.
     *
     * @param string $core_max The maximum core version.
     *
     * @return void
     */
    public function setCore_max($core_max)
    {
        $this->core_max = $core_max;
    }

    /**
     * Retrieve this module's prior names.
     *
     * @return array The module's former names.
     */
    public function getOldnames()
    {
        return $this->oldnames;
    }

    /**
     * Set the list of names this module was once known as.
     *
     * @param array $oldnames The former names.
     *
     * @return void
     */
    public function setOldnames($oldnames)
    {
        $this->oldnames = $oldnames;
    }

    /**
     * Retrieve the module's state.
     *
     * Values:
     * <ul>
     *   <li>ModUtil::STATE_UNINITIALISED</li>
     *   <li>ModUtil::STATE_INACTIVE</li>
     *   <li>ModUtil::STATE_ACTIVE</li>
     *   <li>ModUtil::STATE_MISSING</li>
     *   <li>ModUtil::STATE_UPGRADED</li>
     *   <li>ModUtil::STATE_NOTALLOWED</li>
     *   <li>ModUtil::STATE_INVALID</li>
     * </ul>
     *
     * @return integer The state of the module.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the module's state.
     *
     * Values:
     * <ul>
     *   <li>ModUtil::STATE_UNINITIALISED</li>
     *   <li>ModUtil::STATE_INACTIVE</li>
     *   <li>ModUtil::STATE_ACTIVE</li>
     *   <li>ModUtil::STATE_MISSING</li>
     *   <li>ModUtil::STATE_UPGRADED</li>
     *   <li>ModUtil::STATE_NOTALLOWED</li>
     *   <li>ModUtil::STATE_INVALID</li>
     * </ul>
     *
     * @param integer $state The state of the module.
     *
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Returns the value at the specified offset (see {@link ArrayAccess::offsetGet()}).
     *
     * @param mixed $key The offset to retrieve.
     *
     * @return mixed The value at the specified offset.
     */
    public function offsetGet($key)
    {
        return $this->$key;
    }

    /**
     * Set the value at the specified offset (see {@link ArrayAccess::offsetSet()}).
     *
     * @param mixed $key   The offset to retrieve.
     * @param mixed $value The value to set at the specified offset.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Indicate whether the specified offset is set (see {@link ArrayAccess::offsetExists()}).
     *
     * @param mixed $key The offset to check.
     *
     * @return boolean True if the offset is set, otherwise false.
     */
    public function offsetExists($key)
    {
        return (bool)isset($this->$key);
    }

    /**
     * Unset the specified offset (see {@link ArrayAccess::offsetUnset()}).
     *
     * @param mixed $key The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->$key = null;
    }

    /**
     * Setup Hook Subscribers if any.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
    }

    /**
     * Register a hook subscriber bundle.
     *
     * @param Zikula_HookManager_SubscriberBundle $bundle HookBundle.
     *
     * @return Zikula_AbstractVersion
     */
    public function registerHookSubscriberBundle(Zikula_HookManager_SubscriberBundle $bundle)
    {
        if (array_key_exists($bundle->getArea(), $this->hookSubscriberBundles)) {
            throw new InvalidArgumentException(sprintf('Area %s is already registered', $bundle->getArea()));
        }

        $this->hookSubscriberBundles[$bundle->getArea()] = $bundle;
        return $this;
    }

    /**
     * Register a hook subscriber bundle.
     *
     * @param Zikula_HookManager_ProviderBundle $bundle HookProviderBundle.
     *
     * @return Zikula_AbstractVersion
     */
    public function registerHookProviderBundle(Zikula_HookManager_ProviderBundle $bundle)
    {
        if (array_key_exists($bundle->getArea(), $this->hookProviderBundles)) {
            throw new InvalidArgumentException(sprintf('Area %s is already registered', $bundle->getArea()));
        }

        $this->hookProviderBundles[$bundle->getArea()] = $bundle;
        return $this;
    }

    /**
     * Returns array of hook subscriber bundles.
     *
     * Usually this will only be one.
     *
     * @return array Of Zikula_Version_HookSubscriberBundle
     */
    public function getHookSubscriberBundles()
    {
        return $this->hookSubscriberBundles;
    }

    /**
     * Returns array of hook bundles.
     *
     * Usually this will only be one.
     *
     * @return array Of Zikula_Version_HookProviderBundle
     */
    public function getHookProviderBundles()
    {
        return $this->hookProviderBundles;
    }

    /**
     * Get hook subscriber bundle for a given area.
     *
     * @param string $area Area.
     *
     * @throws InvalidArgumentException If the area specified is not registered.
     *
     * @return Zikula_HookManager_SubscriberBundle
     */
    public function getHookSubscriberBundle($area)
    {
        if (!array_key_exists($area, $this->hookSubscriberBundles)) {
            throw new InvalidArgumentException(__f('Hook subscriber area %s does not exist', $area));
        }

        return $this->hookSubscriberBundles[$area];
    }

    /**
     * Get hook provider bundle for a given area.
     *
     * @param string $area Area.
     *
     * @throws InvalidArgumentException If the area specified is not registered.
     *
     * @return Zikula_HookManager_ProviderBundle
     */
    public function getHookProviderBundle($area)
    {
        if (!array_key_exists($area, $this->hookProviderBundles)) {
            throw new InvalidArgumentException(__f('Hook provider area %s does not exist', $area));
        }

        return $this->hookProviderBundles[$area];
    }
}
