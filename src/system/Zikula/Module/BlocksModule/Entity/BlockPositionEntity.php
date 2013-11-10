<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 * @package Zikula
 * @subpackage ZikulaBlocksModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * BlockPosition entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="block_positions",indexes={@ORM\Index(name="name_idx",columns={"name"})})
 */
class BlockPositionEntity extends EntityAccess
{
    /**
     * The id of the block postion
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pid;

    /**
     * The name of the block position
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * The description of the block position
     *
     * @ORM\Column(type="string", length=255)
     */
    private $description;


    /**
     * constructor
     */
    public function __construct()
    {
        $this->name = '';
        $this->description = '';
    }

    /**
     * get the id of the position
     *
     * @return integer the position's id
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * set the id for the position
     *
     * @param integer $pid the position's id
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * get the name of the position
     *
     * @return string the position's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the position
     *
     * @param string $name the position's name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the description of the position
     *
     * @return string the position's description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set the description for the position
     *
     * @param string $description the position's description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
