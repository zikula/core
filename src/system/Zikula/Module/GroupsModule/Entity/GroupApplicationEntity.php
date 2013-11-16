<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\GroupsModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * GroupApplication entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="group_applications")
 */
class GroupApplicationEntity extends EntityAccess
{
    /**
     * id of the group application
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $app_id;

    /**
     * user id of the applicant
     *
     * @ORM\Column(type="integer")
     */
    private $uid;

    /**
     * group id for the application
     *
     * @ORM\Column(type="integer")
     */
    private $gid;

    /**
     * Details of the application
     *
     * @ORM\Column(type="text")
     */
    private $application;

    /**
     * Status of the application
     *
     * @ORM\Column(type="smallint")
     */
    private $status;


    /**
     * constructor
     */
    public function __construct()
    {
        $this->uid = 0;
        $this->gid = 0;
        $this->application = '';
        $this->status = 0;
    }

    /**
     * get the app_id of the group's application
     *
     * @return integer the group's application's app_id
     */
    public function getApp_id()
    {
        return $this->app_id;
    }

    /**
     * set the app_id for the group's application
     *
     * @param integer $app_id the group's application's app_id
     */
    public function setApp_id($app_id)
    {
        $this->app_id = $app_id;
    }

    /**
     * get the uid of the group's application
     *
     * @return integer the group's application's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * set the uid for the group's application
     *
     * @param integer $uid the group's application's uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * get the gid of the group's application
     *
     * @return integer the group's application's gid
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * set the gid for the group's application
     *
     * @param integer $gid the group's application's gid
     */
    public function setGid($gid)
    {
        $this->gid = $gid;
    }

    /**
     * get the application of the group's application
     *
     * @return string the group's application's application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * set the application for the group's application
     *
     * @param string $application the group's application's application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * get the status of the group's application
     *
     * @return integer the group's application's status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * set the status for the group's application
     *
     * @param integer $status the group's application's status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
