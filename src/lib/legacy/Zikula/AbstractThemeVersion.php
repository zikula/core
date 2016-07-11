<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula Version base class.
 *
 * @deprecated
 */
abstract class Zikula_AbstractThemeVersion implements ArrayAccess
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

    protected $user;
    protected $admin;
    protected $system;

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
     * A {@link ReflectionObject} instance for this instance of the class.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Build a new instance.
     */
    public function __construct($bundle)
    {
        $this->name = $bundle->getName();
        $this->baseDir = $bundle->getPath();
        $this->domain = ZLanguage::getThemeDomain($this->name);

        Zikula_ClassProperties::load($this, $this->getMetaData());
    }

    /**
     * Return certain data elements from this class as an array.
     *
     * @return array An array containing the name, description, display name, url, version, capabilities, dependencies,
     *                  type, directory and security schema.
     */
    public function toArray()
    {
        $meta = [
            'name' => $this->name,
            'description' => $this->description,
            'displayname' => $this->displayname,
            'version' => $this->version,
            'type' => $this->type,
            'user' => $this->user,
            'admin' => $this->admin,
            'system' => $this->system,
            'directory' => $this->directory
        ];

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

    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Sets Directory
     *
     * @param string $directory
     *
     * @return Zikula_AbstractThemeVersion
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Gets Directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Sets Domain
     *
     * @param string $domain
     *
     * @return Zikula_AbstractThemeVersion
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Gets Domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets Reflection
     *
     * @param \ReflectionObject $reflection
     *
     * @return Zikula_AbstractThemeVersion
     */
    public function setReflection($reflection)
    {
        $this->reflection = $reflection;

        return $this;
    }

    /**
     * Gets Reflection
     *
     * @return \ReflectionObject
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * Sets State
     *
     * @param int $state
     *
     * @return Zikula_AbstractThemeVersion
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Gets State
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Sets Type
     *
     * @param int $type
     *
     * @return Zikula_AbstractThemeVersion
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets Type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
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
}
