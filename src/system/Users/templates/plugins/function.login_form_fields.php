<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

function smarty_function_login_form_fields($params, $view)
{
    if (!isset($params) || !is_array($params) || empty($params)) {
        throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('$params', 'login_form_fields'), 'Zikula'));
    }

    if (isset($params['authentication_method'])
            && is_array($params['authentication_method'])
            && !empty($params['authentication_method'])
            && (count($params['authentication_method']) == 2)
            ) {
        $authenticationMethod = $params['authentication_method'];

        if (!isset($authenticationMethod['modname']) || empty($authenticationMethod['modname']) || !is_string($authenticationMethod['modname'])) {
            throw new Zikula_Exception_Fatal(__f('An invalid authentication module was received by the template function \'%1$s\'.', array('login_form_fields'), 'Zikula'));
        }

        if (!isset($authenticationMethod['method']) || empty($authenticationMethod['method']) || !is_string($authenticationMethod['method'])) {
            throw new Zikula_Exception_Fatal(__f('An invalid authentication method was received by the template function \'%1$s\'.', array('login_form_fields'), 'Zikula'));
        }
    } else {
        throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('authentication_method', 'login_form_fields'), 'Zikula'));
    }

    if (isset($params['form_type'])
            && is_string($params['form_type'])
            && !empty($params['form_type'])
            ) {
        $formType = $params['form_type'];
    } else {
        throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('form_type', 'login_form_fields'), 'Zikula'));
    }

    if (isset($params['assign'])) {
        if (!is_string($params['assign'])
                || empty($params['assign'])
                ) {
            throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('assign', 'login_form_fields'), 'Zikula'));
        }
    }

    $args = array(
        'form_type'     => $formType,
        'method'        => $authenticationMethod['method'],
    );
    $content = ModUtil::func($authenticationMethod['modname'], 'Authentication', 'getLoginFormFields', $args, 'Zikula_Controller_AbstractAuthentication');

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $content);
    } else {
        return $content;
    }
}
