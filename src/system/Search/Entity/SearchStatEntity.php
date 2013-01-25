<?php

namespace Search\Entity;

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
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $search
     *
     * @ORM\Column(name="search", type="string", length=50, nullable=false)
     */
    private $search;

    /**
     * @var integer $scount
     *
     * @ORM\Column(name="scount", type="integer", nullable=false)
     */
    private $scount;

    /**
     * @var \Datetime $date
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