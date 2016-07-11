<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

/**
 * Theme entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository")
 * @ORM\Table(name="themes")
 */
class ThemeEntity extends EntityAccess
{
    /**
     * theme id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * theme name
     *
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * theme type
     *
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * display name for theme
     *
     * @ORM\Column(type="string", length=64)
     */
    private $displayname;

    /**
     * theme description
     *
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * theme file system directory
     *
     * @ORM\Column(type="string", length=64)
     */
    private $directory;

    /**
     * theme version
     *
     * @ORM\Column(type="string", length=10)
     */
    private $version;

    /**
     * contact for theme
     *
     * @ORM\Column(type="string", length=255)
     */
    private $contact;

    /**
     * is theme an admin capable theme
     *
     * @ORM\Column(type="smallint")
     */
    private $admin;

    /**
     * is theme an user capable theme
     *
     * @ORM\Column(type="smallint")
     */
    private $user;

    /**
     * is theme an system theme
     *
     * @ORM\Column(type="smallint")
     */
    private $system;

    /**
     * state of the theme
     *
     * @ORM\Column(type="smallint")
     */
    private $state;

    /**
     * is theme xhtml compliant
     *
     * @ORM\Column(type="smallint")
     */
    private $xhtml;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name = '';
        $this->type = 0;
        $this->displayname = '';
        $this->description = '';
        $this->directory = '';
        $this->version = '0.0';
        $this->contact = '';
        $this->admin = 0;
        $this->user = 0;
        $this->system = 0;
        $this->state = ThemeEntityRepository::STATE_INACTIVE;
        $this->xhtml = 1;
    }

    /**
     * get the id of the theme
     *
     * @return integer the theme's id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set the id for the theme
     *
     * @param integer $id the theme's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * get the name of the theme
     *
     * @return string the theme's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set the name for the theme
     *
     * @param string $name the theme's name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get the type of the theme
     *
     * @return integer the theme's type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * set the type for the theme
     *
     * @param integer $type the theme's type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * get the displayname of the theme
     *
     * @return string the theme's displayname
     */
    public function getDisplayname()
    {
        return $this->displayname;
    }

    /**
     * set the displayname for the theme
     *
     * @param string $displayname the theme's displayname
     */
    public function setDisplayname($displayname)
    {
        $this->displayname = $displayname;
    }

    /**
     * get the description of the theme
     *
     * @return string the theme's description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set the description for the theme
     *
     * @param string $description the theme's description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * get the directory of the theme
     *
     * @return string the theme's directory
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * set the directory for the theme
     *
     * @param string $directory the theme's directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * get the version of the theme
     *
     * @return string the theme's version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * set the version for the theme
     *
     * @param string $version the theme's version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * get the contact of the theme
     *
     * @return string the theme's contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * set the contact for the theme
     *
     * @param string $contact the theme's contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * get the admin of the theme
     *
     * @return integer the theme's admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * set the admin for the theme
     *
     * @param integer $admin the theme's admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * get the user of the theme
     *
     * @return integer the theme's user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * set the user for the theme
     *
     * @param integer $user the theme's user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * get the system of the theme
     *
     * @return integer the theme's system
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * set the system for the theme
     *
     * @param integer $system the theme's system
     */
    public function setSystem($system)
    {
        $this->system = $system;
    }

    /**
     * get the state of the theme
     *
     * @return integer the theme's state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * set the state for the theme
     *
     * @param integer $state the theme's state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * get the xhtml of the theme
     *
     * @return integer the theme's xhtml
     */
    public function getXhtml()
    {
        return $this->xhtml;
    }

    /**
     * set the xhtml for the theme
     *
     * @param integer $xhtml the theme's xhtml
     */
    public function setXhtml($xhtml)
    {
        $this->xhtml = $xhtml;
    }
}
