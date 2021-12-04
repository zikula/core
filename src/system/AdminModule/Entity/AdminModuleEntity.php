<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

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
     * @var int
     */
    private $amid;

    /**
     * The module id
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $mid;

    /**
     * The category id
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $cid;

    /**
     * The sort order for this module
     *
     * @ORM\Column(type="integer")
     * @var int
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

    public function getAmid(): ?int
    {
        return $this->amid;
    }

    public function setAmid(int $amid): self
    {
        $this->amid = $amid;

        return $this;
    }

    public function getMid(): int
    {
        return $this->mid;
    }

    public function setMid(int $mid): self
    {
        $this->mid = $mid;

        return $this;
    }

    public function getCid(): int
    {
        return $this->cid;
    }

    public function setCid(int $cid): self
    {
        $this->cid = $cid;

        return $this;
    }

    public function getSortorder(): int
    {
        return $this->sortorder;
    }

    public function setSortorder(int $sortorder): self
    {
        $this->sortorder = $sortorder;

        return $this;
    }
}
