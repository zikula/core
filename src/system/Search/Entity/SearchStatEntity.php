<?php

namespace Search\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchStat
 *
 * @ORM\Table(name="search_stat")
 * @ORM\Entity
 */
class SearchStatEntity
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
     * @var date $date
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
     * @return SearchStat
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
     * @return SearchStat
     */
    public function setScount($scount)
    {
        $this->scount = $scount;
        return $this;
    }

    /**
     * Get scount
     *
     * @return integer
     */
    public function getScount()
    {
        return $this->scount;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return SearchStat
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }
}