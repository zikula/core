<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersBundle\Controller;

use Locale;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuCollector;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Event\DeleteUserFormPostCreatedEvent;
use Zikula\UsersBundle\Event\DeleteUserFormPostValidatedEvent;
use Zikula\UsersBundle\Form\Type\ChangeLanguageType;
use Zikula\UsersBundle\Helper\DeleteHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

#[Route('/account')]
#[PermissionCheck('read')]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly bool $displayGraphics,
        private readonly bool $allowSelfDeletion
    ) {
    }

    /**
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    #[Route('', name: 'zikulausersbundle_account_menu')]
    public function menu(ExtensionMenuCollector $extensionMenuCollector): Response
    {
        $accountMenus = $extensionMenuCollector->getAllByContext(ExtensionMenuInterface::CONTEXT_ACCOUNT);

        foreach ($accountMenus as $accountMenu) {
            /** @var \Knp\Menu\ItemInterface $accountMenu */
            $accountMenu->setChildrenAttribute('class', 'list-group');
            foreach ($accountMenu->getChildren() as $child) {
                $child->setAttribute('class', 'list-group-item');
                $icon = $child->getAttribute('icon');
                $icon = $this->displayGraphics ? $icon . ' fa-fw fa-2x' : null;
                $child->setAttribute('icon', $icon);
            }
        }

        return $this->render('@ZikulaUsers/Account/menu.html.twig', [
            'accountMenus' => $accountMenus,
        ]);
    }

    /**
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    #[Route('/change-language', name: 'zikulausersbundle_account_changelanguage')]
    public function changeLanguage(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        LocaleApiInterface $localeApi
    ): Response {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(ChangeLanguageType::class, [
            'locale' => $currentUserApi->get('locale'),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $locale = $this->getParameter('locale');
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
                $this->addFlash('success', $this->translator->trans('Language changed to %lang%', ['%lang%' => $langText], 'messages', $locale));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulausersbundle_account_menu', ['_locale' => $locale]);
        }

        return $this->render('@ZikulaUsers/Account/changeLanguage.html.twig', [
            'form' => $form->createView(),
            'multilingual' => $localeApi->multilingual(),
        ]);
    }

    /**
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    #[Route('/delete', name: 'zikulausersbundle_account_deletemyaccount')]
    public function deleteMyAccount(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        DeleteHelper $deleteHelper
    ): Response {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        if (!$this->allowSelfDeletion) {
            $this->addFlash('error', 'Self deletion is disabled by the site administrator.');

            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }
        if (UsersConstant::USER_ID_ADMIN === $currentUserApi->get('uid')) {
            $this->addFlash('error', 'Self deletion is not possible for main administrator.');

            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }
        $form = $this->createForm(DeletionType::class);
        $deleteUserFormPostCreatedEvent = new DeleteUserFormPostCreatedEvent($form);
        $eventDispatcher->dispatch($deleteUserFormPostCreatedEvent);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');

                return $this->redirectToRoute('zikulausersbundle_account_menu');
            }
            if ($form->get('delete')->isClicked()) {
                if ($form->isValid()) {
                    $deletedUser = $userRepository->find($currentUserApi->get('uid'));
                    $deleteHelper->deleteUser($deletedUser);
                    $eventDispatcher->dispatch(new DeleteUserFormPostValidatedEvent($form, $deletedUser));
                    $request->getSession()->invalidate(); // logout
                    $this->addFlash('success', 'Success. Account deleted!');

                    return $this->redirectToRoute('home');
                }
            }
        }

        return $this->render('@ZikulaUsers/Account/delete.html.twig', [
            'form' => $form->createView(),
            'additionalTemplates' => isset($deleteUserFormPostCreatedEvent) ? $deleteUserFormPostCreatedEvent->getTemplates() : [],
        ]);
    }
}
