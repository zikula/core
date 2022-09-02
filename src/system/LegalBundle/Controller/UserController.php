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

namespace Zikula\LegalBundle\Controller;

use DateTime;
use DateTimeZone;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\LegalBundle\Form\Type\AcceptPoliciesType;
use Zikula\LegalBundle\Helper\AcceptPoliciesHelper;
use Zikula\LegalBundle\LegalConstant;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Helper\AccessHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

#[Route('/legal')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly PermissionApiInterface $permissionApi,
        private readonly SiteDefinitionInterface $site,
        private readonly array $legalConfig
    ) {
    }

    /**
     * Main user function.
     * Redirects to the Terms of Use legal document.
     */
    #[Route('', name: 'zikulalegalbundle_user_index', methods: ['GET'])]
    public function index(RouterInterface $router): RedirectResponse
    {
        return $this->redirectToRoute('zikulalegalbundle_user_legalnotice');
    }

    /**
     * Display Legal notice.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/legalnotice', name: 'zikulalegalbundle_user_legalnotice', methods: ['GET'])]
    public function legalNotice(): Response
    {
        return $this->renderDocument('legalNotice', 'legal_notice');
    }

    /**
     * Display Privacy Policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/privacypolicy', name: 'zikulalegalbundle_user_privacypolicy', methods: ['GET'])]
    public function privacyPolicy(): Response
    {
        return $this->renderDocument('privacyPolicy', 'privacy_policy');
    }

    /**
     * Display Terms of Use
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/termsofuse', name: 'zikulalegalbundle_user_termsofuse', methods: ['GET'])]
    public function termsOfUse(): Response
    {
        return $this->renderDocument('termsOfUse', 'terms_of_use');
    }

    /**
     * Display Accessibility statement
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/accessibilitystatement', name: 'zikulalegalbundle_user_accessibilitystatement', methods: ['GET'])]
    public function accessibilityStatement(): Response
    {
        return $this->renderDocument('accessibilityStatement', 'accessibility');
    }

    /**
     * Display Trade conditions
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/tradeconditions', name: 'zikulalegalbundle_user_tradeconditions', methods: ['GET'])]
    public function tradeConditions(): Response
    {
        return $this->renderDocument('tradeConditions', 'trade_conditions');
    }

    /**
     * Display Cancellation right policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/cancellationrightpolicy', name: 'zikulalegalbundle_user_cancellationrightpolicy', methods: ['GET'])]
    public function cancellationRightPolicy(): Response
    {
        return $this->renderDocument('cancellationRightPolicy', 'cancellation_right_policy');
    }

    /**
     * Render and display the specified legal document, or redirect to the specified custom URL if it exists.
     *
     * If a custom URL for the legal document exists, as specified by the bundle configuration, then
     * this function will redirect the user to that URL.
     *
     * If no custom URL exists, then this function will render and return the appropriate template for the legal document.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    private function renderDocument(string $documentName, string $policyConfigKey): Response
    {
        if (!$this->permissionApi->hasPermission('ZikulaLegalBundle::' . $documentName, '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $policyConfig = $this->legalConfig['policies'][$policyConfigKey];
        if (!$policyConfig['enabled']) {
            return $this->render('@ZikulaLegal/User/policyNotActive.html.twig');
        }

        $customUrl = $policyConfig['custom_url'] ?: null;
        if (!empty($customUrl)) {
            return $this->redirect($customUrl);
        }

        return $this->render('@ZikulaLegal/User/' . $documentName . '.html.twig', [
            'adminMail' => $this->site->getAdminMail(),
        ]);
    }

    #[Route('/acceptpolicies', name: 'zikulalegalbundle_user_acceptpolicies')]
    public function acceptPolicies(
        Request $request,
        ManagerRegistry $doctrine,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AccessHelper $accessHelper,
        AcceptPoliciesHelper $acceptPoliciesHelper
    ): Response {
        // Retrieve and delete any session variables being sent in by the log-in process before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $uid = null;
        if ($request->hasSession() && ($session = $request->getSession())) {
            $uid = $session->get(LegalConstant::FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY);
            $session->remove(LegalConstant::FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY);
        }

        if (null !== $uid) {
            $login = true;
        } else {
            $login = false;
            $uid = $currentUserApi->get('uid');
        }

        $form = $this->createForm(AcceptPoliciesType::class, [
            'uid' => $uid,
            'login' => $login
        ]);
        if ($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var User $userEntity */
            $userEntity = $userRepository->find($data['uid']);
            $policiesToCheck = [
                'termsOfUse' => LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
                'privacyPolicy' => LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
                'agePolicy' => LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
                'tradeConditions' => LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED,
                'cancellationRightPolicy' => LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
            ];
            $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
            $nowUTCStr = $nowUTC->format(DateTime::ATOM);
            $activePolicies = $acceptPoliciesHelper->getActivePolicies();
            foreach ($policiesToCheck as $policyName => $acceptedVar) {
                if ($data['acceptedpolicies_policies'] && $activePolicies[$policyName]) {
                    $userEntity->setAttribute($acceptedVar, $nowUTCStr);
                } else {
                    $userEntity->delAttribute($acceptedVar);
                }
            }
            $doctrine->getManager()->flush();
            if ($data['acceptedpolicies_policies'] && $data['login']) {
                $accessHelper->login($userEntity);

                return $this->redirectToRoute('zikulausersbundle_account_menu');
            }

            return $this->redirectToRoute('home');
        }

        return $this->render('@ZikulaLegal/User/acceptPolicies.html.twig', [
            'login' => $login,
            'form' => $form->createView(),
            'activePolicies' => $acceptPoliciesHelper->getActivePolicies(),
            'acceptedPolicies' => $acceptPoliciesHelper->getAcceptedPolicies($uid),
        ]);
    }
}
