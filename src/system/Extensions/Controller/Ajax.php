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

class Extensions_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * togglesubscriberareastatus
     * This function attaches/detaches a subscriber area to a provider area
     *
     * @param subscriberarea string area to be attached/detached
     * @param providerarea   string area to attach/detach
     * @return mixed Ajax response
     */
    public function togglesubscriberareastatus()
    {
        $this->checkAjaxToken();

        // get subscriberarea from POST
        $subscriberArea = $this->request->request->get('subscriberarea','');
        if (empty($subscriberArea)) {
            throw new Zikula_Exception_Fatal($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = HookUtil::getOwnerByArea($subscriberArea);
        if (empty($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!ModUtil::available($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        $this->throwForbiddenUnless(SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN));

        // get providerarea from POST
        $providerArea = $this->request->request->get('providerarea','');
        if (empty($providerArea)) {
            throw new Zikula_Exception_Fatal($this->__('No provider area passed.'));
        }

        // get provider module based on area and do some checks
        $provider = HookUtil::getOwnerByArea($providerArea);
        if (empty($provider)) {
            throw new Zikula_Exception_Fatal($this->__f('Module "%s" is not a valid provider.', $provider));
        }
        if (!ModUtil::available($provider)) {
            throw new Zikula_Exception_Fatal($this->__f('Provider module "%s" is not available.', $provider));
        }
        $this->throwForbiddenUnless(SecurityUtil::checkPermission($provider.'::', '::', ACCESS_ADMIN));

        // get hookmanager
        $hookManager = $this->serviceManager->getService('zikula.hookmanager');

        // check if binding between areas exists
        $binding = HookUtil::getBindingBetweenAreas($subscriberArea, $providerArea);
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

        return new Zikula_Response_Ajax($response);
    }

    /**
     * changeproviderareaorder
     * This function changes the order of the providers' areas that are attached to a subscriber
     *
     * @param subscriber string     name of the subscriber
     * @param providerorder array   array of sorted provider ids
     * @return Ajax response
     */
    public function changeproviderareaorder()
    {
        $this->checkAjaxToken();

        // get subscriberarea from POST
        $subscriberarea = $this->request->request->get('subscriberarea','');
        if (empty($subscriberarea)) {
            throw new Zikula_Exception_Fatal($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = HookUtil::getOwnerByArea($subscriberarea);
        if (empty($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!ModUtil::available($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        $this->throwForbiddenUnless(SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN));

        // get providers' areas from POST
        $providerarea = $this->request->request->get('providerarea','');
        if (!(is_array($providerarea) && count($providerarea) > 0)) {
            throw new Zikula_Exception_Fatal($this->__('Providers\' areas order is not an array.'));
        }

        // set sorting
        HookUtil::setBindOrder($subscriberarea, $providerarea);

        $ol_id = $this->request->request->get('ol_id','');

        return new Zikula_Response_Ajax(array('result' => true, 'ol_id' => $ol_id));
    }
}
