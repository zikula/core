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

namespace Zikula\UsersModule\Helper;

use DateTime;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Constant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\ActiveUserPostCreatedEvent;
use Zikula\UsersModule\Event\ActiveUserPreCreatedEvent;
use Zikula\UsersModule\Event\RegistrationPostApprovedEvent;
use Zikula\UsersModule\Event\RegistrationPostCreatedEvent;
use Zikula\UsersModule\RegistrationEvents;

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

    /**
     * Create a new user or registration.
     */
    public function registerNewUser(UserEntity $userEntity): void
    {
        $adminApprovalRequired = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
        if (null === $userEntity->getUid()) {
            $userEntity->setRegistrationDate(new DateTime());
        }
        $this->eventDispatcher->dispatch($createActiveUser = new ActiveUserPreCreatedEvent($userEntity));
        if (($adminApprovalRequired && !$userEntity->isApproved()) || $createActiveUser->isPropagationStopped()) {
            // We need a registration record
            $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_REG);
            $this->userRepository->persistAndFlush($userEntity);

            // ATTENTION: Do NOT issue an item-create hook at this point! The record is a pending
            // registration, not a user, so a user account record has really not yet been "created".
            // The item-create hook will be fired when the registration becomes a "real" user
            // account record. This is so that modules that do default actions on the creation
            // of a user account do not perform those actions on a pending registration, which
            // may be deleted at any point.
            $event = new RegistrationPostCreatedEvent($userEntity);
        } else {
            // Everything is in order for a full user record
            $userEntity->setActivated(UsersConstant::ACTIVATED_ACTIVE);

            // Add user to default group
            $defaultGroupId = $this->variableApi->get('ZikulaGroupsModule', 'defaultgroup', Constant::GROUP_ID_USERS);
            if (!$userEntity->getGroups()->containsKey($defaultGroupId)) {
                /** @var GroupEntity $defaultGroupEntity */
                $defaultGroupEntity = $this->groupRepository->find($defaultGroupId);
                $userEntity->addGroup($defaultGroupEntity);
            }
            $this->userRepository->persistAndFlush($userEntity);

            // ATTENTION: This is the proper place for the item-create hook, not when a pending
            // registration is created. It is not a "real" record until now, so it wasn't really
            // "created" until now. It is way down here so that the activated state can be properly
            // saved before the hook is fired.
            $event = new ActiveUserPostCreatedEvent($userEntity);
        }
        if (!$adminApprovalRequired) {
            $approvedBy = $this->currentUserApi->isLoggedIn() ? $this->currentUserApi->get('uid') : $userEntity->getUid();
            $this->userRepository->setApproved($userEntity, new DateTime(), $approvedBy); // flushes EM
        }
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * Approves a registration.
     * If the registration is also verified (or does not need it) then a new users table record is created.
     */
    public function approve(UserEntity $user): void
    {
        $user->setApprovedBy((int)$this->currentUserApi->get('uid'));
        $user->setApprovedDate(new DateTime());

        $user->setActivated(UsersConstant::ACTIVATED_ACTIVE);
        $this->userRepository->persistAndFlush($user);
        $this->eventDispatcher->dispatch(new RegistrationPostApprovedEvent($user));

        $this->registerNewUser($user);
    }
}
