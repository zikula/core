<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\UserEvents;

/**
 * Class UserAdministrationController
 * @Route("/admin")
 */
class UserAdministrationController extends AbstractController
{
    /**
     * @Route("/user/edit/{user}")
     * @Theme("admin")
     * @Template()
     * @param Request $request
     * @param UserEntity $user
     * @return array
     */
    public function editAction(Request $request, UserEntity $user = null)
    {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (null === $user) {
            $user = new UserEntity();
        }
        $form = $this->createForm('Zikula\UsersModule\Form\Type\AdminCreatedUserType',
            $user, ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);

        $event = new GenericEvent($form->getData(), array(), new ValidationProviders());
        $this->get('event_dispatcher')->dispatch(UserEvents::USER_VALIDATE_NEW, $event);
        $validators = $event->getData();
        $hook = new ValidationHook($validators);
        $this->get('hook_dispatcher')->dispatch(UserEvents::HOOK_USER_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $user = $form->getData();
                $registrationErrors = $this->get('zikulausersmodule.helper.registration_helper')->registerNewUser(
                    $user,
                    false,
                    $form['usernotification']->getData(),
                    $form['adminnotification']->getData(),
                    $form['sendpass']->getData()
                );
                if (empty($registrationErrors)) {
                    $event = new GenericEvent($form->getData(), array(), new ValidationProviders());
                    $this->get('event_dispatcher')->dispatch(UserEvents::USER_PROCESS_NEW, $event);
                    $hook = new ProcessHook($user->getUid());
                    $this->get('hook_dispatcher')->dispatch(UserEvents::HOOK_USER_PROCESS, $hook);

                    if ($user->getActivated() == UsersConstant::ACTIVATED_PENDING_REG) {
                        $this->addFlash('status', $this->__('Done! Created new registration application.'));
                    } elseif (null !== $user->getActivated()) {
                        $this->addFlash('status', $this->__('Done! Created new user account.'));
                    } else {
                        $this->addFlash('error', $this->__('Warning! New user information has been saved, however there may have been an issue saving it properly.'));
                    }

                    return $this->redirectToRoute('zikulausersmodule_admin_view');
                } else {
                    $this->addFlash('error', $this->__('Errors creating user!'));
                    foreach ($registrationErrors as $registrationError) {
                        $this->addFlash('error', $registrationError);
                    }
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
