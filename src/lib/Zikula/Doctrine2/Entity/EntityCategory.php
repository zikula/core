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
 * Base class of many-to-many assocation between any entity and Category.
 *
 * @ORM\MappedSuperclass
 */
abstract class Zikula_Doctrine2_Entity_EntityCategory extends Zikula\Core\Doctrine\Entity\AbstractEntityCategory
{
}

