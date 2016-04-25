<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Tests\Api\Fixtures;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;

class HookContainer extends AbstractHookContainer
{
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle('ZikulaBlocksModule', 'foo.area', 'ui_hooks', $this->__('Translatable title'));
        $bundle->addEvent('form_edit', 'foo.event.name');
        $this->registerHookSubscriberBundle($bundle);
    }
}
