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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("")
     * @Template
     * @return Response|array
     */
    public function menuAction()
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn() && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $accountLinks = [];
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            $accountLinks = $this->get('zikula_users_module.helper.account_links_helper')->getAllAccountLinks();
        }

        return ['accountLinks' => $accountLinks];
    }

    /**
     * @Route("/change-language")
     * @Template
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function changeLanguageAction(Request $request)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        $installedLanguages = $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(null, $request->getLocale());
        $form = $this->createFormBuilder()
            ->add('locale', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Choose language'),
                'choices' => $installedLanguages,
                'choices_as_values' => true,
                'placeholder' => $this->__('Site default'),
                'required' => false,
                'data' => $this->get('zikula_users_module.current_user')->get('locale')
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $locale = $this->getParameter('locale');
            if ($form->get('submit')->isClicked()) {
                $data = $form->getData();
                $locale = !empty($data['locale']) ? $data['locale'] : $locale;
                $userEntity = $this->get('zikula_users_module.user_repository')->find($this->get('zikula_users_module.current_user')->get('uid'));
                $userEntity->setLocale($locale);
                $this->get('zikula_users_module.user_repository')->persistAndFlush($userEntity);
                $request->getSession()->set('_locale', $locale);
                \Locale::setDefault($locale);
                $langText = Intl::getLanguageBundle()->getLanguageName($locale);
                $this->addFlash('success', $this->__f('Language changed to %lang', ['%lang' => $langText], 'zikula', $locale));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu', ['_locale' => $locale]);
        }

        return [
            'form' => $form->createView()
        ];
    }
}
