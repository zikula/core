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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Form\Type\ChangeLanguageType;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

#[Route('/account')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    #[Route('', name: 'zikulausersbundle_account_menu')]
    public function menu(): Response
    {
        return $this->redirect('/');
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
                /** @var User $userEntity */
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
}
