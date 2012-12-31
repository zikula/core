<?php

namespace Zikula\Component\HookDispatcher\Storage\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HookBinding
 *
 * @ORM\Table(name="hook_binding")
 * @ORM\Entity
 */
class HookBindingEntity
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
     * @var string $sowner
     *
     * @ORM\Column(name="sowner", type="string", length=40, nullable=false)
     */
    private $sowner;

    /**
     * @var string $subsowner
     *
     * @ORM\Column(name="subsowner", type="string", length=40, nullable=true)
     */
    private $subsowner;

    /**
     * @var string $powner
     *
     * @ORM\Column(name="powner", type="string", length=40, nullable=false)
     */
    private $powner;

    /**
     * @var string $subpowner
     *
     * @ORM\Column(name="subpowner", type="string", length=40, nullable=true)
     */
    private $subpowner;

    /**
     * @var integer $sareaid
     *
     * @ORM\Column(name="sareaid", type="integer", nullable=false)
     */
    private $sareaid;

    /**
     * @var integer $pareaid
     *
     * @ORM\Column(name="pareaid", type="integer", nullable=false)
     */
    private $pareaid;

    /**
     * @var string $category
     *
     * @ORM\Column(name="category", type="string", length=20, nullable=false)
     */
    private $category;

    /**
     * @var smallint $sortorder
     *
     * @ORM\Column(name="sortorder", type="smallint", nullable=false)
     */
    private $sortorder;


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
     * Set sowner
     *
     * @param string $sowner
     * @return HookBindingEntity
     */
    public function setSowner($sowner)
    {
        $this->sowner = $sowner;
        return $this;
    }

    /**
     * Get sowner
     *
     * @return string
     */
    public function getSowner()
    {
        return $this->sowner;
    }

    /**
     * Set subsowner
     *
     * @param string $subsowner
     * @return HookBindingEntity
     */
    public function setSubsowner($subsowner)
    {
        $this->subsowner = $subsowner;
        return $this;
    }

    /**
     * Get subsowner
     *
     * @return string
     */
    public function getSubsowner()
    {
        return $this->subsowner;
    }

    /**
     * Set powner
     *
     * @param string $powner
     * @return HookBindingEntity
     */
    public function setPowner($powner)
    {
        $this->powner = $powner;
        return $this;
    }

    /**
     * Get powner
     *
     * @return string
     */
    public function getPowner()
    {
        return $this->powner;
    }

    /**
     * Set subpowner
     *
     * @param string $subpowner
     * @return HookBindingEntity
     */
    public function setSubpowner($subpowner)
    {
        $this->subpowner = $subpowner;
        return $this;
    }

    /**
     * Get subpowner
     *
     * @return string
     */
    public function getSubpowner()
    {
        return $this->subpowner;
    }

    /**
     * Set sareaid
     *
     * @param integer $sareaid
     * @return HookBindingEntity
     */
    public function setSareaid($sareaid)
    {
        $this->sareaid = $sareaid;
        return $this;
    }

    /**
     * Get sareaid
     *
     * @return integer
     */
    public function getSareaid()
    {
        return $this->sareaid;
    }

    /**
     * Set pareaid
     *
     * @param integer $pareaid
     * @return HookBindingEntity
     */
    public function setPareaid($pareaid)
    {
        $this->pareaid = $pareaid;
        return $this;
    }

    /**
     * Get pareaid
     *
     * @return integer
     */
    public function getPareaid()
    {
        return $this->pareaid;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return HookBindingEntity
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set sortorder
     *
     * @param smallint $sortorder
     * @return HookBindingEntity
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;
        return $this;
    }

    /**
     * Get sortorder
     *
     * @return smallint
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }
}