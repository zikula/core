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

namespace Zikula\UsersModule\Controller;

use Locale;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Helper\AccountLinksHelper;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("")
     * @Template("@ZikulaUsersModule/Account/menu.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in or hasn't read permissions for the module
     */
    public function menuAction(
        CurrentUserApiInterface $currentUserApi,
        AccountLinksHelper $accountLinksHelper
    ): array {
        if ($currentUserApi->isLoggedIn() && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $accountLinks = [];
        if ($currentUserApi->isLoggedIn()) {
            $accountLinks = $accountLinksHelper->getAllAccountLinks();
        }

        return ['accountLinks' => $accountLinks];
    }

    /**
     * @Route("/change-language")
     * @Template("@ZikulaUsersModule/Account/changeLanguage.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function changeLanguageAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        LocaleApiInterface $localeApi
    ) {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        $installedLanguages = $localeApi->getSupportedLocaleNames(null, $request->getLocale());
        $form = $this->createFormBuilder()
            ->add('locale', ChoiceType::class, [
                'label' => $this->__('Choose language'),
                'choices' => $installedLanguages,
                'placeholder' => $this->__('Site default'),
                'required' => false,
                'data' => $currentUserApi->get('locale')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $locale = $this->container->getParameter('locale');
            if ($form->get('submit')->isClicked()) {
                $data = $form->getData();
                $locale = !empty($data['locale']) ? $data['locale'] : $locale;
                /** @var UserEntity $userEntity */
                $userEntity = $userRepository->find($currentUserApi->get('uid'));
                $userEntity->setLocale($locale);
                $userRepository->persistAndFlush($userEntity);
                if ($request->hasSession() && ($session = $request->getSession())) {
                    $session->set('_locale', $locale);
                }
                Locale::setDefault($locale);
                $langText = Languages::getName($locale);
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
