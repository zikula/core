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
 * ExtensionDependency entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Extensions_Entity_Repository_ExtensionDependency")
 * @ORM\Table(name="module_deps")
 */
class Extensions_Entity_ExtensionDependency extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $modid;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $modname;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $minversion;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $maxversion;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;
    
    
    /**
     * constructor 
     */
    public function __construct()
    {
        $this->modid = 0;
        $this->modname = '';
        $this->minversion = '0.0.0';
        $this->maxversion = '0.0.0';
        $this->status = 0;
    }

    /**
     * get the id of the extension dependency
     * 
     * @return integer the extension dependency's id 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set the id for the extension dependency
     * 
     * @param integer $id the extension dependency's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get the module id of the extension dependency
     * 
     * @return integer the extension dependency's module id 
     */
    public function getModid()
    {
        return $this->modid;
    }

    /**
     * set the module id for the extension dependency
     * 
     * @param integer $modid the extension dependency's module id
     */
    public function setModid($modid)
    {
        $this->modid = $modid;
    }

    /**
     * get the module name of the extension dependency
     * 
     * @return string the extension dependency's module name 
     */
    public function getModname()
    {
        return $this->modname;
    }

    /**
     * set the module name for the extension dependency
     * 
     * @param string $modname the extension dependency's module name
     */
    public function setModname($modname)
    {
        $this->modname = $modname;
    }

    /**
     * get the minimum version of the extension dependency
     * 
     * @return string the extension dependency's minimum version
     */
    public function getMinversion()
    {
        return $this->minversion;
    }

    /**
     * set the minimum version for the extension dependency
     * 
     * @param string $minversion the extension dependency's minimum version
     */
    public function setMinversion($minversion)
    {
        $this->minversion = $minversion;
    }

    /**
     * get the maximum version of the extension dependency
     * 
     * @return string the extension dependency's maximum version
     */
    public function getMaxversion()
    {
        return $this->maxversion;
    }

    /**
     * set the maximum version for the extension dependency
     * 
     * @param string $maxversion the extension dependency's maximum version
     */
    public function setMaxversion($maxversion)
    {
        $this->maxversion = $maxversion;
    }

    /**
     * get the status of the extension dependency
     * 
     * @return string the extension dependency's status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * set the status for the extension dependency
     * 
     * @param string $status the extension dependency's status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
