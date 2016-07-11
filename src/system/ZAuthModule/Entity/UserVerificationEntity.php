<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * UserVerification entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Zikula\ZAuthModule\Entity\Repository\UserVerificationRepository")
 * @ORM\Table(name="users_verifychg")
 *
 * Account-change verification table.
 * Holds a one-time use, expirable verification code used when a user needs to changs his email address,
 * reset his password and has not answered any security questions,
 * or when a new user is registering with the site for the first time.
 */
class UserVerificationEntity extends EntityAccess
{
    /**
     * ID: Primary ID of the verification record. Not related to the uid.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Change type: a code indicating what type of change action created this record.
     *
     * @ORM\Column(type="smallint")
     */
    private $changetype;

    /**
     * User ID: Primary ID of the user record to which this verification record is related. Foreign key to users table.
     *
     * @ORM\Column(type="integer")
     */
    private $uid;

    /**
     * New e-mail address: If the change type indicates that this verification record was created as a result of a user changing his e-mail address,
     * then this field holds the new address temporarily until the verification is complete.
     * Only after the verification code is received back from the user (thus, verifying the new e-mail address) is the new e-mail address saved to the user's account record.
     *
     * @ORM\Column(type="string", length=60)
     */
    private $newemail;

    /**
     * Verification Code: The verification code last sent to the user to verify the requested action, as a salted hash of the value sent.
     *
     * @ORM\Column(type="string", length=138)
     */
    private $verifycode;

    /**
     * Date/Time created: The date and time the verification record was created, as a UTC date/time, used to expire the record.
     *
     * @ORM\Column(type="datetime")
     */
    private $created_dt;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->changetype = 0;
        $this->uid = 0;
        $this->newemail = '';
        $this->verifycode = '';
        $this->created_dt = new \DateTime("now");
    }

    /**
     * get the id of the user verification
     *
     * @return integer the user verification's id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set the id for the user verification
     *
     * @param integer $id the user verification's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get the changetype of the user verification
     *
     * @return integer the user verification's changetype
     */
    public function getChangetype()
    {
        return $this->changetype;
    }

    /**
     * set the changetype for the user verification
     *
     * @param integer $changetype the user verification's changetype
     */
    public function setChangetype($changetype)
    {
        $this->changetype = $changetype;
    }

    /**
     * get the uid of the user verification
     *
     * @return integer the user verification's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * set the uid for the user verification
     *
     * @param integer $uid the user verification's uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * get the new email of the user verification
     *
     * @return string the user verification's new email
     */
    public function getNewemail()
    {
        return $this->newemail;
    }

    /**
     * set the new email for the user verification
     *
     * @param string $newemail the user verification's new email
     */
    public function setNewemail($newemail)
    {
        $this->newemail = $newemail;
    }

    /**
     * get the verifycode of the user verification
     *
     * @return string the user verification's verifycode
     */
    public function getVerifycode()
    {
        return $this->verifycode;
    }

    /**
     * set the verifycode for the user verification
     *
     * @param string $verifycode the user verification's verifycode
     */
    public function setVerifycode($verifycode)
    {
        $this->verifycode = $verifycode;
    }

    /**
     * get the created date of the user verification
     *
     * @return \DateTime the user verification's created date
     */
    public function getCreated_Dt()
    {
        return $this->created_dt;
    }

    /**
     * set the created date for the user verification
     *
     * @param string|\DateTime $created_dt the user verification's created date
     */
    public function setCreated_Dt($created_dt)
    {
        if ($created_dt instanceof \DateTime) {
            $this->created_dt = $created_dt;
        } else {
            $this->created_dt = new \DateTime($created_dt);
        }
    }
}
