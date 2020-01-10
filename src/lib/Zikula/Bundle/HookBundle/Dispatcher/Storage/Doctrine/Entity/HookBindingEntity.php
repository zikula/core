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

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * HookBinding
 *
 * @ORM\Table(name="hook_binding")
 * @ORM\Entity(repositoryClass="Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\Repository\HookBindingRepository")
 */
class HookBindingEntity extends EntityAccess
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="sowner", type="string", length=40, nullable=false)
     * @Assert\Length(min="0", max="40", allowEmptyString="false")
     * @var string
     */
    private $sowner;

    /**
     * @ORM\Column(name="powner", type="string", length=40, nullable=false)
     * @Assert\Length(min="0", max="40", allowEmptyString="false")
     * @var string
     */
    private $powner;

    /**
     * @ORM\Column(name="sareaid", type="string", length=512, nullable=false)
     * @Assert\Length(min="0", max="512", allowEmptyString="false")
     * @var string
     */
    private $sareaid;

    /**
     * @ORM\Column(name="pareaid", type="string", length=512, nullable=false)
     * @Assert\Length(min="0", max="512", allowEmptyString="false")
     * @var string
     */
    private $pareaid;

    /**
     * @ORM\Column(name="category", type="string", length=20, nullable=false)
     * @Assert\Length(min="0", max="20", allowEmptyString="false")
     * @var string
     */
    private $category;

    /**
     * @ORM\Column(name="sortorder", type="smallint", nullable=false)
     * @var int
     */
    private $sortorder;

    public function getId(): int
    {
        return $this->id;
    }

    public function setSowner(string $sowner): self
    {
        $this->sowner = $sowner;

        return $this;
    }

    public function getSowner(): string
    {
        return $this->sowner;
    }

    public function setPowner(string $powner): self
    {
        $this->powner = $powner;

        return $this;
    }

    public function getPowner(): string
    {
        return $this->powner;
    }

    public function setSareaid(string $subscriberAreaId): self
    {
        $this->sareaid = $subscriberAreaId;

        return $this;
    }

    public function getSareaid(): string
    {
        return $this->sareaid;
    }

    public function setPareaid(string $providerAreaId): self
    {
        $this->pareaid = $providerAreaId;

        return $this;
    }

    public function getPareaid(): string
    {
        return $this->pareaid;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setSortorder(int $sortorder): self
    {
        $this->sortorder = $sortorder;

        return $this;
    }

    public function getSortorder(): int
    {
        return $this->sortorder;
    }
}
