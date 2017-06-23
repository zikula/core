<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Container;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;

class HookContainer extends AbstractHookContainer
{
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle('ZikulaBlocksModule', 'subscriber.blocks.ui_hooks.htmlblock.content', UiHooksCategory::NAME, $this->__('HTML Block content hook'));
        $bundle->addEvent(UiHooksCategory::TYPE_FORM_EDIT, 'blocks.ui_hooks.htmlblock.content.form_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}
