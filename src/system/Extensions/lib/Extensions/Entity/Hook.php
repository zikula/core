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
 * Hook entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Extensions_Entity_Repository_Hook")
 * @ORM\Table(name="hooks",indexes={@ORM\index(name="smodule",columns={"smodule"}),@ORM\index(name="smodule_tmodule",columns={"smodule","tmodule"})})
 */
class Extensions_Entity_Hook extends Zikula_EntityAccess
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
    private $object;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $action;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $smodule;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $stype;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $tarea;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $tmodule;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $ttype;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $tfunc;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $sequence;

    
    /**
     * constructor 
     */
    public function __construct()
    {
        $this->object = '';
        $this->action = '';
        $this->smodule = '';
        $this->stype = '';
        $this->tarea = '';
        $this->tmodule = '';
        $this->ttype = '';
        $this->tfunc = '';
        $this->sequence = 0;
    }
    
    /**
     * get the id of the hook
     * 
     * @return integer the hook's id 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * set the id for the hook
     * 
     * @param integer $id the hook's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * get the object of the hook
     *
     * @return string the hook's object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * set the object for the hook
     *
     * @param string $object the hook's object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }
    
    /**
     * get the action of the hook
     *
     * @return string the hook's action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * set the action for the hook
     *
     * @param string $action the hook's action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    /**
     * get the smodule of the hook
     *
     * @return string the hook's smodule
     */
    public function getSmodule()
    {
        return $this->smodule;
    }

    /**
     * set the smodule for the hook
     *
     * @param string $smodule the hook's smodule
     */
    public function setSmodule($smodule)
    {
        $this->smodule = $smodule;
    }
    
    /**
     * get the stype of the hook
     *
     * @return string the hook's stype
     */
    public function getStype()
    {
        return $this->stype;
    }

    /**
     * set the stype for the hook
     *
     * @param string $stype the hook's stype
     */
    public function setStype($stype)
    {
        $this->stype = $stype;
    }
    
    /**
     * get the tarea of the hook
     *
     * @return string the hook's tarea
     */
    public function getTarea()
    {
        return $this->tarea;
    }

    /**
     * set the tarea for the hook
     *
     * @param string $tarea the hook's tarea
     */
    public function setTarea($tarea)
    {
        $this->tarea = $tarea;
    }
    
    /**
     * get the tmodule of the hook
     *
     * @return string the hook's tmodule
     */
    public function getTmodule()
    {
        return $this->tmodule;
    }

    /**
     * set the tmodule for the hook
     *
     * @param string $tmodule the hook's tmodule
     */
    public function setTmodule($tmodule)
    {
        $this->tmodule = $tmodule;
    }
    
    /**
     * get the ttype of the hook
     *
     * @return string the hook's ttype
     */
    public function getTtype()
    {
        return $this->ttype;
    }

    /**
     * set the ttype for the hook
     *
     * @param string $ttype the hook's ttype
     */
    public function setTtype($ttype)
    {
        $this->ttype = $ttype;
    }
    
    /**
     * get the tfunc of the hook
     *
     * @return string the hook's tfunc
     */
    public function getTfunc()
    {
        return $this->tfunc;
    }

    /**
     * set the tfunc for the hook
     *
     * @param string $tfunc the hook's tfunc
     */
    public function setTfunc($tfunc)
    {
        $this->tfunc = $tfunc;
    }
    
    /**
     * get the sequence of the hook
     *
     * @return integer the hook's sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * set the sequence for the hook
     *
     * @param integer $sequence the hook's sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }
}
