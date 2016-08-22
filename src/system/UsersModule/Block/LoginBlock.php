<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;

/**
 * A block that allows users to log into the system.
 */
class LoginBlock extends AbstractBlockHandler
{
    public function display(array $properties)
    {
        if ($this->hasPermission('Loginblock::', $properties['title'].'::', ACCESS_READ)) {
            if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
                $request = $this->get('request_stack')->getCurrentRequest();

                $authenticationMethodCollector = $this->get('zikula_users_module.internal.authentication_method_collector');
                $template = '@ZikulaUsersModule/Block/login.html.twig';
                $templateParams = [
                    'collector' => $authenticationMethodCollector,
                    'path' => 'zikulausersmodule_access_login',
                    'position' => $properties['position']
                ];
                if (count($authenticationMethodCollector->getActiveKeys()) == 1) {
                    $selectedMethod = $authenticationMethodCollector->getActiveKeys()[0];
                    $request->getSession()->set('authenticationMethod', $selectedMethod);
                    if (!$request->getSession()->has('returnUrl')) {
                        $request->getSession()->set('returnUrl', $request->isMethod('GET') ? $request->getUri() : '');
                    }
                    $authenticationMethod = $authenticationMethodCollector->get($selectedMethod);
                    if ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface) {
                        $form = $this->get('form.factory')->create($authenticationMethod->getLoginFormClassName(), [], [
                            'action' => $this->get('router')->generate('zikulausersmodule_access_login')
                        ]);
                        $templateParams['form'] = $form->createView();
                        $template = $authenticationMethod->getLoginTemplateName('block', $properties['position']);
                    }
                }

                return $this->renderView($template, $templateParams);
            }
        }

        return '';
    }
}
