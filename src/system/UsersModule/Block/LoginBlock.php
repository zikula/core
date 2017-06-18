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
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Event\UserFormAwareEvent;

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
                $dispatcher = $this->get('event_dispatcher');
                $hasListeners = $dispatcher->hasListeners(AccessEvents::LOGIN_FORM); // @deprecated
                $addedContent = $hasListeners ? !empty($dispatcher->dispatch(AccessEvents::LOGIN_FORM, new GenericEvent())->getData()) : false; // @deprecated
                if ($dispatcher->hasListeners(AccessEvents::AUTHENTICATION_FORM)) {
                    $mockForm = $this->get('form.factory')->create();
                    $mockLoginFormEvent = new UserFormAwareEvent($mockForm);
                    $dispatcher->dispatch(AccessEvents::AUTHENTICATION_FORM, $mockLoginFormEvent);
                    $addedContent = $hasListeners && $mockForm->count() > 0;
                }
                $hookBindings = $this->get('hook_dispatcher')->getBindingsFor('subscriber.users.ui_hooks.login_screen');
                // if form is too complicated for a simple block display, display only a link to main form
                $templateParams['linkOnly'] = ($addedContent || count($hookBindings) > 0);

                if (!$addedContent && count($hookBindings) == 0 && count($authenticationMethodCollector->getActiveKeys()) == 1) {
                    $selectedMethod = $authenticationMethodCollector->getActiveKeys()[0];
                    if ($request->hasSession()) {
                        $request->getSession()->set('authenticationMethod', $selectedMethod);
                        if (!$request->getSession()->has('returnUrl')) {
                            $request->getSession()->set('returnUrl', $request->isMethod('GET') ? $request->getUri() : '');
                        }
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
