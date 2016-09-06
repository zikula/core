<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * AdminModule entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\AdminModule\Entity\Repository\AdminModuleRepository")
 * @ORM\Table(name="admin_module",indexes={@ORM\Index(name="mid_cid",columns={"mid","cid"})})
 */
class AdminModuleEntity extends EntityAccess
{
    /**
     * The id key field
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $amid;

    /**
     * The module id
     *
     * @ORM\Column(type="integer")
     */
    private $mid;

    /**
     * The category id
     *
     * @ORM\Column(type="integer")
     */
    private $cid;

    /**
     * The sort order for this module
     *
     * @ORM\Column(type="integer")
     */
    private $sortorder;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->mid = 0;
        $this->cid = 0;
        $this->sortorder = 0;
    }

    /**
     * get the id of the module/category association
     *
     * @return integer the module/category association id
     */
    public function getAmid()
    {
        return $this->amid;
    }

    /**
     * set the id for the module/category association
     *
     * @param integer $amid the module/category association id
     */
    public function setAmid($amid)
    {
        $this->amid = $amid;
    }

    /**
     * get the id of the module
     *
     * @return integer the module id
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * set the id for the module
     *
     * @param integer $mid the module id
     */
    public function setMid($mid)
    {
        $this->mid = $mid;
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
     * get the sortorder of the module/category association
     *
     * @return integer the module/category association sortorder
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * set the sortorder for the module/category association
     *
     * @param integer $sortorder the module/category association sortorder
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;
    }
}
