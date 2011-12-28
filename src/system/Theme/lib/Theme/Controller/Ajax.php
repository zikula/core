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

use Zikula\Core\Event\GenericEvent;

class Theme_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    public function dispatch()
    {
        $event = new GenericEvent("theme.ajax_request");
        $this->eventManager->notify($event);
        $this->throwNotFoundUnless($event->isPropagationStopped(), $this->__('No event handlers responded.'));
        return $event->getData();
    }
}