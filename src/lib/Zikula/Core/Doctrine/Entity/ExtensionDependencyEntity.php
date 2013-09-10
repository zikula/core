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

namespace Zikula\Core\Doctrine\Entity;

use ModUtil;
use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Extension dependencies.
 *
 * @ORM\Entity
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
     * This is a dummy method to set the reason for a dependency. However, the reason is not saved into the database.
     * This method is required for merges to work.
     *
     * @note The reason of a dependency is not saved into the database to avoid multilingual problems but loaded from Version.php.
     */
    public function setReason($reason)
    {
        // Don't do anything. The reason is hardcoded in Version.php.
    }

    /**
     * Get the reason for a dependency.
     *
     * @param string $reason
     *
     * @note The reason of a dependency is not saved into the database to avoid multilingual problems but loaded from Version.php.
     */
    public function getReason()
    {
        if ($this->reason === false) {
            $modinfo = ModUtil::getInfo($this->getModid());
            $bundle = ModUtil::getModule($modinfo['name']);

            if (null !== $bundle) {
                $versionClass =  $bundle->getVersionClass();
                $version = new $versionClass();
                $meta = $version->getMetaData();
                $dependencies = $meta['dependencies'];
                foreach ($dependencies as $dependency) {
                    if ($dependency['modname'] == $this->modname) {
                        $this->reason = $dependency['reason'];
                        return $this->reason;
                    }
                }
            }
            $this->reason = '';
        }

        return $this->reason;
    }
}
