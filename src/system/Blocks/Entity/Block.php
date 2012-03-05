<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Block entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity(repositoryClass="Blocks_Entity_Repository_Block")
 * @ORM\Table(name="blocks",indexes={@ORM\index(name="active_idx",columns={"active"})})
 */
class Blocks_Entity_Block extends Zikula_EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $bid;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bkey;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;
    
    /**
     * @ORM\Column(type="text")
     */
    private $description;
    
    /**
     * @ORM\Column(type="text")
     */
    private $content;
    
    /**
     * @ORM\Column(type="text")
     */
    private $url;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $mid;
    
    /**
     * @ORM\Column(type="array")
     */
    private $filter;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $active;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $collapsable;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $defaultstate;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $refresh;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $last_update;
    
    /**
     * @ORM\Column(type="string", length=30)
     */
    private $language;


    /* constructor */
    public function __construct()
    {
        $this->bkey = '';
        $this->title = '';
        $this->description = '';
        $this->content = '';
        $this->url = '';
        $this->mid = 0;
        $this->filter = array();
        $this->active = 1;
        $this->collapsable = 1;
        $this->defaultstate = 1;
        $this->refresh = 3600;
        $this->last_update = new \DateTime("now");
        $this->language = '';
    }

    /* getters & setters */
    public function getBid()
    {
        return $this->bid;
    }

    public function setBid($bid)
    {
        $this->bid = $bid;
    }
    
    public function getBkey()
    {
        return $this->bkey;
    }

    public function setBkey($bkey)
    {
        $this->bkey = $bkey;
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    public function getMid()
    {
        return $this->mid;
    }

    public function setMid($mid)
    {
        $this->mid = $mid;
    }
    
    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }
    
    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }
    
    public function getCollapsable()
    {
        return $this->collapsable;
    }

    public function setCollapsable($collapsable)
    {
        $this->collapsable = $collapsable;
    }
    
    public function getDefaultstate()
    {
        return $this->defaultstate;
    }

    public function setDefaultstate($defaultstate)
    {
        $this->defaultstate = $defaultstate;
    }
    
    public function getRefresh()
    {
        return $this->refresh;
    }

    public function setRefresh($refresh)
    {
        $this->refresh = $refresh;
    }
    
    public function getLast_Update()
    {
        return $this->last_update;
    }

    public function setLast_Update()
    {
        $this->last_update = new \DateTime("now");
    }
    
    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }
}
