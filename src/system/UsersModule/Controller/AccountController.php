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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Form\Type\ChangeLanguageType;

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
        CurrentUserApiInterface $currentUserApi,
        ExtensionMenuCollector $extensionMenuCollector,
        VariableApiInterface $variableApi
    ): array {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $accountMenus = [];
        if ($currentUserApi->isLoggedIn()) {
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
}
