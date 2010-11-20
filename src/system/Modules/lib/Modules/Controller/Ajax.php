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
     * @param id int            id of module to be attached/detached
     * @param provider string   module to attach/detach
     * @return mixed            Ajax response
     */
    public function togglesubscriberstatus()
    {
        $provider = FormUtil::getPassedValue('provider', '', 'GET');
        
        if (!SecurityUtil::checkPermission($provider.'::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $id = FormUtil::getPassedValue('id', -1, 'GET');
        if ($id == -1) {
            throw new Zikula_Exception_Fatal($this->__('No module ID passed.'));
        }

        // check if provider is attached to module and act accordingly
        /* TODO */

        return new Zikula_Response_Ajax(array('id' => $id));
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

        if (!SecurityUtil::checkPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $providerorder = FormUtil::getPassedValue('providerorder');

        $ordering = array();
        foreach ((array)$providerorder as $order => $id) {
            $ordering[] = array('id' => $id, 'order' => $order);
        }

        // update ordering status
        /* TODO */

        return new Zikula_Response_Ajax(array('result' => true));
    }
}