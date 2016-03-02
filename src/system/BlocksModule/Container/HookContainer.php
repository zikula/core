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

namespace Zikula\BlocksModule\Container;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;

class HookContainer extends AbstractHookContainer
{
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle('ZikulaBlocksModule', 'subscriber.blocks.ui_hooks.htmlblock.content', 'ui_hooks', $this->__('HTML Block content hook'));
        $bundle->addEvent('form_edit', 'blocks.ui_hooks.htmlblock.content.form_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}
