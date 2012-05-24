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
 * CategoryAttribute doctrine2 entity.
 *
 * @ORM\Entity
 */
class Zikula_Doctrine2_Entity_CategoryAttribute extends Zikula_Doctrine2_Entity_AbstractAttribute
{
    /**
     * @ORM\ManyToOne(targetEntity="Zikula_Doctrine2_Entity_Category", inversedBy="attributes")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * @var integer
     */
    private $objectId;

    public function __construct($objectId, $objectStatus, $name, $value)
    {
        parent::__construct($objectId, 'categories_category', $objectStatus, $name, $value);
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

}
