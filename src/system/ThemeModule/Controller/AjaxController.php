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

namespace Zikula\ThemeModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\Ajax\AjaxResponse;

/**
 * @deprecated at Core-2.0 This feature will not be available in Core-2.0
 * @Route("/ajax")
 *
 * Ajax controllers for the theme module
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/dispatch", options={"expose"=true})
     *
     * dispatch a theme.ajax_request event
     *
     * @return mixed results of the event request
     *
     * @throws NotFoundHttpException Thrown if the no event handlers responsed to the event
     */
    public function dispatchAction()
    {
        $event = $this->get('event_dispatcher')->dispatch('theme.ajax_request', new GenericEvent());
        if (!$event->isPropagationStopped()) {
            throw new NotFoundHttpException($this->__('No event handlers responded.'));
        }

        return new AjaxResponse($event->getData());
    }
}
