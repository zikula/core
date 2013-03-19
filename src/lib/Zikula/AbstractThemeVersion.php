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
        $meta = array();
        $meta['name'] = $this->name;
        $meta['description'] = $this->description;
        $meta['displayname'] = $this->displayname;
        $meta['url'] = $this->url;
        $meta['version'] = $this->version;
        $meta['type'] = $this->type;
        $meta['directory'] = $this->directory;

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
