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
    protected $dependencies = array();
    protected $capabilities = array();
    protected $domain;
    protected $type;
    protected $state;
    protected $directory;
    protected $core_min;
    protected $core_max;
    protected $oldnames;

    protected $baseDir;
    protected $libBaseDir;
    protected $systemBaseDir;
    protected $reflection;

//    abstract public function getMetaData();

    public function __construct()
    {
        $this->reflection = new ReflectionObject($this);
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
        Zikula_ClassProperties::load($this, $this->getMetaData());
    }

    public function toArray()
    {
        $meta = array();
        $meta['name'] = $this->name;
        $meta['description'] = $this->description;
        $meta['displayname'] = $this->displayname;
        $meta['url'] = $this->url;
        $meta['version'] = $this->version;
        $meta['contact'] = $this->contact;
        $meta['capabilities'] = $this->capabilities;
        $meta['dependencies'] = $this->dependencies;
        $meta['type'] = $this->type;
        $meta['directory'] = $this->directory;
        $meta['securityschema'] = $this->securityschema;
        return $meta;
    }

    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
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
        if (!preg_match('#\d+\.\d+\.\d+#', $version)) {
            throw new InvalidArgumentException($this->__f('Version numbers must be in the format "a.b.c" in class %s', get_class($this)));
        }
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

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function getCapabilities()
    {
        return $this->capabilities;
    }

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

    public function getCore_min()
    {
        return $this->core_min;
    }

    public function setCore_min($core_min)
    {
        $this->core_min = $core_min;
    }

    public function getCore_max()
    {
        return $this->core_max;
    }

    public function setCore_max($core_max)
    {
        $this->core_max = $core_max;
    }

    public function getOldnames()
    {
        return $this->oldnames;
    }

    public function setOldnames($oldnames)
    {
        $this->oldnames = $oldnames;
    }
    
    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
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
