<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ThemeModule\Controller;

use Zikula\Core\Event\GenericEvent;

/**
 * Ajax controllers for the theme module
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * dispatch a theme.ajax_request event
     *
     * @return mixed results of the event request
     */
    public function dispatchAction()
    {
        $event = $this->getDispatcher()->dispatch('theme.ajax_request', new GenericEvent());
        $this->throwNotFoundUnless($event->isPropagationStopped(), $this->__('No event handlers responded.'));

        return $event->getData();
    }
}