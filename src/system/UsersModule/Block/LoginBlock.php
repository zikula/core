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

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Collector\AuthenticationMethodCollector;
use Zikula\UsersModule\Event\LoginFormPostCreatedEvent;

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

    public function display(array $properties): string
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
        if ($this->eventDispatcher->hasListeners(LoginFormPostCreatedEvent::class)) {
            $mockForm = $this->formFactory->create();
            $mockLoginFormEvent = new LoginFormPostCreatedEvent($mockForm);
            $this->eventDispatcher->dispatch($mockLoginFormEvent);
            $addedContent = $mockForm->count() > 0;
        }
        $hookBindings = $this->hookDispatcher->getBindingsFor('subscriber.users.ui_hooks.login_screen');
        // if form is too complicated for a simple block display, display only a link to main form
        $templateParams['linkOnly'] = ($addedContent || count($hookBindings) > 0);

        if (!$addedContent && 0 === count($hookBindings) && 1 === count($this->authenticationMethodCollector->getActiveKeys())) {
            $request = $this->requestStack->getCurrentRequest();
            $selectedMethod = $this->authenticationMethodCollector->getActiveKeys()[0];
            if ($request->hasSession() && ($session = $request->getSession())) {
                $session->set('authenticationMethod', $selectedMethod);
                if (!$session->has('returnUrl')) {
                    $session->set('returnUrl', $request->isMethod('GET') ? $request->getUri() : '');
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
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @required
     */
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @required
     */
    public function setCurrentUserApi(CurrentUserApiInterface $currentUserApi): void
    {
        $this->currentUserApi = $currentUserApi;
    }

    /**
     * @required
     */
    public function setAuthenticationMethodCollector(AuthenticationMethodCollector $authenticationMethodCollector): void
    {
        $this->authenticationMethodCollector = $authenticationMethodCollector;
    }

    /**
     * @required
     */
    public function setHookDispatcher(HookDispatcherInterface $hookDispatcher): void
    {
        $this->hookDispatcher = $hookDispatcher;
    }
}
