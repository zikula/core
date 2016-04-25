<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Response;

function smarty_function_authentication_method_selector($params, $view)
{
    if (!isset($params) || !is_array($params) || empty($params)) {
        throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('$params', 'authentication_method_selector'), 'Zikula'));
    }

    if (isset($params['authentication_method'])
            && is_array($params['authentication_method'])
            && !empty($params['authentication_method'])
            && (count($params['authentication_method']) == 2)
            ) {
        $authenticationMethod = $params['authentication_method'];

        if (!isset($authenticationMethod['modname']) || empty($authenticationMethod['modname']) || !is_string($authenticationMethod['modname'])) {
            throw new Zikula_Exception_Fatal(__f('An invalid authentication module was received by the template function \'%1$s\'.', array('authentication_method_selector'), 'Zikula'));
        }

        if (!isset($authenticationMethod['method']) || empty($authenticationMethod['method']) || !is_string($authenticationMethod['method'])) {
            throw new Zikula_Exception_Fatal(__f('An invalid authentication method was received by the template function \'%1$s\'.', array('authentication_method_selector'), 'Zikula'));
        }
    } else {
        throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('authentication_method', 'authentication_method_selector'), 'Zikula'));
    }

    if (isset($params['selected_authentication_method']) && is_array($params['selected_authentication_method'])
                && !empty($params['selected_authentication_method'])) {
        if (count($params['selected_authentication_method']) == 2) {
            if (!isset($params['selected_authentication_method']['modname']) || empty($params['selected_authentication_method']['modname'])
                    || !is_string($params['selected_authentication_method']['modname'])
                    ) {
                throw new Zikula_Exception_Fatal(__f('An invalid selected authentication module was received by the template function \'%1$s\'.', array('authentication_method_selector'), 'Zikula'));
            }

            if (!isset($params['selected_authentication_method']['method']) || empty($params['selected_authentication_method']['method'])
                    || !is_string($params['selected_authentication_method']['method'])
                    ) {
                throw new Zikula_Exception_Fatal(__f('An invalid selected authentication method was received by the template function \'%1$s\'.', array('authentication_method_selector'), 'Zikula'));
            }

            $isSelected = ($authenticationMethod['modname'] == $params['selected_authentication_method']['modname'])
                    && ($authenticationMethod['method'] == $params['selected_authentication_method']['method']);
        } else {
            throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('selected_authentication_method', 'authentication_method_selector'), 'Zikula'));
        }
    } else {
        $isSelected = false;
    }

    if (!isset($params['form_type'])
            || !is_string($params['form_type'])
            || empty($params['form_type'])
            ) {
        throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('form_type', 'authentication_method_selector')));
    }

    if (!isset($params['form_action'])
            || !is_string($params['form_action'])
            || empty($params['form_action'])
            ) {
        throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('form_action', 'authentication_method_selector')));
    }

    if (isset($params['assign'])) {
        if (!is_string($params['assign'])
                || empty($params['assign'])
                ) {
            throw new Zikula_Exception_Fatal(__f('An invalid \'%1$s\' parameter was received by the template function \'%2$s\'.', array('assign', 'authentication_method_selector'), 'Zikula'));
        }
    }

    $getSelectorArgs = array(
        'form_type'   => $params['form_type'],
        'form_action' => $params['form_action'],
        'method'      => $authenticationMethod['method'],
        'is_selected' => $isSelected,
    );
    $content = ModUtil::func($authenticationMethod['modname'], 'Authentication', 'getAuthenticationMethodSelector', $getSelectorArgs, 'Zikula_Controller_AbstractAuthentication');
    if ($content instanceof Response) {
        // Forward compatability. @todo Remove check in 1.5.0
        $content = $content->getContent();
    }

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $content);
    } else {
        return $content;
    }
}
