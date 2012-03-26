<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Extension entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Extensions_Entity_Repository_Extension")
 * @ORM\Table(name="modules",indexes={@ORM\index(name="state",columns={"state"}),@ORM\index(name="mod_state",columns={"name","state"})})
 */
class Extensions_Entity_Extension extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $displayname;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $directory;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $version;

    /**
     * @ORM\Column(type="array")
     */
    private $capabilities;

    /**
     * @ORM\Column(type="smallint")
     */
    private $state;

    /**
     * @ORM\Column(type="array")
     */
    private $securityschema;

    /**
     * @ORM\Column(type="string", length=9)
     */
    private $core_min;

    /**
     * @ORM\Column(type="string", length=9)
     */
    private $core_max;

    
    /**
     * constructor 
     */
    public function __construct()
    {
        $this->name = '';
        $this->type = 0;
        $this->displayname = '';
        $this->url = '';
        $this->description = '';
        $this->directory = '';
        $this->version = '0.0.0';
        $this->capabilities = array();
        $this->state = 0;
        $this->securityschema = array();
        $this->core_min = '';
        $this->core_max = '';
    }
    
    /**
     * get the id of the extension
     * 
     * @return integer the extension's id 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * set the id for the extension
     * 
     * @param integer $id the extension's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * get the name of the extension
     * 
     * @return string the extension's name 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the extension
     * 
     * @param string $name the extension's name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the type of the extension
     * 
     * @return integer the extension's type (2=module, 3=system, 4=core) 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * set the type for the extension
     * 
     * @param integer $type the extension's type (2=module, 3=system, 4=core)
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * get the display name of the extension
     * 
     * @return string the extension's display name 
     */
    public function getDisplayname()
    {
        return $this->displayname;
    }

    /**
     * set the display name for the extension
     * 
     * @param string $displayname the extension's display name
     */
    public function setDisplayname($displayname)
    {
        $this->displayname = $displayname;
    }

    /**
     * get the url of the extension
     * 
     * @return string the extension's url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * set the url for the extension
     * 
     * @param string $url the extension's url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * get the description of the extension
     * 
     * @return string the extension's description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set the description for the extension
     * 
     * @param string $description the extension's description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * get the directory name of the extension
     * 
     * @return string the extension's directory name
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * set the directory name for the extension
     * 
     * @param string $directory the extension's directory name
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * get the version of the extension
     * 
     * @return string the extension's version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * set the version for the extension
     * 
     * @param string $version the extension's version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * get the capabilities of the extension
     * 
     * @return array the extension's capabilities
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * set the capabilities for the extension
     * 
     * @param array $capabilities the extension's capabilities
     */
    public function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
    }

    /**
     * get the state of the extension
     * 
     * @return integer the extension's state (1=not installed, 2=inactive, 3=active, 4=missing files, 5=upgraded, 6=not allowed, -1=invalid)
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * set the state for the extension
     * 
     * @param integer $state the extension's state (1=not installed, 2=inactive, 3=active, 4=missing files, 5=upgraded, 6=not allowed, -1=invalid)
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * get the security schema of the extension
     * 
     * @return array the extension's security schema 
     */
    public function getSecurityschema()
    {
        return $this->securityschema;
    }

    /**
     * set the security schema for the extension
     * 
     * @param array $securityschema the extension's security schema
     */
    public function setSecurityschema($securityschema)
    {
        $this->securityschema = $securityschema;
    }

    /**
     * get the minimum core version of the extension
     * 
     * @return string the extension's minimum core version
     */
    public function getCore_min()
    {
        return $this->core_min;
    }

    /**
     * set the minimum core version for the extension
     * 
     * @param string $core_min the extension's minimum core version
     */
    public function setCore_min($core_min)
    {
        $this->core_min = $core_min;
    }

    /**
     * get the maximum core version of the extension
     * 
     * @return string the extension's maximum core version
     */
    public function getCore_max()
    {
        return $this->core_max;
    }

    /**
     * set the maximum core version for the extension
     * 
     * @param string $core_max the extension's maximum core version
     */
    public function setCore_max($core_max)
    {
        $this->core_max = $core_max;
    }
}
