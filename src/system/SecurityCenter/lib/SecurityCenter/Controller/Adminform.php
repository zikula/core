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


class SecurityCenter_Controller_Adminform extends Zikula_AbstractController
{
    /**
     * Initialise.
     *
     * @return void
     */
    protected function initialize()
    {
        // Do not setup a view pfor this controller.
    }

    /**
     * Function to delete an ids log entry
     */
    public function deleteidsentry()
    {
        // verify auth-key
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('SecurityCenter::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // get paramters
        $id = (int)FormUtil::getPassedValue('id', 0, 'GETPOST');

        // sanity check
        if (!is_numeric($id)) {
            return LogUtil::registerError($this->__f("Error! Received a non-numeric object ID '%s'.", $id));
        }

        $class = 'SecurityCenter_DBObject_Intrusion';
        $object = new $class();
        $data = $object->get($id);

        // check for valid object
        if (!$data) {
            return LogUtil::registerError($this->__f('Error! Invalid %s received.', "object ID [$id]"));
        } else {
            // delete object
            $object->delete();
        }

        // redirect back to view function
        $this->redirect(ModUtil::url('SecurityCenter', 'admin', 'viewidslog'));
    }
}
