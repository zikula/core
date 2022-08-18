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

namespace Zikula\LegalModule\Listener;

use DateTime;
use DateTimeZone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Form\Type\PolicyType;
use Zikula\LegalModule\Helper\AcceptPoliciesHelper;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Event\EditUserFormPostCreatedEvent;
use Zikula\UsersModule\Event\EditUserFormPostValidatedEvent;
use Zikula\UsersModule\Event\UserAccountDisplayEvent;
use Zikula\UsersModule\Event\UserPreLoginSuccessEvent;

/**
 * Handles hook-like event notifications from log-in and registration for the acceptance of policies.
 */
class UsersUiListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private const EVENT_KEY = 'module.legal.users_ui_handler';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AcceptPoliciesHelper
     */
    private $acceptPoliciesHelper;

    /**
     * @var array
     */
    private $moduleVars;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var PermissionApiInterface
     */
    protected $permissionApi;

    public function __construct(
        RequestStack $requestStack,
        Environment $twig,
        RouterInterface $router,
        VariableApiInterface $variableApi,
        AcceptPoliciesHelper $acceptPoliciesHelper,
        FormFactoryInterface $formFactory,
        PermissionApiInterface $permissionApi
    ) {
        $this->requestStack = $requestStack;
        $this->twig = $twig;
        $this->router = $router;
        $this->moduleVars = $variableApi->getAll('ZikulaLegalModule');
        $this->acceptPoliciesHelper = $acceptPoliciesHelper;
        $this->formFactory = $formFactory;
        $this->permissionApi = $permissionApi;
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
        $activePolicyCount = array_sum($activePolicies);
        $user = $event->getUser();
        if (!isset($user) || empty($user) || $activePolicyCount < 1) {
            return;
        }

        $acceptedPolicies = $this->acceptPoliciesHelper->getAcceptedPolicies($user->getUid());
        $viewablePolicies = $this->acceptPoliciesHelper->getViewablePolicies($user->getUid());
        if (array_sum($viewablePolicies) < 1) {
            return;
        }

        $templateParameters = [
            'activePolicies' => $activePolicies,
            'viewablePolicies' => $viewablePolicies,
            'acceptedPolicies' => $acceptedPolicies,
        ];

        $event->addContent(self::EVENT_KEY, $this->twig->render('@ZikulaLegalModule/UsersUI/view.html.twig', $templateParameters));
    }

    /**
     * Vetoes (denies) a login attempt, and forces the user to accept policies.
     *
     * This handler is triggered by the 'Zikula\UsersModule\Event\UserPreLoginSuccessEvent' event.  It vetoes (denies) a
     * login attempt if the users's Legal record is flagged to force the user to accept
     * one or more legal agreements.
     */
    public function acceptPolicies(UserPreLoginSuccessEvent $event): void
    {
        $termsOfUseActive = $this->moduleVars[LegalConstant::MODVAR_TERMS_ACTIVE] ?? false;
        $privacyPolicyActive = $this->moduleVars[LegalConstant::MODVAR_PRIVACY_ACTIVE] ?? false;
        $agePolicyActive = isset($this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE]) ? 0 !== $this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE] : 0;
        $cancellationRightPolicyActive = $this->moduleVars[LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE] ?? false;
        $tradeConditionsActive = $this->moduleVars[LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE] ?? false;

        if (!$termsOfUseActive && !$privacyPolicyActive && !$agePolicyActive && !$tradeConditionsActive && !$cancellationRightPolicyActive) {
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
        $termsOfUseAccepted = $termsOfUseActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED) : true;
        $privacyPolicyAccepted = $privacyPolicyActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED) : true;
        $agePolicyAccepted = $agePolicyActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED) : true;
        $tradeConditionsAccepted = true; // $tradeConditionsActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED) : true;
        $cancellationRightPolicyAccepted = true; // $cancellationRightPolicyActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED) : true;

        if ($termsOfUseAccepted && $privacyPolicyAccepted && $agePolicyAccepted && $tradeConditionsAccepted && $cancellationRightPolicyAccepted) {
            return;
        }

        $event->stopPropagation();
        $event->setRedirectUrl($this->router->generate('zikulalegalmodule_user_acceptpolicies'));

        $request = $this->requestStack->getMainRequest();
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set(LegalConstant::FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY, $user->getUid());
        }
        $event->addFlash('Your log-in request was not completed. You must review and confirm your acceptance of one or more site policies prior to logging in.');
    }

    public function amendForm(EditUserFormPostCreatedEvent $event): void
    {
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        if (array_sum($activePolicies) < 1) {
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
            ->addTemplate('@ZikulaLegalModule/UsersUI/editRegistration.html.twig', [
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
