<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Extension dependencies.
 *
 * @ORM\Entity(repositoryClass="Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository")
 * @ORM\Table(name="module_deps")
 */
class ExtensionDependencyEntity extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $modid;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $modname;

    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $minversion;

    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $maxversion;

    /**
     * @ORM\Column(type="integer", length=64)
     * @var integer
     */
    private $status;

    /**
     * Non-persisted data
     * The reason of a dependency is not saved into the database to avoid multilingual problems but loaded from Version.php.
     * @var string
     */
    private $reason = false;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getModid()
    {
        return $this->modid;
    }

    public function setModid($modid)
    {
        $this->modid = $modid;
    }

    public function getModname()
    {
        return $this->modname;
    }

    public function setModname($modname)
    {
        $this->modname = $modname;
    }

    public function getMinversion()
    {
        return $this->minversion;
    }

    public function setMinversion($minversion)
    {
        $this->minversion = $minversion;
    }

    public function getMaxversion()
    {
        return $this->maxversion;
    }

    public function setMaxversion($maxversion)
    {
        $this->maxversion = $maxversion;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Non-persisted data.
     *
     * Note: The reason of a dependency is not saved into the database to avoid multilingual problems but loaded from Version.php.
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * Get the reason for a dependency.
     *
     * Note: The reason of a dependency is not saved into the database to avoid multilingual problems but loaded from Version.php.
     */
    public function getReason()
    {
        return $this->reason;
    }
}
