<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Controller
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract AJAX controller.
 */
abstract class Zikula_Controller_Ajax extends Zikula_Controller
{
    /**
     * Check the CSFR token.
     *
     * @throws Zikula_Response_Ajax_Forbidden If the CSFR token fails.
     *
     * @return void
     */
    public function checkAjaxToken()
    {
        $token = isset($_SERVER['HTTP_X_ZIKULA_AJAX_TOKEN']) ? $_SERVER['HTTP_X_ZIKULA_AJAX_TOKEN'] : '';
        // we might have to account for session regeneration here - drak
        if (!$token == session_id()) {
            throw new Zikula_Exception_Forbidden(__('Ajax security checks failed.'));
        }
    }
}