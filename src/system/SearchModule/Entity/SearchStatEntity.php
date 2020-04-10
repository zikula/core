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

namespace Zikula\SearchModule\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Bundle\CoreBundle\Doctrine\EntityAccess;

/**
 * SearchStat
 *
 * @ORM\Entity(repositoryClass="Zikula\SearchModule\Entity\Repository\SearchStatRepository")
 * @ORM\Table(name="search_stat")
 */
class SearchStatEntity extends EntityAccess
{
    /**
     * id of the previous search
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     * search terms of the previous search
     *
     * @ORM\Column(name="search", type="string", length=50, nullable=false)
     * @Assert\Length(min="0", max="50", allowEmptyString="false")
     * @var string
     */
    private $search;

    /**
     * Number of times previous search has been run
     *
     * @ORM\Column(name="scount", type="integer", nullable=false)
     * @Assert\NotNull
     * @var int
     */
    private $scount;

    /**
     * Timestamp of last time this search was run
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     * @var DateTime
     */
    private $date;

    public function __construct()
    {
        $this->search = '';
        $this->scount = 0;
        $this->date = new DateTime('now', new DateTimeZone('UTC'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function setCount(int $scount): self
    {
        $this->scount = $scount;

        return $this;
    }

    public function incrementCount(): void
    {
        $this->scount++;
    }

    public function getCount(): int
    {
        return $this->scount;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}
