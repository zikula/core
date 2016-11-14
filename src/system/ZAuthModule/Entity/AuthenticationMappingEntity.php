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
use Zikula\ZAuthModule\Validator\Constraints as ZAuthAssert;

/**
 * @ORM\Entity(repositoryClass="Zikula\ZAuthModule\Entity\Repository\AuthenticationMappingRepository")
 * @ORM\Table(name="zauth_authentication_mapping")
 * @ZAuthAssert\ValidUserFields()
 */
class AuthenticationMappingEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $method;

    /**
     * @ORM\Column(type="integer")
     */
    private $uid;

    /**
     * @ZAuthAssert\ValidUname()
     * @ORM\Column(type="string")
     */
    private $uname;

    /**
     * @ZAuthAssert\ValidEmail()
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verifiedEmail;

    /**
     * Password: User's password for logging in.
     * This value is salted and hashed. The salt is stored in this field, delimited from the hash with a dollar sign character ($).
     *
     * @ZAuthAssert\ValidPassword()
     * @ORM\Column(type="string")
     */
    private $pass;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return integer
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param integer $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getUname()
    {
        return $this->uname;
    }

    /**
     * @param string $uname
     */
    public function setUname($uname)
    {
        $this->uname = $uname;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function isVerifiedEmail()
    {
        return $this->verifiedEmail;
    }

    /**
     * @param mixed $verifiedEmail
     */
    public function setVerifiedEmail($verifiedEmail)
    {
        $this->verifiedEmail = $verifiedEmail;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    public function getUserEntityData()
    {
        return [
            'uid' => $this->getUid(),
            'uname' => $this->getUname(),
            'email' => $this->getEmail()
        ];
    }
}
