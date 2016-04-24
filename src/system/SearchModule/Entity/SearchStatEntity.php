<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * SearchStat
 *
 * @ORM\Table(name="search_stat")
 * @ORM\Entity
 */
class SearchStatEntity extends EntityAccess
{
    /**
     * id of the previous search
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * search terms of the previous search
     *
     * @var string
     *
     * @ORM\Column(name="search", type="string", length=50, nullable=false)
     */
    private $search;

    /**
     * Number of times previous search has been run
     *
     * @var integer
     *
     * @ORM\Column(name="scount", type="integer", nullable=false)
     */
    private $scount;

    /**
     * timestamp of last time this search was run
     *
     * @var \Datetime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set search
     *
     * @param string $search
     * @return SearchStatEntity
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Get search
     *
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Set scount
     *
     * @param integer $scount
     * @return SearchStatEntity
     */
    public function setCount($scount)
    {
        $this->scount = $scount;

        return $this;
    }

    /**
     * Get scount
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->scount;
    }

    /**
     * Set date
     *
     * @param \Datetime $date
     * @return SearchStatEntity
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \Datetime
     */
    public function getDate()
    {
        return $this->date;
    }
}
