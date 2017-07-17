<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

/**
 * @deprecated Remove at Core-3.0
 * Class MockHookApi
 * This class only exists to prevent errors where an Installer class tries to call a method like
 * `$this->hookApi->uninstallSubscriberHooks()`. This method call is no longer required because the tables it populated
 * have been removed. But a module author may unknowingly leave them in the installer.
 */
class MockHookApi
{
    public function __call($name, $arguments)
    {
        // intentionally do nothing
        trigger_error('All methods from HookApi are no longer needed. They should be completely removed from the Installer class.');
    }
}
