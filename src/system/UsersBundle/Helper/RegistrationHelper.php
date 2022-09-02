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

namespace Zikula\UsersBundle\Helper;

use DateTime;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\GroupsBundle\Entity\Group;
use Zikula\GroupsBundle\Helper\DefaultHelper;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Event\ActiveUserPostCreatedEvent;
use Zikula\UsersBundle\Event\ActiveUserPreCreatedEvent;
use Zikula\UsersBundle\Event\RegistrationPostApprovedEvent;
use Zikula\UsersBundle\Event\RegistrationPostCreatedEvent;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

class RegistrationHelper
{
    use TranslatorTrait;

    public function __construct(
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly UserRepositoryInterface $userRepository,
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DefaultHelper $defaultHelper,
        TranslatorInterface $translator,
        private readonly bool $registrationEnabled,
        private readonly bool $registrationRequiresApproval,
        private readonly ?string $registrationNotificationEmail
    ) {
        $this->setTranslator($translator);
    }

    public function isRegistrationEnabled(): bool
    {
        return $this->registrationEnabled;
    }

    public function getNotificationEmail(): ?string
    {
        return $this->registrationNotificationEmail;
    }

    /**
     * Create a new user or registration.
     */
    public function registerNewUser(User $userEntity): void
    {
        $adminApprovalRequired = $this->registrationRequiresApproval;
        if (null === $userEntity->getUid()) {
            $userEntity->setRegistrationDate(new DateTime());
        }
        $this->eventDispatcher->dispatch($createActiveUser = new ActiveUserPreCreatedEvent($userEntity));
        if (($adminApprovalRequired && !$userEntity->isApproved()) || $createActiveUser->isPropagationStopped()) {
            // We need a registration record
            $userEntity->setActivated(UsersConstant::ACTIVATED_PENDING_REG);

            // ATTENTION: Do NOT dispatch ActiveUserPostCreatedEvent at this point! The record is a pending
            // registration, not a user, so a user account record has really not yet been "created".
            // The ActiveUserPostCreatedEvent will be dispatched when the registration becomes a "real" user
            // account record. This is so that modules that do default actions on the creation
            // of a user account do not perform those actions on a pending registration, which
            // may be deleted at any point.
            $event = new RegistrationPostCreatedEvent($userEntity);
        } else {
            // Everything is in order for a full user record
            $userEntity->setActivated(UsersConstant::ACTIVATED_ACTIVE);

            // Add user to default group
            $defaultGroupId = $this->defaultHelper->getDefaultGroupId();
            if (!$userEntity->getGroups()->containsKey($defaultGroupId)) {
                /** @var Group $defaultGroupEntity */
                $defaultGroupEntity = $this->groupRepository->find($defaultGroupId);
                $userEntity->addGroup($defaultGroupEntity);
            }

            // ATTENTION: This is the proper place for the ActiveUserPostCreatedEvent, not when a pending
            // registration is created. It is not a "real" record until now, so it wasn't really
            // "created" until now. It is here so that the activated state can be properly
            // saved before the ActiveUserPostCreatedEvent is dispatched.
            $event = new ActiveUserPostCreatedEvent($userEntity);
        }
        $this->userRepository->persistAndFlush($userEntity);
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
    public function approve(User $user): void
    {
        $user->setApprovedBy((int) $this->currentUserApi->get('uid'));
        $user->setApprovedDate(new DateTime());

        $user->setActivated(UsersConstant::ACTIVATED_ACTIVE);
        $this->userRepository->persistAndFlush($user);
        $this->eventDispatcher->dispatch(new RegistrationPostApprovedEvent($user));

        $this->registerNewUser($user);
    }
}
