<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Form\ConfigType\AuthenticationMethodsType;
use Zikula\UsersModule\Form\ConfigType\ConfigType;
use Zikula\UsersModule\UserEvents;

/**
 * @Route("/admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @return array
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ConfigType::class, $this->getVars(), [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                $this->setVars($data);
                $this->get('event_dispatcher')->dispatch(UserEvents::CONFIG_UPDATED, new GenericEvent(null, [], $data));
                $this->addFlash('status', $this->__('Done! Configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
            'UC' => new UsersConstant()
        ];
    }

    /**
     * @Route("/config/authentication-methods")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @return array
     */
    public function authenticationMethodsAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $allMethods = $this->get('zikula_users_module.internal.authentication_method_collector')->getAll();
        $authenticationMethodsStatus = $this->get('zikula_extensions_module.api.variable')->getSystemVar('authenticationMethodsStatus', []);
        foreach ($allMethods as $alias => $method) {
            if (!isset($authenticationMethodsStatus[$alias])) {
                $authenticationMethodsStatus[$alias] = false;
            }
        }

        $form = $this->createForm(AuthenticationMethodsType::class, ['
            authenticationMethodsStatus' => $authenticationMethodsStatus
        ], [
            'translator' => $this->getTranslator()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                if (!in_array(true, $data['authenticationMethodsStatus'])) {
                    $data['authenticationMethodsStatus']['native_uname'] = true; // do not allow all methods to be inactive.
                    $this->addFlash('info', $this->__f('All methods cannot be inactive. At least one methods must be enabled. (%m has been enabled).', ['%m' => $allMethods['native_uname']->getDisplayName()]));
                }
                $this->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'authenticationMethodsStatus', $data['authenticationMethodsStatus']);
                $this->addFlash('status', $this->__('Done! Configuration updated.'));

                return $this->redirectToRoute('zikulausersmodule_config_authenticationmethods');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
            'methods' => $allMethods
        ];
    }
}
