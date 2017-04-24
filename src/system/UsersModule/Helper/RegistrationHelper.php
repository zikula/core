<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;

class RegistrationHelper
{
    use TranslatorTrait;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * RegistrationHelper constructor.
     * @param VariableApiInterface $variableApi
     * @param CurrentUserApiInterface $currentUserApi
     * @param UserRepositoryInterface $userRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface $translator
     */
    public function __construct(
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        GroupRepositoryInterface $groupRepository,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        $this->variableApi = $variableApi;
        $this->currentUserApi = $currentUserApi;
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->setTranslator($translator);
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Create a new user or registration.
     *
     * @param UserEntity $userEntity
     */
    public function registerNewUser(UserEntity $userEntity)
    {
        $adminApprovalRequired = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
        if (null === $userEntity->getUid()) {
            $userEntity->setUser_Regdate(new \DateTime());
        }
        $userCreateEvent = new GenericEvent($userEntity);
        $this->eventDispatcher->dispatch(RegistrationEvents::FULL_USER_CREATE_VETO, $userCreateEvent);
        if (($adminApprovalRequired && !$userEntity->isApproved()) || $userCreateEvent->isPropagationStopped()) {
            // We need a registration record
            $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_REG);
            $this->userRepository->persistAndFlush($userEntity);

            // ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
            // registration, not a user, so a user account record has really not yet been "created".
            // The item-create hook will be fired when the registration becomes a "real" user
            // account record. This is so that modules that do default actions on the creation
            // of a user account do not perform those actions on a pending registration, which
            // may be deleted at any point.
            $eventName = RegistrationEvents::CREATE_REGISTRATION;
        } else {
            // Everything is in order for a full user record
            $userEntity->setActivated(UsersConstant::ACTIVATED_ACTIVE);

            // Add user to default group @todo refactor with Groups module
            $defaultGroup = $this->variableApi->get('ZikulaGroupsModule', 'defaultgroup', false);
            if (!$defaultGroup) {
                throw new \RuntimeException($this->__('Warning! The user account was created, but there was a problem adding the account to the default group.'));
            }
            if (!$userEntity->getGroups()->containsKey($defaultGroup)) {
                $defaultGroupEntity = $this->groupRepository->find($defaultGroup);
                $userEntity->addGroup($defaultGroupEntity);
            }
            $this->userRepository->persistAndFlush($userEntity);

            // ATTENTION: This is the proper place for the item-create hook, not when a pending
            // registration is created. It is not a "real" record until now, so it wasn't really
            // "created" until now. It is way down here so that the activated state can be properly
            // saved before the hook is fired.
            $eventName = UserEvents::CREATE_ACCOUNT;
        }
        if (!$adminApprovalRequired) {
            $approvedBy = $this->currentUserApi->isLoggedIn() ? $this->currentUserApi->get('uid') : $userEntity->getUid();
            $this->userRepository->setApproved($userEntity, new \DateTime(), $approvedBy); // flushes EM
        }
        $this->eventDispatcher->dispatch($eventName, new GenericEvent($userEntity));
    }

    /**
     * Approves a registration.
     * If the registration is also verified (or does not need it) then a new users table record is created.
     *
     * @param UserEntity $user
     */
    public function approve(UserEntity $user)
    {
        $user->setApproved_By($this->currentUserApi->get('uid'));
        $user->setApproved_Date(new \DateTime());

        $user->setActivated(UsersConstant::ACTIVATED_ACTIVE);
        $this->userRepository->persistAndFlush($user);
        $this->eventDispatcher->dispatch(RegistrationEvents::FORCE_REGISTRATION_APPROVAL, new GenericEvent($user));

        $this->registerNewUser($user);
    }
}
