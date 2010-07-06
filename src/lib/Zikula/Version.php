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
class Zikula_Version implements ArrayAccess
{
    protected $name;
    protected $displayname;
    protected $url;
    protected $description;
    protected $version = 0;
    protected $contact;
    protected $securityschema;
    protected $moddependencies;
    protected $capabilities = array();

    protected $domain;
    protected $type;
    protected $directory;

    protected $baseDir;
    protected $libBaseDir;
    protected $systemBaseDir;

    protected $reflection;

//    abstract public function getMetaData();

    public function __construct()
    {
        $this->reflection = new ReflectionObject($this);
        Zikula_ClassProperties::load($this, $this->getMetaData());
        $p = explode('_', get_class($this));
        $this->name = $p[0];
        $this->directory = $this->name; // legacy handling
        $path = str_replace('Version.php', '', $this->reflection->getFileName());
        $this->baseDir = realpath("$path/../../") ;
        $this->libBaseDir = realpath($this->baseDir . '/lib');
        $this->systemBaseDir = realpath($this->baseDir . '/../..');
        $this->type = (strrpos($this->baseDir, 'modules')) ? ModUtil::TYPE_MODULE : ModUtil::TYPE_SYSTEM;
        if ($this->type == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }
    }

    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    public function getBaseDir()
    {
        return $this->baseDir;
    }

    public function getLibBaseDir()
    {
        return $this->libBaseDir;
    }

    public function getSystemBaseDir()
    {
        return $this->systemBaseDir;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDisplayName()
    {
        return $this->displayname;
    }

    public function setDisplayName($displayName)
    {
        $this->displayname = $displayName;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    public function getSecuritySchema()
    {
        return $this->securityschema;
    }

    public function setSecuritySchema($securitySchema)
    {
        $this->securityschema = $securitySchema;
    }

    public function getModDependencies()
    {
        return $this->moddependencies;
    }

    public function setModDependencies($dependencies)
    {
        $this->moddependencies = $dependencies;
    }

    public function getCapabilities()
    {
        return $this->capabilities;
    }

    public function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
    }

    public function offsetGet($key)
    {
        return $this->$key;
    }

    public function offsetSet($key, $value)
    {
        $this->$key = $value;
    }

    public function offsetExists($key)
    {
        return (bool)isset($this->$key);
    }

    public function offsetUnset($key)
    {
        $this->$key = null;
    }

}
