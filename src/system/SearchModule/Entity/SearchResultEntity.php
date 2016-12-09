<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\UrlInterface;

/**
 * SearchResult
 *
 * @ORM\Entity(repositoryClass="Zikula\SearchModule\Entity\Repository\SearchResultRepository")
 * @ORM\Table(name="search_result")
 */
class SearchResultEntity
{
    /**
     * ID of the search
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * title of the search
     *
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * the matching search text
     *
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * the module providing the search hit
     *
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=100, nullable=true)
     */
    private $module;

    /**
     * additional information about this search result
     *
     * @var string
     *
     * @ORM\Column(name="extra", type="string", length=1000, nullable=true)
     */
    private $extra;

    /**
     * creation timestamp of this search hit
     *
     * @var \Datetime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * Last found timestamp of this search hit
     *
     * @var \Datetime
     *
     * @ORM\Column(name="found", type="datetime", nullable=true)
     */
    private $found;

    /**
     * Session id associated
     *
     * @var string
     *
     * @ORM\Column(name="sesid", type="string", length=50, nullable=true)
     */
    private $sesid;

    /**
     * Url for found item
     *
     * @var UrlInterface
     *
     * @ORM\Column(type="object", nullable=true)
     */
    private $url;

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
     * @param string $text
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
     * @return string
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

    /**
     * @param UrlInterface $url
     */
    public function setUrl(UrlInterface $url)
    {
        $this->url = $url;
    }

    /**
     * @return UrlInterface
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function merge(array $result)
    {
        $this->title = isset($result['title']) ? $result['title'] : 'unknown';
        $this->text = isset($result['text']) ? $result['text'] : null;
        $this->extra = isset($result['extra']) ? $result['extra'] : null;
        $this->module = isset($result['module']) ? $result['module'] : null;
        $this->created = (isset($result['created']) && ($result['created'] instanceof \DateTime)) ? $result['created'] : new \DateTime('now', new \DateTimeZone('UTC'));
        $this->sesid = isset($result['sesid']) ? $result['sesid'] : null;
        $this->url = (isset($result['url']) && ($result['url'] instanceof UrlInterface)) ? $result['url'] : null;
    }
}
