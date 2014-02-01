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

/**
 * Base module definition for the search module
 */
namespace Zikula\Module\SearchModule {

    use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;

    /**
     * Base module definition for the search module
     */
    class ZikulaSearchModule extends AbstractCoreModule
    {
    }
}

/**
 * Classes for modules prior to 1.3.7
 */
namespace {
    /**
     * user api functions for the search module
     *
     * @deprecated since 1.3.7 use \Zikula\Module\SearchModule\Api\UserApi instead
     */
    class Search_Api_User extends \Zikula\Module\SearchModule\Api\UserApi
    {
    }
}