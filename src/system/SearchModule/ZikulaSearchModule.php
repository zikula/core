<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base module definition for the search module
 */

namespace Zikula\SearchModule {

    use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;

    /**
     * Base module definition for the search module
     */
    class ZikulaSearchModule extends AbstractCoreModule
    {
    }
}

/**
 * Classes for modules prior to 1.4.0
 */

namespace {
    /**
     * user api functions for the search module
     *
     * @deprecated since 1.4.0 use \Zikula\SearchModule\Api\UserApi instead
     */
    class Search_Api_User extends \Zikula\SearchModule\Api\UserApi
    {
    }
}

namespace Zikula\Module\SearchModule {

    use Zikula\SearchModule\AbstractSearchable as AbstractSearchableActual;

    /**
     * @deprecated remove at Core-2.0
     * @see Zikula\SearchModule\AbstractSearchable
     *
     * This class is necessary because of the refactoring of the SearchModule to psr-4
     * This class maintains the 1.4.x BC API
     *
     * Class AbstractSearchable
     */
    abstract class AbstractSearchable extends AbstractSearchableActual
    {
    }
}
