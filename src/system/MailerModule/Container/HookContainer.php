<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Container;

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
