<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Tests\Api\Fixtures;

use Zikula\Component\HookDispatcher\AbstractContainer;
use Zikula\Component\HookDispatcher\SubscriberBundle;

class HookContainer extends AbstractContainer
{
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle('ZikulaBlocksModule', 'foo.area', 'ui_hooks', $this->__('Translatable title'));
        $bundle->addEvent('form_edit', 'foo.event.name');
        $this->registerHookSubscriberBundle($bundle);
    }
}