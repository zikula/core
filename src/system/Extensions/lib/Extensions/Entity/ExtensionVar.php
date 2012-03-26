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
 * ExtensionVar entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Extensions_Entity_Repository_ExtensionVar")
 * @ORM\Table(name="module_vars",indexes={@ORM\index(name="mod_var",columns={"modname","name"})})
 */
class Extensions_Entity_ExtensionVar extends Zikula_EntityAccess
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
    private $modname;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    
    /**
     * constructor 
     */
    public function __construct()
    {
        $this->modname = '';
        $this->name = '';
        $this->value = '';
    }
    
    /**
     * get the id of the extension var
     * 
     * @return integer the extension var's id 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * set the id for the extension var
     * 
     * @param integer $id the extension var's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get the module name of the extension var
     * 
     * @return string the extension var's module name 
     */
    public function getModname()
    {
        return $this->modname;
    }

    /**
     * set the module name for the extension var
     * 
     * @param string $modname the extension var's module name
     */
    public function setModname($modname)
    {
        $this->modname = $modname;
    }

    /**
     * get the name of the extension var
     * 
     * @return string the extension var's name 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the extension var
     * 
     * @param string $name the extension var's name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the value of the extension var
     * 
     * @return string the extension var's value 
     */
    public function getValue()
    {
        return unserialize($this->value);
    }

    /**
     * set the value for the extension var
     * 
     * @param string $value the extension var's value
     */
    public function setValue($value)
    {
        $this->value = serialize($value);
    }
}
