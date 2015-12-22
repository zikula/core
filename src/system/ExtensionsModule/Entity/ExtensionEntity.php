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

namespace Zikula\ExtensionsModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Extension Entity.
 *
 * @ORM\Entity(repositoryClass="Zikula\ExtensionsModule\Entity\Repository\ExtensionRepository")
 * @ORM\Table(name="modules")
 */
class ExtensionEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
//    private $namespace;

    /**
     * @ORM\Column(type="integer", length=2)
     * @var integer
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $displayname;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $directory;

    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $version;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    private $capabilities;

    /**
     * @ORM\Column(type="integer", length=2)
     * @var integer
     */
    private $state;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    private $securityschema;

    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $core_min;

    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $core_max;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

//    public function getNamespace()
//    {
//        return $this->namespace;
//    }
//
//    public function setNamespace($namespace)
//    {
//        $this->namespace = $namespace;
//    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getDisplayname()
    {
        return $this->displayname;
    }

    public function setDisplayname($displayname)
    {
        $this->displayname = $displayname;
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

    public function getDirectory()
    {
        return $this->directory;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getCapabilities()
    {
        return $this->capabilities;
    }

    public function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getSecurityschema()
    {
        return $this->securityschema;
    }

    public function setSecurityschema($securityschema)
    {
        $this->securityschema = $securityschema;
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

    public function setCorecompatibility($coreCompatibility)
    {
        // @todo temporarily use core_min to store the string - rename and remove core_max
        $this->core_min = $coreCompatibility;
    }
}
