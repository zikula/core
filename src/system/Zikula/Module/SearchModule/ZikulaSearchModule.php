<?php

namespace Zikula\Module\SearchModule {

    use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;

    class ZikulaSearchModule extends AbstractCoreModule
    {
    }
}

/**
 * Classes for modules prior to 1.3.6
 */
namespace {
    class Search_Api_User extends \Zikula\Module\SearchModule\Api\UserApi
    {
    }
}