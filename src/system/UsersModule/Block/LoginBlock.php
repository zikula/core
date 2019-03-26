<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Block;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Collector\AuthenticationMethodCollector;
use Zikula\UsersModule\Event\UserFormAwareEvent;

/**
 * A block that allows users to log into the system.
 */
class LoginBlock extends AbstractBlockHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var AuthenticationMethodCollector
     */
    private $authenticationMethodCollector;

    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    public function display(array $properties)
    {
        if (!$this->hasPermission('Loginblock::', $properties['title'] . '::', ACCESS_READ)) {
            return '';
        }

        if ($this->currentUserApi->isLoggedIn()) {
            return '';
        }

        $template = '@ZikulaUsersModule/Block/login.html.twig';
        $templateParams = [
            'collector' => $this->authenticationMethodCollector,
            'path' => 'zikulausersmodule_access_login',
            'position' => $properties['position']
        ];
        $addedContent = false;
        if ($this->eventDispatcher->hasListeners(AccessEvents::AUTHENTICATION_FORM)) {
            $mockForm = $this->formFactory->create();
            $mockLoginFormEvent = new UserFormAwareEvent($mockForm);
            $this->eventDispatcher->dispatch(AccessEvents::AUTHENTICATION_FORM, $mockLoginFormEvent);
            $addedContent = $mockForm->count() > 0;
        }
        $hookBindings = $this->hookDispatcher->getBindingsFor('subscriber.users.ui_hooks.login_screen');
        // if form is too complicated for a simple block display, display only a link to main form
        $templateParams['linkOnly'] = ($addedContent || count($hookBindings) > 0);

        if (!$addedContent && 0 === count($hookBindings) && 1 === count($this->authenticationMethodCollector->getActiveKeys())) {
            $request = $this->requestStack->getCurrentRequest();
            $selectedMethod = $this->authenticationMethodCollector->getActiveKeys()[0];
            if ($request->hasSession()) {
                $request->getSession()->set('authenticationMethod', $selectedMethod);
                if (!$request->getSession()->has('returnUrl')) {
                    $request->getSession()->set('returnUrl', $request->isMethod('GET') ? $request->getUri() : '');
                }
            }
            $authenticationMethod = $this->authenticationMethodCollector->get($selectedMethod);
            if ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface) {
                $form = $this->formFactory->create($authenticationMethod->getLoginFormClassName(), [], [
                    'action' => $this->router->generate('zikulausersmodule_access_login')
                ]);
                $templateParams['form'] = $form->createView();
                $template = $authenticationMethod->getLoginTemplateName('block', $properties['position']);
                $templateParams['loginHeader'] = $this->renderView('@ZikulaUsersModule/Access/loginHeader.html.twig');
                $templateParams['loginFooter'] = $this->renderView('@ZikulaUsersModule/Access/loginFooter.html.twig');
            }
        }

        return $this->renderView($template, $templateParams);
    }

    /**
     * @required
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @required
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @required
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @required
     * @param CurrentUserApiInterface $currentUserApi
     */
    public function setCurrentUserApi(CurrentUserApiInterface $currentUserApi)
    {
        $this->currentUserApi = $currentUserApi;
    }

    /**
     * @required
     * @param AuthenticationMethodCollector $authenticationMethodCollector
     */
    public function setAuthenticationMethodCollector(AuthenticationMethodCollector $authenticationMethodCollector)
    {
        $this->authenticationMethodCollector = $authenticationMethodCollector;
    }

    /**
     * @required
     * @param HookDispatcherInterface $hookDispatcher
     */
    public function setHookDispatcher(HookDispatcherInterface $hookDispatcher)
    {
        $this->hookDispatcher = $hookDispatcher;
    }
}
