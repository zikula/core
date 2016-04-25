<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Permission entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\PermissionsModule\Entity\Repository\PermissionRepository")
 * @ORM\Table(name="group_perms")
 */
class PermissionEntity extends EntityAccess
{
    /**
     * permission rule id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pid;

    /**
     * group id for the rule
     *
     * @ORM\Column(type="integer")
     */
    private $gid;

    /**
     * the place of the rule in the sequence
     *
     * @ORM\Column(type="integer")
     */
    private $sequence;

    /**
     * the realm assoiciated with this rule
     *
     * @ORM\Column(type="integer")
     */
    private $realm;

    /**
     * the component part of the rule
     *
     * @ORM\Column(type="string", length=255)
     */
    private $component;

    /**
     * the instance part of the rule
     *
     * @ORM\Column(type="string", length=255)
     */
    private $instance;

    /**
     * the access level of the rule
     *
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * the bond of the rule
     *
     * @ORM\Column(type="integer")
     */
    private $bond;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->gid = 0;
        $this->sequence = 0;
        $this->realm = 0;
        $this->component = '';
        $this->instance = '';
        $this->level = 0;
        $this->bond = 0;
    }

    /**
     * get the pid of the permission
     *
     * @return integer the permission's pid
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * set the pid for the permission
     *
     * @param integer $pid the permission's pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * get the gid of the permission
     *
     * @return integer the permission's gid
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * set the gid for the permission
     *
     * @param integer $gid the permission's gid
     */
    public function setGid($gid)
    {
        $this->gid = $gid;
    }

    /**
     * get the sequence of the permission
     *
     * @return integer the permission's sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * set the sequence for the permission
     *
     * @param integer $sequence the permission's sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * get the realm of the permission
     *
     * @return integer the permission's realm
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * set the realm for the permission
     *
     * @param integer $realm the permission's realm
     */
    public function setRealm($realm)
    {
        $this->realm = $realm;
    }

    /**
     * get the component of the permission
     *
     * @return string the permission's component
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * set the component for the permission
     *
     * @param string $component the permission's component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * get the instance of the permission
     *
     * @return string the permission's instance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * set the instance for the permission
     *
     * @param string $instance the permission's instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * get the level of the permission
     *
     * @return integer the permission's level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * set the level for the permission
     *
     * @param integer $level the permission's level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * get the bond of the permission
     *
     * @return integer the permission's bond
     */
    public function getBond()
    {
        return $this->bond;
    }

    /**
     * set the bond for the permission
     *
     * @param integer $bond the permission's bond
     */
    public function setBond($bond)
    {
        $this->bond = $bond;
    }
}
