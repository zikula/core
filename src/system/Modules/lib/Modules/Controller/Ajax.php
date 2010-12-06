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
     * togglesubscriberstatus
     * This function toggles attached/detached status of subscribers
     *
     * @param subscriber string module to be attached/detached
     * @param provider string   module to attach/detach
     * @return mixed            Ajax response
     */
    public function togglesubscriberstatus()
    {
        $provider = FormUtil::getPassedValue('provider', '', 'GET');
        if (empty($provider)) {
            throw new Zikula_Exception_Fatal($this->__('No provider module passed.'));
        }
        if (!ModUtil::available($provider)) {
            throw new Zikula_Exception_Fatal($this->__f('Provider module "%s" is not available.', $provider));
        }

        if (!SecurityUtil::checkPermission($provider.'::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $subscriber = FormUtil::getPassedValue('subscriber', '', 'GET');
        if (empty($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__('No subscriber module passed.'));
        }
        if (!ModUtil::available($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }

        if ($subscriber == $provider) {
            throw new Zikula_Exception_Fatal($this->__f("%s can't be attached to itself.", $subscriber));
        }
        
        // find out if subscriber is already connected to provider
        $bindings = HookUtil::bindingsBetweenProviderAndSubscriber($subscriber, $provider);
        
        // if number of bindings are greated than 0, than means
        // that subscriber is already connected to provider
        if (count($bindings) > 0) {
            foreach ($bindings as $binding) {
                HookUtil::unbindSubscribersFromProvider($binding['subarea'], $binding['providerarea']);
            }
        } else {
            // find out areas for provider module
            $providerVersion = $provider.'_Version';
            $providerModule = new $providerVersion;
            $providerBundles = $providerModule->getHookProviderBundles();
            $providerAreas = array();
            foreach ($providerBundles as $area => $hookproviderbundle) {
                $providerAreas[] = $area;
            }

            // find out areas for subscriber module
            $subscriberVersion = $subscriber.'_Version';
            $subscriberModule = new $subscriberVersion;
            $subscriberBundles = $subscriberModule->getHookSubscriberBundles();
            $subscriberAreas = array();
            foreach ($subscriberBundles as $area => $hooksubscriberbundle) {
                $subscriberAreas[] = $area;
            }

            // bind subscriber to provider
            foreach ($subscriberAreas as $sarea) {
                foreach ($providerAreas as $parea) {
                    HookUtil::bindSubscribersToProvider($sarea, $parea);
                }
            }
        }

        return new Zikula_Response_Ajax(array('id' => ModUtil::getIdFromName($subscriber)));
    }

    /**
     * changeproviderorder
     *
     * @param subscriber string     name of the subscriber
     * @param providerorder array   array of sorted provider ids
     * @return Ajax response
     */
    public function changeproviderorder()
    {
        $subscriber = FormUtil::getPassedValue('subscriber', '', 'GET');
        if (empty($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__('No subscriber module passed.'));
        }
        if (!ModUtil::available($subscriber)) {
            throw new Zikula_Exception_Fatal($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }

        if (!SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $providersorder = FormUtil::getPassedValue('providersorder');
        if (!(is_array($providersorder) && count($providersorder) > 0)) {
            throw new Zikula_Exception_Fatal($this->__('Providers order is not an array.'));
        }

        $areaSorts = array();
        foreach ((array)$providersorder as $order => $id) {
            $providerModule = ModUtil::getInfo($id);
            $bindings = HookUtil::bindingsBetweenProviderAndSubscriber($subscriber, $providerModule['name']);
            foreach ($bindings as $binding) {
                if (!isset($areaSorts[$binding['subarea']])) {
                    $areaSorts[$binding['subarea']] = array();
                }

                $areaSorts[$binding['subarea']][] = $binding['providerarea'];
            }
        }

        foreach($areaSorts as $area => $sort) {
            HookUtil::setDisplaySortsByArea($area, $sort);
        }

        return new Zikula_Response_Ajax(array('result' => true));
    }
}