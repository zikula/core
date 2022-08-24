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

namespace Zikula\LegalBundle\EventListener;

use DateTime;
use DateTimeZone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Zikula\LegalBundle\Form\Type\PolicyType;
use Zikula\LegalBundle\Helper\AcceptPoliciesHelper;
use Zikula\LegalBundle\LegalConstant;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Event\EditUserFormPostCreatedEvent;
use Zikula\UsersBundle\Event\EditUserFormPostValidatedEvent;
use Zikula\UsersBundle\Event\UserAccountDisplayEvent;
use Zikula\UsersBundle\Event\UserPreLoginSuccessEvent;
use Zikula\UsersBundle\UsersConstant;

/**
 * Handles hook-like event notifications from log-in and registration for the acceptance of policies.
 */
class UsersUiListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private const EVENT_KEY = 'module.legal.users_ui_handler';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
        private readonly AcceptPoliciesHelper $acceptPoliciesHelper,
        private readonly FormFactoryInterface $formFactory,
        private readonly PermissionApiInterface $permissionApi
    ) {
    }

    /**
     * Establish the handlers for various events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserAccountDisplayEvent::class => ['uiView'],
            UserPreLoginSuccessEvent::class => ['acceptPolicies'],
            EditUserFormPostCreatedEvent::class => ['amendForm', -256],
            EditUserFormPostValidatedEvent::class => ['editFormHandler']
        ];
    }

    /**
     * Responds to ui.view hook-like event notifications.
     */
    public function uiView(UserAccountDisplayEvent $event): void
    {
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        $user = $event->getUser();
        if (!isset($user) || empty($user) || 1 > array_sum($activePolicies)) {
            return;
        }

        $acceptedPolicies = $this->acceptPoliciesHelper->getAcceptedPolicies($user->getUid());
        $viewablePolicies = $this->acceptPoliciesHelper->getViewablePolicies($user->getUid());
        if (1 > array_sum($viewablePolicies)) {
            return;
        }

        $templateParameters = [
            'activePolicies' => $activePolicies,
            'viewablePolicies' => $viewablePolicies,
            'acceptedPolicies' => $acceptedPolicies,
        ];

        $event->addContent(self::EVENT_KEY, $this->twig->render('@ZikulaLegal/UsersUI/view.html.twig', $templateParameters));
    }

    /**
     * Vetoes (denies) a login attempt, and forces the user to accept policies.
     *
     * This handler is triggered by the 'Zikula\UsersBundle\Event\UserPreLoginSuccessEvent' event.  It vetoes (denies) a
     * login attempt if the users's Legal record is flagged to force the user to accept
     * one or more legal agreements.
     */
    public function acceptPolicies(UserPreLoginSuccessEvent $event): void
    {
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        if (1 > array_sum($activePolicies)) {
            return;
        }

        $user = $event->getUser();
        if (!isset($user) || $user->getUid() <= UsersConstant::USER_ID_ADMIN) {
            return;
        }

        $attributeIsEmpty = function ($name) use ($user) {
            if ($user->hasAttribute($name)) {
                $v = $user->getAttributeValue($name);

                return empty($v);
            }

            return true;
        };
        $privacyPolicyAccepted = $activePolicies['privacyPolicy'] ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED) : true;
        $termsOfUseAccepted = $activePolicies['termsOfUse'] ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED) : true;
        $tradeConditionsAccepted = true; // $activePolicies['tradeConditions'] ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED) : true;
        $cancellationRightPolicyAccepted = true; // $activePolicies['cancellationRightPolicy'] ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED) : true;
        $agePolicyAccepted = $activePolicies['agePolicy'] ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED) : true;

        if ($privacyPolicyAccepted && $termsOfUseAccepted && $tradeConditionsAccepted && $cancellationRightPolicyAccepted && $agePolicyAccepted) {
            return;
        }

        $event->stopPropagation();
        $event->setRedirectUrl($this->router->generate('zikulalegalbundle_user_acceptpolicies'));

        $request = $this->requestStack->getMainRequest();
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set(LegalConstant::FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY, $user->getUid());
        }
        $event->addFlash('Your log-in request was not completed. You must review and confirm your acceptance of one or more site policies prior to logging in.');
    }

    public function amendForm(EditUserFormPostCreatedEvent $event): void
    {
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        if (1 > array_sum($activePolicies)) {
            return;
        }

        $user = $event->getFormData();
        $uid = !empty($user['uid']) ? $user['uid'] : null;
        $uname = !empty($user['uname']) ? $user['uname'] : null;
        $policyForm = $this->formFactory->create(PolicyType::class, [], [
            'error_bubbling' => true,
            'auto_initialize' => false,
            'mapped' => false,
            'userEditAccess' => $this->permissionApi->hasPermission('ZikulaUsersModule::', $uname . '::' . $uid, ACCESS_EDIT)
        ]);
        $acceptedPolicies = $this->acceptPoliciesHelper->getAcceptedPolicies($uid);
        $event
            ->formAdd($policyForm)
            ->addTemplate('@ZikulaLegal/UsersUI/editRegistration.html.twig', [
                'activePolicies' => $this->acceptPoliciesHelper->getActivePolicies(),
                'acceptedPolicies' => $acceptedPolicies,
            ])
        ;
    }

    public function editFormHandler(EditUserFormPostValidatedEvent $event): void
    {
        $userEntity = $event->getUser();
        $formData = $event->getFormData(LegalConstant::FORM_BLOCK_PREFIX);
        if (!isset($formData)) {
            return;
        }
        $policiesToCheck = [
            'termsOfUse' => LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
            'privacyPolicy' => LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
            'agePolicy' => LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
            'tradeConditions' => LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED,
            'cancellationRightPolicy' => LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
        ];
        $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(DateTime::ATOM);
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        foreach ($policiesToCheck as $policyName => $acceptedVar) {
            if ($formData['acceptedpolicies_policies'] && $activePolicies[$policyName]) {
                $userEntity->setAttribute($acceptedVar, $nowUTCStr);
            } else {
                $userEntity->delAttribute($acceptedVar);
            }
        }

        // we do not call flush here on purpose because maybe
        // other modules need to care for certain things before
        // the Users module calls flush after all listeners finished
    }
}
