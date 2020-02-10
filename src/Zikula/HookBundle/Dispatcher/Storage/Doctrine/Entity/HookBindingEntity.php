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
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Traits\HookEntityTrait;

/**
 * HookBinding
 *
 * @ORM\Table(name="hook_binding")
 * @ORM\Entity(repositoryClass="Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\Repository\HookBindingRepository")
 */
class HookBindingEntity extends EntityAccess
{
    use HookEntityTrait;

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

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setSortorder(int $sortorder): void
    {
        $this->sortorder = $sortorder;
    }

    public function getSortorder(): int
    {
        return $this->sortorder;
    }
}
