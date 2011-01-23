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

class Modules_Controller_Ajax extends Zikula_Controller
{
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
    }
    
    /**
     * togglesubscriberareastatus
     * This function attaches/detaches a subscriber area to a provider area
     *
     * @param subscriberarea string area to be attached/detached
     * @param providerarea   string area to attach/detach
     * @return mixed         Ajax response
     */
    public function togglesubscriberareastatus()
    {
        // get subscriberarea from GET
        $subscriberarea = FormUtil::getPassedValue('subscriberarea', '', 'GET');
        if (empty($subscriberarea)) {
            throw new Zikula_Exception_Fatal($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = HookUtil::getOwnerBySubscriberArea($subscriberarea);
        if (empty($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!ModUtil::available($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        if (!SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        // get providerarea from GET
        $providerarea = FormUtil::getPassedValue('providerarea', '', 'GET');
        if (empty($providerarea)) {
            throw new Zikula_Exception_Fatal($this->__('No provider area passed.'));
        }

        // get provider module based on area and do some checks
        $provider = HookUtil::getOwnerByProviderArea($providerarea);
        if (empty($provider)) {
            throw new Zikula_Exception_Fatal($this->__f('Module "%s" is not a valid provider.', $provider));
        }
        if (!ModUtil::available($provider)) {
            throw new Zikula_Exception_Fatal($this->__f('Provider module "%s" is not available.', $provider));
        }
        if (!SecurityUtil::checkPermission($provider.'::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        // check if binding between areas exists
        $binding = HookUtil::bindingBetweenAreas($subscriberarea, $providerarea);
        
        if (!$binding) {
            HookUtil::bindSubscribersToProvider($subscriberarea, $providerarea);
        } else {
            HookUtil::unbindSubscribersFromProvider($subscriberarea, $providerarea);
        }
        
        return new Zikula_Response_Ajax(array('result' => true));
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
        // get subscriberarea from GET
        $subscriberarea = FormUtil::getPassedValue('subscriberarea', '', 'GET');
        if (empty($subscriberarea)) {
            throw new Zikula_Exception_Fatal($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = HookUtil::getOwnerBySubscriberArea($subscriberarea);
        if (empty($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!ModUtil::available($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        if (!SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        // get providers' areas from GET
        $providerarea = FormUtil::getPassedValue('providerarea');
        if (!(is_array($providerarea) && count($providerarea) > 0)) {
            throw new Zikula_Exception_Fatal($this->__('Providers\' areas order is not an array.'));
        }
        
        // set sorting
        HookUtil::setDisplaySortsByArea($subscriberarea, $providerarea);

        $ol_id = FormUtil::getPassedValue('ol_id', '', 'GET');
       
        return new Zikula_Response_Ajax(array('result' => true, 'ol_id' => $ol_id));
    }
}