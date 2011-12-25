<?php

/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Framework;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AjaxModuleDispatcher
{
    public function dispatch()
    {
        // Get variables
        $module = \FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
        $type = \FormUtil::getPassedValue('type', 'ajax', 'GETPOST', FILTER_SANITIZE_STRING);
        $func = \FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

        // Check for site closed
        if (\System::getVar('siteoff') && !\SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin')) {
            if (\SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && \UserUtil::isLoggedIn()) {
                \UserUtil::logout();
            }
            $response = new \Zikula_Response_Ajax_Unavailable(__('The site is currently off-line.'));
        }

        if (empty($func)) {
            $response = new \Zikula_Response_Ajax_NotFound(__f("Missing parameter '%s'", 'func'));
        }

        // get module information
        $modinfo = \ModUtil::getInfoFromName($module);
        if ($modinfo == false) {
            $response = new \Zikula_Response_Ajax_NotFound(__f("Error! The '%s' module is unknown.", \DataUtil::formatForDisplay($module)));
        }

        if (!\ModUtil::available($modinfo['name'])) {
            $response = new \Zikula_Response_Ajax_NotFound(__f("Error! The '%s' module is not available.", \DataUtil::formatForDisplay($module)));
        }

        if (!\ModUtil::load($modinfo['name'], $type)) {
            $response = new \Zikula_Response_Ajax_NotFound(__f("Error! The '%s' module is not available.", \DataUtil::formatForDisplay($module)));
        }

        // Handle database transactions
        if (\System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
            $dbConn = \Doctrine_Manager::getInstance()->getCurrentConnection();
            $dbConn->beginTransaction();
        }

        // Dispatch controller.
        try {
            $response = \ModUtil::func($modinfo['name'], $type, $func);
        } catch (\Zikula_Exception_NotFound $e) {
            $response = new \Zikula_Response_Ajax_NotFound($e->getMessage());
        } catch (\Zikula_Exception_Forbidden $e) {
            $response = new \Zikula_Response_Ajax_Forbidden($e->getMessage());
        } catch (\Zikula_Exception_Fatal $e) {
            $response = new Zikula_Response_Ajax_Fatal($e->getMessage());
        } catch (\PDOException $e) {
            $response = new \Zikula_Response_Ajax_Fatal($e->getMessage());
        } catch (\Exception $e) {
            $response = new \Zikula_Response_Ajax_Fatal($e->getMessage());
        }

        // Handle database transactions
        if (\System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
            if (isset($e) && $e instanceof \Exception) {
                $dbConn->rollback();
            } else {
                $dbConn->commit();
            }
        }

        // Issue response.
        return $response;
    }
}