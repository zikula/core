<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace ThemeModule\Controller;

use Zikula\Core\Event\GenericEvent;

class AjaxController extends \Zikula\Framework\Controller\AbstractAjax
{
    public function dispatchAction()
    {
        $event = $this->dispatcher->dispatch('theme.ajax_request', new GenericEvent());
        $this->throwNotFoundUnless($event->isPropagationStopped(), $this->__('No event handlers responded.'));

        return $event->getData();
    }
}