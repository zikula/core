<?php


namespace Zikula\Module\UsersModule {
    use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;

    class ZikulaUsersModule extends AbstractCoreModule
    {
    }
}

/**
 * BC layer till for <=1.3.5
 */
namespace {
    class Users_Constant extends Zikula\Module\UsersModule\Constant
    {
    }

    class Users_Helper_AuthenticationMethod extends \Zikula\Module\UsersModule\Helper\AuthenticationMethodHelper
    {
    }

    class Users_Helper_AuthenticationMethodList extends \Zikula\Module\UsersModule\Helper\AuthenticationMethodListHelper
    {
    }

    class Users_Helper_HasMethodList extends \Zikula\Module\UsersModule\Helper\HashMethodListHelper
    {
    }
}