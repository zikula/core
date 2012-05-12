<?php


namespace Zikula\Core\Doctrine\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Workflows
 *
 * @ORM\Table(name="workflows")
 * @ORM\Entity
 */
class Workflows
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
     * @var integer $metaid
     *
     * @ORM\Column(name="metaid", type="integer", nullable=false)
     */
    private $metaid;

    /**
     * @var string $module
     *
     * @ORM\Column(name="module", type="string", length=255, nullable=false)
     */
    private $module;

    /**
     * @var string $schemaname
     *
     * @ORM\Column(name="schemaname", type="string", length=255, nullable=false)
     */
    private $schemaname;

    /**
     * @var string $state
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    private $state;

    /**
     * @var smallint $type
     *
     * @ORM\Column(name="type", type="smallint", nullable=false)
     */
    private $type;

    /**
     * @var string $objTable
     *
     * @ORM\Column(name="obj_table", type="string", length=40, nullable=false)
     */
    private $objTable;

    /**
     * @var string $objIdcolumn
     *
     * @ORM\Column(name="obj_idcolumn", type="string", length=40, nullable=false)
     */
    private $objIdcolumn;

    /**
     * @var integer $objId
     *
     * @ORM\Column(name="obj_id", type="integer", nullable=false)
     */
    private $objId;

    /**
     * @var integer $busy
     *
     * @ORM\Column(name="busy", type="integer", nullable=false)
     */
    private $busy;

    /**
     * @var text $debug
     *
     * @ORM\Column(name="debug", type="text", nullable=true)
     */
    private $debug;


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
     * Set metaid
     *
     * @param integer $metaid
     * @return Workflows
     */
    public function setMetaid($metaid)
    {
        $this->metaid = $metaid;
        return $this;
    }

    /**
     * Get metaid
     *
     * @return integer 
     */
    public function getMetaid()
    {
        return $this->metaid;
    }

    /**
     * Set module
     *
     * @param string $module
     * @return Workflows
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
     * Set schemaname
     *
     * @param string $schemaname
     * @return Workflows
     */
    public function setSchemaname($schemaname)
    {
        $this->schemaname = $schemaname;
        return $this;
    }

    /**
     * Get schemaname
     *
     * @return string 
     */
    public function getSchemaname()
    {
        return $this->schemaname;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Workflows
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set type
     *
     * @param smallint $type
     * @return Workflows
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return smallint 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set objTable
     *
     * @param string $objTable
     * @return Workflows
     */
    public function setObjTable($objTable)
    {
        $this->objTable = $objTable;
        return $this;
    }

    /**
     * Get objTable
     *
     * @return string 
     */
    public function getObjTable()
    {
        return $this->objTable;
    }

    /**
     * Set objIdcolumn
     *
     * @param string $objIdcolumn
     * @return Workflows
     */
    public function setObjIdcolumn($objIdcolumn)
    {
        $this->objIdcolumn = $objIdcolumn;
        return $this;
    }

    /**
     * Get objIdcolumn
     *
     * @return string 
     */
    public function getObjIdcolumn()
    {
        return $this->objIdcolumn;
    }

    /**
     * Set objId
     *
     * @param integer $objId
     * @return Workflows
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;
        return $this;
    }

    /**
     * Get objId
     *
     * @return integer 
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set busy
     *
     * @param integer $busy
     * @return Workflows
     */
    public function setBusy($busy)
    {
        $this->busy = $busy;
        return $this;
    }

    /**
     * Get busy
     *
     * @return integer 
     */
    public function getBusy()
    {
        return $this->busy;
    }

    /**
     * Set debug
     *
     * @param text $debug
     * @return Workflows
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Get debug
     *
     * @return text 
     */
    public function getDebug()
    {
        return $this->debug;
    }
}