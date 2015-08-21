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

namespace Zikula\CategoriesModule {

    use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;

    /**
     * Base module definition for the categories module
     */
    class ZikulaCategoriesModule extends AbstractCoreModule
    {
    }
}

namespace Zikula\Module\CategoriesModule\Entity {

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @deprecated remove at Core-2.0
     * @see Zikula\CategoriesModule\Entity\CategoryEntity
     *
     * This class is necessary because of the refactoring to psr-4
     * This class maintains the 1.4.x BC API

     * Class CategoryEntity
     * @package Zikula\Module\CategoriesModule\Entity
     *
     * @ORM\Entity
     * @ORM\Table(name="categories_category",indexes={@ORM\Index(name="idx_categories_is_leaf",columns={"is_leaf"}),
     *                                                @ORM\Index(name="idx_categories_name",columns={"name"}),
     *                                                @ORM\Index(name="idx_categories_ipath",columns={"ipath","is_leaf","status"}),
     *                                                @ORM\Index(name="idx_categories_status",columns={"status"}),
     *                                                @ORM\Index(name="idx_categories_ipath_status",columns={"ipath","status"})})
     */
    class CategoryEntity extends \Zikula\CategoriesModule\Entity\CategoryEntity
    {
    }
}