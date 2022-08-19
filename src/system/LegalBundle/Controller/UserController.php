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
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\LegalBundle\Constant as LegalConstant;
use Zikula\LegalBundle\Form\Type\AcceptPoliciesType;
use Zikula\LegalBundle\Helper\AcceptPoliciesHelper;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Helper\AccessHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

#[Route('/legal')]
class UserController extends AbstractController
{
    /**
     * Legal module main user function.
     * Redirects to the Terms of Use legal document.
     */
    #[Route('', name: 'zikulalegalbundle_user_index', methods: ['GET'])]
    public function index(RouterInterface $router): RedirectResponse
    {
        $url = $this->getVar(LegalConstant::MODVAR_TERMS_URL, '');
        if (empty($url)) {
            $url = $router->generate('zikulalegalbundle_user_termsofuse');
        }

        return new RedirectResponse($url);
    }

    /**
     * Display Legal notice.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/legalnotice', name: 'zikulalegalbundle_user_legalnotice', methods: ['GET'])]
    public function legalNotice(): Response
    {
        $doc = $this->renderDocument('legalNotice', LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, LegalConstant::MODVAR_LEGALNOTICE_URL);

        return new Response($doc);
    }

    /**
     * Display Terms of Use
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/termsofuse', name: 'zikulalegalbundle_user_termsofuse', methods: ['GET'])]
    public function termsOfUse(): Response
    {
        $doc = $this->renderDocument('termsOfUse', LegalConstant::MODVAR_TERMS_ACTIVE, LegalConstant::MODVAR_TERMS_URL);

        return new Response($doc);
    }

    /**
     * Display Privacy Policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/privacypolicy', name: 'zikulalegalbundle_user_privacypolicy', methods: ['GET'])]
    public function privacyPolicy(): Response
    {
        $doc = $this->renderDocument('privacyPolicy', LegalConstant::MODVAR_PRIVACY_ACTIVE, LegalConstant::MODVAR_PRIVACY_URL);

        return new Response($doc);
    }

    /**
     * Display Accessibility statement
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/accessibilitystatement', name: 'zikulalegalbundle_user_accessibilitystatement', methods: ['GET'])]
    public function accessibilityStatement(): Response
    {
        $doc = $this->renderDocument('accessibilityStatement', LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, LegalConstant::MODVAR_ACCESSIBILITY_URL);

        return new Response($doc);
    }

    /**
     * Display Cancellation right policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/cancellationrightpolicy', name: 'zikulalegalbundle_user_cancellationrightpolicy', methods: ['GET'])]
    public function cancellationRightPolicy(): Response
    {
        $doc = $this->renderDocument('cancellationRightPolicy', LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL);

        return new Response($doc);
    }

    /**
     * Display Trade conditions
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     */
    #[Route('/tradeconditions', name: 'zikulalegalbundle_user_tradeconditions', methods: ['GET'])]
    public function tradeConditions(): Response
    {
        $doc = $this->renderDocument('tradeConditions', LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, LegalConstant::MODVAR_TRADECONDITIONS_URL);

        return new Response($doc);
    }

    /**
     * Render and display the specified legal document, or redirect to the specified custom URL if it exists.
     *
     * If a custom URL for the legal document exists, as specified by the module variable identified by $customUrlKey, then
     * this function will redirect the user to that URL.
     *
     * If no custom URL exists, then this function will render and return the appropriate template for the legal document, as
     * specified by $documentName. If the legal document
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return RedirectResponse|string HTML output string
     */
    private function renderDocument(string $documentName, string $activeFlagKey, string $customUrlKey)
    {
        if (!$this->hasPermission(LegalConstant::MODNAME . '::' . $documentName, '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        if (!$this->getVar($activeFlagKey)) {
            // intentionally return non-Response
            return $this->renderView('@ZikulaLegal/User/policyNotActive.html.twig');
        }

        $customUrl = $this->getVar($customUrlKey, '');
        if (!empty($customUrl)) {
            return $this->redirect($customUrl);
        }

        $view = $this->renderView('@ZikulaLegal/User/' . $documentName . '.html.twig');

        // intentionally return non-Response
        return $view;
    }

    /**
     * @Template("@ZikulaLegal/User/acceptPolicies.html.twig")
     *
     * @return Response|array
     * @throws Exception
     */
    #[Route('/acceptpolicies', name: 'zikulalegalbundle_user_acceptpolicies')]
    public function acceptPolicies(
        Request $request,
        ManagerRegistry $doctrine,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AccessHelper $accessHelper,
        AcceptPoliciesHelper $acceptPoliciesHelper
    ) {
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
            /** @var UserEntity $userEntity */
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

        return [
            'login' => $login,
            'form' => $form->createView(),
            'activePolicies' => $acceptPoliciesHelper->getActivePolicies(),
            'acceptedPolicies' => $acceptPoliciesHelper->getAcceptedPolicies($uid),
        ];
    }
}
