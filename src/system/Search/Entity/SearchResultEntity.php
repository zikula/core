<?php

namespace Search\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchResult
 *
 * @ORM\Table(name="search_result")
 * @ORM\Entity
 */
class SearchResultEntity
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var text $text
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * @var string $module
     *
     * @ORM\Column(name="module", type="string", length=100, nullable=true)
     */
    private $module;

    /**
     * @var string $extra
     *
     * @ORM\Column(name="extra", type="string", length=100, nullable=true)
     */
    private $extra;

    /**
     * @var \Datetime $created
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var \Datetime $found
     *
     * @ORM\Column(name="found", type="datetime", nullable=true)
     */
    private $found;

    /**
     * @var string $sesid
     *
     * @ORM\Column(name="sesid", type="string", length=50, nullable=true)
     */
    private $sesid;


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
     * Set title
     *
     * @param string $title
     * @return SearchResultEntity
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param text $text
     * @return SearchResultEntity
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Get text
     *
     * @return text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set module
     *
     * @param string $module
     * @return SearchResultEntity
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Get module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set extra
     *
     * @param string $extra
     * @return SearchResultEntity
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * Get extra
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Set created
     *
     * @param \Datetime $created
     * @return SearchResultEntity
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return \Datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set found
     *
     * @param \Datetime $found
     * @return SearchResultEntity
     */
    public function setFound($found)
    {
        $this->found = $found;
        return $this;
    }

    /**
     * Get found
     *
     * @return \Datetime
     */
    public function getFound()
    {
        return $this->found;
    }

    /**
     * Set sesid
     *
     * @param string $sesid
     * @return SearchResultEntity
     */
    public function setSesid($sesid)
    {
        $this->sesid = $sesid;
        return $this;
    }

    /**
     * Get sesid
     *
     * @return string
     */
    public function getSesid()
    {
        return $this->sesid;
    }
}