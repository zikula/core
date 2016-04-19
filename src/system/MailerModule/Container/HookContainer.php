<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\MailerModule\Container\Base;

use Zikula\Bundle\HookBundle\AbstractHookContainer;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;

/**
 * Class for hook container methods.
 */
class HookContainer extends AbstractHookContainer
{
    /**
     * Define the hook bundles supported by this module.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
        // This enables Scribite 5 connection to HTML e-mail test
        $bundle = new SubscriberBundle('ZikulaMailerModule', 'subscriber.mailer.ui_hooks.htmlmail', 'ui_hooks', $this->__('HTML mail hook'));
        $bundle->addEvent('form_edit', 'mailer.ui_hooks.htmlmail.form_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}
