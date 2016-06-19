<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule {

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;
    use Zikula\UsersModule\DependencyInjection\Compiler\AuthenticationMethodCollectorPass;

    /**
     * Base module definition for the users module
     */
    class ZikulaUsersModule extends AbstractCoreModule
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);

            $container->addCompilerPass(new AuthenticationMethodCollectorPass());
        }
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
     * hash method helpers
     *
     * @deprecated since 1.4.0 use \Zikula\UsersModule\Helper\HashMethodListHelper instead
     */
    class Users_Helper_HasMethodList extends \Zikula\UsersModule\Helper\HashMethodListHelper
    {
    }
}
