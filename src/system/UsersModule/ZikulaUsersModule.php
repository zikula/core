<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule {
    use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;

    /**
     * Base module definition for the users module
     */
    class ZikulaUsersModule extends AbstractCoreModule
    {
    }
}

/**
 * BC layer - remove at Core-2.0
 */

namespace {
    /**
     * constants for the users module
     *
     * @deprecated since 1.4.0 use Zikula\UsersModule\Constant instead
     */
    class Users_Constant extends Zikula\UsersModule\Constant
    {
    }

    /**
     * authentication method helper
     *
     * @deprecated since 1.4.0 use \Zikula\UsersModule\Helper\AuthenticationMethodHelper instead
     */
    class Users_Helper_AuthenticationMethod extends \Zikula\UsersModule\Helper\AuthenticationMethodHelper
    {
    }

    /**
     * list authentication helpers
     *
     * @deprecated since 1.4.0 use \Zikula\UsersModule\Helper\AuthenticationMethodListHelper instead
     */
    class Users_Helper_AuthenticationMethodList extends \Zikula\UsersModule\Helper\AuthenticationMethodListHelper
    {
    }

    /**
     * hash method helpers
     *
     * @deprecated since 1.4.0 use \Zikula\UsersModule\Helper\HashMethodListHelper instead
     */
    class Users_Helper_HasMethodList extends \Zikula\UsersModule\Helper\HashMethodListHelper
    {
    }
}
