<?php
/**
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
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

        $form = $this->createForm('Zikula\UsersModule\Form\ConfigType\ConfigType',
            $this->getVars(), ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                $this->setVars($data);
                $this->get('event_dispatcher')->dispatch(UserEvents::CONFIG_UPDATED, new GenericEvent(null, array(), $data));
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
        $authenticationMethodsStatus = $this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'authenticationMethodsStatus', []);
        foreach ($allMethods as $alias => $method) {
            if (!isset($authenticationMethodsStatus[$alias])) {
                $authenticationMethodsStatus[$alias] = false;
            }
        }

        $form = $this->createForm('Zikula\UsersModule\Form\ConfigType\AuthenticationMethodsType',
            ['authenticationMethodsStatus' => $authenticationMethodsStatus],
            ['translator' => $this->getTranslator()]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                // @todo check if ALL are disabled
                $data = $form->getData();
                $this->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'authenticationMethodsStatus', $data['authenticationMethodsStatus']);
                $this->addFlash('status', $this->__('Done! Configuration updated.'));
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
