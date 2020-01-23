<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * AdminCategory entity class.
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
     * @var int
     */
    private $cid;

    /**
     * The category name
     *
     * @ORM\Column(type="string", length=32)
     * @Assert\Length(min="0", max="32", allowEmptyString="false")
     * @var string
     */
    private $name;

    /**
     * The category description
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="0", max="255", allowEmptyString="true")
     * @var string
     */
    private $description;

    /**
     * The category icon
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(min="0", max="50", allowEmptyString="true")
     * @var string
     */
    private $icon;

    /**
     * The sort order of the category
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
        $this->name = '';
        $this->description = '';
        $this->icon = '';
        $this->sortorder = 99;
    }

    public function getCid(): ?int
    {
        return $this->cid;
    }

    public function setCid(int $cid): void
    {
        $this->cid = $cid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description ?? '';
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon ?? '';
    }

    public function getSortorder(): int
    {
        return $this->sortorder;
    }

    public function setSortorder(int $sortorder): void
    {
        $this->sortorder = $sortorder;
    }
}
