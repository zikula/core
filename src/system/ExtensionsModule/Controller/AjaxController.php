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

namespace Zikula\ExtensionsModule\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use HookUtil;
use ModUtil;
use SecurityUtil;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the extensions module
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * @Route("/togglestatus", options={"expose"=true})
     * @Method("POST")
     * 
     * Attach/detach a subscriber area to a provider area
     *
     * @param Request $request
     * 
     *  subscriberarea string area to be attached/detached
     *  providerarea   string area to attach/detach
     *
     * @return AjaxResponse
     *
     * @throws \InvalidArgumentException Thrown if either the subscriber, provider or subscriberArea parameters are empty
     * @throws \RuntimeException Thrown if either the subscriber or provider module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to either the subscriber or provider modules
     */
    public function togglesubscriberareastatusAction(Request $request)
    {
        // get hookmanager
        /** @var $hookManager \Zikula\Component\HookDispatcher\StorageInterface */
        $hookManager = $this->serviceManager->get('hook_dispatcher');

        $this->checkAjaxToken();

        // get subscriberarea from POST
        $subscriberArea = $request->request->get('subscriberarea', '');
        if (empty($subscriberArea)) {
            throw new \InvalidArgumentException($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $hookManager->getOwnerByArea($subscriberArea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        if (!SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providerarea from POST
        $providerArea = $request->request->get('providerarea', '');
        if (empty($providerArea)) {
            throw new \InvalidArgumentException($this->__('No provider area passed.'));
        }

        // get provider module based on area and do some checks
        $provider = $hookManager->getOwnerByArea($providerArea);
        if (empty($provider)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid provider.', $provider));
        }
        if (!ModUtil::available($provider)) {
            throw new \RuntimeException($this->__f('Provider module "%s" is not available.', $provider));
        }
        if (!SecurityUtil::checkPermission($provider.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check if binding between areas exists
        $binding = $hookManager->getBindingBetweenAreas($subscriberArea, $providerArea);
        if (!$binding) {
            $hookManager->bindSubscriber($subscriberArea, $providerArea);
        } else {
            $hookManager->unbindSubscriber($subscriberArea, $providerArea);
        }

        // ajax response
        $response = array(
            'result' => true,
            'action' => $binding ? 'unbind' : 'bind',
            'subscriberarea' => $subscriberArea,
            'subscriberarea_id' => md5($subscriberArea),
            'providerarea' => $providerArea,
            'providerarea_id' => md5($providerArea),
            'isSubscriberSelfCapable' => (HookUtil::isSubscriberSelfCapable($subscriber) ? true : false)
        );

        return new AjaxResponse($response);
    }

    /**
     * @Route("/changeorder", options={"expose"=true})
     * @Method("POST")
     *
     * changeproviderareaorder
     * This function changes the order of the providers' areas that are attached to a subscriber
     *
     * @param Request $request
     *
     *  subscriber    string     name of the subscriber
     *  providerorder array      array of sorted provider ids
     *
     * @return AjaxResponse
     *
     * @throws \InvalidArgumentException Thrown if the subscriber or subscriberarea parameters aren't valid
     * @throws \RuntimeException Thrown if the subscriber module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the subscriber module
     */
    public function changeproviderareaorderAction(Request $request)
    {
        $this->checkAjaxToken();

        // get subscriberarea from POST
        $subscriberarea = $request->request->get('subscriberarea', '');
        if (empty($subscriberarea)) {
            throw new \InvalidArgumentException($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = HookUtil::getOwnerByArea($subscriberarea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        if (!SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providers' areas from POST
        $providerarea = $request->request->get('providerarea', '');
        if (!(is_array($providerarea) && count($providerarea) > 0)) {
            throw new \InvalidArgumentException($this->__('Providers\' areas order is not an array.'));
        }

        // set sorting
        HookUtil::setBindOrder($subscriberarea, $providerarea);

        $ol_id = $request->request->get('ol_id', '');

        return new AjaxResponse(array('result' => true, 'ol_id' => $ol_id));
    }
}
