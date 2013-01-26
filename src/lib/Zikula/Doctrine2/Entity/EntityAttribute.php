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
use Zikula\Core\Doctrine\Entity\AbstractEntityAttribute;

/**
 * Base class of many-to-many association between any entity and attribute.
 *
 * @ORM\MappedSuperclass
 *
 * @deprecated since 1.3.6
 */
abstract class Zikula_Doctrine2_Entity_EntityAttribute extends AbstractEntityAttribute
{

}

