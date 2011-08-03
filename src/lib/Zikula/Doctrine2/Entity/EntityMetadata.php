<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class of one-to-one assocation between any entity and metadata.
 * 
 * @ORM\MappedSuperclass
 */
abstract class Zikula_Doctrine2_Entity_EntityMetadata extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     * @var string 
     */
    private $title;
    
    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     * @var string 
     */
    private $author;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string 
     */
    private $subject;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string 
     */
    private $keywords;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string 
     */
    private $description;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string 
     */
    private $publisher;
    
    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     * @var string 
     */
    private $contributor;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var string 
     */
    private $startdate;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var string 
     */
    private $enddate;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string 
     */
    private $type;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string 
     */
    private $format;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string 
     */
    private $uri;
    
    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @var string 
     */
    private $source;
    
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     * @var string 
     */
    private $language;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string 
     */
    private $relation;
    
    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @var string 
     */
    private $coverage;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string 
     */
    private $comment;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string 
     */
    private $extra;
    
    
    public function __construct($entity)
    {
        $this->setEntity($entity);
    }
    
    public abstract function getEntity();
    
    public abstract function setEntity($entity);
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function getAuthor()
    {
        return $this->author;
    }
    
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    
    public function getSubject()
    {
        return $this->subject;
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    public function getKeywords()
    {
        return $this->keywords;
    }
    
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function getPublisher()
    {
        return $this->publisher;
    }
    
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }
    
    public function getContributor()
    {
        return $this->contributor;
    }
    
    public function setContributor($contributor)
    {
        $this->contributor = $contributor;
    }
    
    public function getStartdate()
    {
        return $this->startdate;
    }
    
    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;
    }
    
    public function getEnddate()
    {
        return $this->enddate;
    }
    
    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getFormat()
    {
        return $this->format;
    }
    
    public function setFormat($format)
    {
        $this->format = $format;
    }
    
    public function getUri()
    {
        return $this->uri;
    }
    
    public function setUri($uri)
    {
        $this->uri = $uri;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->source = $source;
    }
    
    public function getLanguage()
    {
        return $this->language;
    }
    
    public function setLanguage($language)
    {
        $this->language = $language;
    }
    
    public function getRelation()
    {
        return $this->relation;
    }
    
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }
    
    public function getCoverage()
    {
        return $this->coverage;
    }
    
    public function setCoverage($coverage)
    {
        $this->coverage = $coverage;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    
    public function getExtra()
    {
        return $this->extra;
    }
    
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }
}

