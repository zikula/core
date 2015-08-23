<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * Block entity class.
 *
 * We use annotations to define the entity mappings to database (see http://www.doctrine-project.org/docs/orm/2.1/en/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="blocks",indexes={@ORM\Index(name="active_idx",columns={"active"})})
 */
class BlockEntity extends EntityAccess
{
    /**
     * The block id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $bid;

    /**
     * The block key
     *
     * @ORM\Column(type="string", length=255)
     */
    private $bkey;

    /**
     * The block title
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * The block description
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * The block content 
     *
     * A seralized array of block content variables
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * The block url
     *
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * The id of the module owning the block
     *
     * @ORM\Column(type="integer")
     */
    private $mid;

    /**
     * The display filter to apply to the block
     *     
     * @ORM\Column(type="array")
     */
    private $filter;

    /**
     * The active status of the block
     *
     * @ORM\Column(type="integer")
     */
    private $active;

    /**
     * Is the block collapseable
     *
     * @ORM\Column(type="integer")
     */
    private $collapsable;

    /**
     * The default display state of the block (collapsed, uncollapsed)
     *
     * @ORM\Column(type="integer")
     */
    private $defaultstate;

    /**
     * The refresh time for the block content
     *
     * @ORM\Column(type="integer")
     */
    private $refresh;

    /**
     * The last updated timestamp of the block
     *
     * @ORM\Column(type="datetime")
     */
    private $last_update;

    /**
     * The language of the block
     *
     * @ORM\Column(type="string", length=30)
     */
    private $language;


    /**
     * constructor
     */
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

    /**
     * get the id of the block
     *
     * @return integer the block's id
     */
    public function getBid()
    {
        return $this->bid;
    }

    /**
     * set the id for the block
     *
     * @param integer $bid the block's id
     */
    public function setBid($bid)
    {
        $this->bid = $bid;
    }

    /**
     * get the bkey of the block
     *
     * @return string the block's bkey
     */
    public function getBkey()
    {
        return $this->bkey;
    }

    /**
     * set the bkey for the block
     *
     * @param string $bkey the block's bkey
     */
    public function setBkey($bkey)
    {
        $this->bkey = $bkey;
    }

    /**
     * get the title of the block
     *
     * @return string the block's title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set the title for the block
     *
     * @param string $title the block's title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * get the description of the block
     *
     * @return string the block's description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set the description for the block
     *
     * @param string $description the block's description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * get the content of the block
     *
     * @return string the block's content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * set the content for the block
     *
     * @param string $content the block's content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * get the url of the block
     *
     * @return string the block's url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * set the url for the block
     *
     * @param string $url the block's url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * get the id of the module that the block belongs to
     *
     * @return integer the module's id
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * set the id of the module that the block belongs to
     *
     * @param integer $mid the module's id
     */
    public function setMid($mid)
    {
        $this->mid = $mid;
    }

    /**
     * get the filters of the block
     *
     * @return array the block's filters
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * set the filters for the block
     *
     * @param array $filter the blocks's filters
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * get the status of the block
     *
     * @return integer the status number (0=inactive, 1=active)
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * set the status of the block
     *
     * @param integer $active the status number (0=inactive, 1=active)
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * get the collapsable status of the block
     *
     * @return integer the collapsable status number (0=not collapsable, 1=collapsable)
     */
    public function getCollapsable()
    {
        return $this->collapsable;
    }

    /**
     * set the collapsable status of the block
     *
     * @param integer $collapsable the collapsable status number (0=inactive, 1=active)
     */
    public function setCollapsable($collapsable)
    {
        $this->collapsable = $collapsable;
    }

    /**
     * get the default activation state of the block
     *
     * @return integer the state number (0=inactive, 1=active)
     */
    public function getDefaultstate()
    {
        return $this->defaultstate;
    }

    /**
     * set the default activation state of the block
     *
     * @param integer $defaultstate the default activation state (0=inactive, 1=active)
     */
    public function setDefaultstate($defaultstate)
    {
        $this->defaultstate = $defaultstate;
    }

    /**
     * get the refresh rate of the block
     *
     * @return integer the refresh rate number
     */
    public function getRefresh()
    {
        return $this->refresh;
    }

    /**
     * set the refresh rate of the block
     *
     * @param integer $refresh the refresh rate in milliseconds (1sec=1000ms)
     */
    public function setRefresh($refresh)
    {
        $this->refresh = $refresh;
    }

    /**
     * get last update time of the block
     *
     * @return datetime the block's last updated time
     */
    public function getLast_Update()
    {
        return $this->last_update;
    }

    /**
     * set the last updated time of the block
     *
     * @param none
     */
    public function setLast_Update()
    {
        $this->last_update = new \DateTime("now");
    }

    /**
     * get the language of the block
     *
     * @return string the block's language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * set the language of the block
     *
     * @param string $language the block's language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }
}
