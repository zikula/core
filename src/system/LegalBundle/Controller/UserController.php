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

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Nucleos\UserBundle\Security\LoginManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\LegalBundle\Form\Type\AcceptPoliciesType;
use Zikula\LegalBundle\Helper\AcceptPoliciesHelper;
use Zikula\ThemeBundle\Controller\Dashboard\UserDashboardController;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

#[Route('/legal')]
class UserController extends AbstractController
{
    private string $firewallName;

    public function __construct(
        private readonly SiteDefinitionInterface $site,
        private readonly array $legalConfig,
        private readonly LoginManager $loginManager,
        #[Autowire('%nucleos_user.firewall_name%')]
        string $firewallName
    ) {
        $this->firewallName = $firewallName;
    }

    /**
     * Main user function.
     * Redirects to the legal notice document.
     */
    #[Route('', name: 'zikulalegalbundle_user_index', methods: ['GET'])]
    public function index(AdminUrlGenerator $urlGenerator): RedirectResponse
    {
        $url = $urlGenerator
            ->setDashboard(UserDashboardController::class)
            // ->setController(self::class)
            ->setRoute('zikulalegalbundle_user_legalnotice')
            ->generateUrl();
        $url = str_replace('/admin?route', '/en?route', $url); // TODO remove hack

        return $this->redirect($url);
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
        $policyConfig = $this->legalConfig['policies'][$policyConfigKey];
        if (!$policyConfig['enabled']) {
            return $this->render('@ZikulaLegal/User/Policy/Display/policyNotActive.html.twig');
        }

        $customUrl = $policyConfig['custom_url'] ?: null;
        if (!empty($customUrl)) {
            return $this->redirect($customUrl);
        }

        return $this->render('@ZikulaLegal/User/Policy/Display/' . $documentName . '.html.twig', [
            'adminMail' => $this->site->getAdminMail(),
        ]);
    }

    #[Route('/acceptpolicies', name: 'zikulalegalbundle_user_acceptpolicies')]
    public function acceptPolicies(
        Request $request,
        ManagerRegistry $doctrine,
        Security $security,
        UserRepositoryInterface $userRepository,
        AcceptPoliciesHelper $acceptPoliciesHelper
    ): Response {
        $currentUser = $security->getUser();
        $loginRequired = null === $currentUser;
        $userId = $currentUser?->getId() ?? 0;

        $form = $this->createForm(AcceptPoliciesType::class, $currentUser, [
            'userId' => $userId,
            'loginRequired' => $loginRequired,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var User $userEntity */
            $userEntity = $userRepository->find($data['userId']);
            $policiesToCheck = $acceptPoliciesHelper->getActivePolicies();
            $nowUTC = new \DateTime('now', new \DateTimeZone('UTC'));
            foreach ($policiesToCheck as $policyName => $isEnabled) {
                $setter = 'set' . ucfirst($policyName);
                $userEntity->{$setter}($data[$policyName . 'Accepted'] && $isEnabled ? $nowUTC : null);
            }
            $doctrine->getManager()->flush();
            if ($data['hasAcceptedPolicies'] && $data['loginRequired']) {
                $this->loginManager->logInUser($this->firewallName, $userEntity);
            }

            return $this->redirectToRoute('user_home');
        }

        return $this->render('@ZikulaLegal/User/acceptPolicies.html.twig', [
            'loginRequired' => $loginRequired,
            'form' => $form->createView(),
        ]);
    }
}
