<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\AdminModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * AdminCategory entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Zikula\AdminModule\Entity\Repository\AdminCategoryRepository")
 * @ORM\Table(name="admin_category")
 */
class AdminCategoryEntity extends EntityAccess
{
    /**
     * The category id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cid;

    /**
     * The category name
     *
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    /**
     * The category description
     *
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * The sort order of the category
     *
     * @ORM\Column(type="integer")
     */
    private $sortorder;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name = '';
        $this->description = '';
        $this->sortorder = 0;
    }

    /**
     * get the id of the category
     *
     * @return integer the category id
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * set the id for the category
     *
     * @param integer $cid the category id
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
    }

    /**
     * get the name of the category
     *
     * @return string the category name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the category
     *
     * @param string $name the category name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the description of the category
     *
     * @return string the category description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set the description for the category
     *
     * @param string $description the category description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * get the sortorder of the category
     *
     * @return integer the category sortorder
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * set the sortorder for the category
     *
     * @param integer $sortorder the category sortorder
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;
    }
}
