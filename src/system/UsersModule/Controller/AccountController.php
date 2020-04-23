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

namespace Zikula\UsersModule\Controller;

use Locale;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\DeleteUserFormPostCreatedEvent;
use Zikula\UsersModule\Event\DeleteUserFormPostValidatedEvent;
use Zikula\UsersModule\Form\Type\ChangeLanguageType;
use Zikula\UsersModule\Helper\DeleteHelper;
use Zikula\UsersModule\HookSubscriber\UserManagementUiHooksSubscriber;

/**
 * @Route("/account")
 * @PermissionCheck("read")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("")
     * @Template("@ZikulaUsersModule/Account/menu.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    public function menuAction(
        ExtensionMenuCollector $extensionMenuCollector,
        VariableApiInterface $variableApi
    ): array {
        $extensionMenuCollector->getAllByType(ExtensionMenuInterface::TYPE_ACCOUNT);
        $accountMenus = $extensionMenuCollector->getAllByType(ExtensionMenuInterface::TYPE_ACCOUNT);
        $displayIcon = $variableApi->get('ZikulaUsersModule', Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS, Constant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS);

        foreach ($accountMenus as $accountMenu) {
            /** @var \Knp\Menu\ItemInterface $accountMenu */
            $accountMenu->setChildrenAttribute('class', 'list-group');
            foreach ($accountMenu->getChildren() as $child) {
                $child->setAttribute('class', 'list-group-item');
                $icon = $child->getAttribute('icon');
                $icon = $displayIcon ? $icon . ' fa-fw fa-2x' : null;
                $child->setAttribute('icon', $icon);
            }
        }

        return ['accountMenus' => $accountMenus];
    }

    /**
     * @Route("/change-language")
     * @Template("@ZikulaUsersModule/Account/changeLanguage.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    public function changeLanguageAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ) {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(ChangeLanguageType::class, [
            'locale' => $currentUserApi->get('locale')
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
                $this->addFlash('success', $this->trans('Language changed to %lang%', ['%lang%' => $langText], 'messages', $locale));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulausersmodule_account_menu', ['_locale' => $locale]);
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete")
     * @Template("@ZikulaUsersModule/Account/delete.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    public function deleteMyAccountAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        HookDispatcherInterface $hookDispatcher,
        DeleteHelper $deleteHelper
    ) {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        if (!$this->getVar(UsersConstant::MODVAR_ALLOW_USER_SELF_DELETE, UsersConstant::DEFAULT_ALLOW_USER_SELF_DELETE)) {
            $this->addFlash('error', 'Self deletion is disabled by the site administrator.');

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        if (UsersConstant::USER_ID_ADMIN === $currentUserApi->get('uid')) {
            $this->addFlash('error', 'Self deletion is not possible for main administrator.');

            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        $form = $this->createForm(DeletionType::class);
        $deleteUserFormPostCreatedEvent = new DeleteUserFormPostCreatedEvent($form);
        $eventDispatcher->dispatch($deleteUserFormPostCreatedEvent);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');

                return $this->redirectToRoute('zikulausersmodule_account_menu');
            }
            if ($form->get('delete')->isClicked()) {
                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::DELETE_VALIDATE, $hook = new ValidationHook());
                $validHooks = true;
                if ($hook->getValidators()->hasErrors()) {
                    $message = implode('<br>', $hook->getValidators()->getErrors());
                    $this->addFlash('error', $message);
                    $validHooks = false;
                }
                if ($validHooks && $form->isValid()) {
                    $deletedUser = $userRepository->find($currentUserApi->get('uid'));
                    $deleteHelper->deleteUser($deletedUser);
                    $eventDispatcher->dispatch(new DeleteUserFormPostValidatedEvent($form, $deletedUser));
                    $request->getSession()->invalidate(); // logout
                    $this->addFlash('success', 'Success. Account deleted!');

                    return $this->redirectToRoute('home');
                }
            }
        }

        return [
            'form' => $form->createView(),
            'additionalTemplates' => isset($deleteUserFormPostCreatedEvent) ? $deleteUserFormPostCreatedEvent->getTemplates() : []
        ];
    }
}
