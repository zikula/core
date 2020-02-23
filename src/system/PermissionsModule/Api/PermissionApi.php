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

namespace Zikula\PermissionsModule\Api;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Class PermissionApi
 *
 * This class is used to determine whether a user has rights (or permissions) to a given component. Rights are granted
 * or denied from the Permissions module User Interface. Components/Extensions must declare their Permission structure in
 * their `composer.json` file.
 */
class PermissionApi implements PermissionApiInterface
{
    /**
     * 'all users', includes unregistered users
     */
    public const ALL_USERS = -1;

    /**
     * 'all groups', includes unregistered users
     */
    public const ALL_GROUPS = -1;

    /**
     * pseudo group of unregistered users.
     */
    public const UNREGISTERED_USER_GROUP = 0;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * holds the cache of group Perms by User
     * @var array
     */
    private $groupPermsByUser = [];

    public function __construct(
        PermissionRepositoryInterface $permRepository,
        UserRepositoryInterface $userRepository,
        CurrentUserApiInterface $currentUserApi,
        TranslatorInterface $translator
    ) {
        $this->permRepository = $permRepository;
        $this->userRepository = $userRepository;
        $this->currentUserApi = $currentUserApi;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission(string $component = null, string $instance = null, int $level = ACCESS_NONE, int $user = null): bool
    {
        if (!isset($user)) {
            $user = (int)$this->currentUserApi->get('uid');
        }
        $user = !$user ? Constant::USER_ID_ANONYMOUS : $user;
        if (!isset($this->groupPermsByUser[$user]) || false === $this->groupPermsByUser[$user]) {
            $this->setGroupPermsForUser($user);
        }

        return 0 === count($this->groupPermsByUser[$user])
            ? false
            : $this->getSecurityLevel($this->groupPermsByUser[$user], $component, $instance) >= $level;
    }

    /**
     * Get auth info.
     */
    private function setGroupPermsForUser(int $user): void
    {
        $uids = [self::ALL_USERS, $user]; // by default include 'all users'

        // Get all groups that user is in
        $foundUsers = $this->userRepository->findByUids($uids);
        $foundGids = [];
        /** @var UserEntity $foundUser */
        foreach ($foundUsers as $foundUser) {
            foreach ($foundUser->getGroups() as $gid => $group) {
                $foundGids[] = $gid;
            }
        }

        $defaultGids = [self::ALL_GROUPS];
        if (Constant::USER_ID_ANONYMOUS === $user) {
            $defaultGids[] = self::UNREGISTERED_USER_GROUP; // Unregistered GID
        }
        $allGroups = array_merge_recursive($defaultGids, $foundGids);

        // Get all group permissions
        $permsByGroup = $this->permRepository->getPermissionsByGroups($allGroups);

        $groupPerms = [];
        foreach ($permsByGroup as $perm) {
            $component = $this->normalizeRegexString($perm['component']);
            $instance = $this->normalizeRegexString($perm['instance']);
            $level = (int)$perm['level']; // this string must be a numeric and not normalized.
            $groupPerms[] = [
                'component' => $component,
                'instance' => $instance,
                'level' => $level
            ];
        }

        $this->groupPermsByUser[$user] = $groupPerms;
    }

    /**
     * Get security Level
     */
    private function getSecurityLevel(array $perms = [], string $component = null, string $instance = null): int
    {
        $level = ACCESS_INVALID;

        // If we get a test component or instance purely consisting of ':' signs
        // then it counts as blank
        if ($component === str_repeat(':', mb_strlen($component))) {
            $component = '';
        }
        if ($instance === str_repeat(':', mb_strlen($instance))) {
            $instance = '';
        }

        // Test for generic permission
        if (empty($component) && empty($instance)) {
            // Looking for best permission
            foreach ($perms as $perm) {
                if ($perm['level'] > $level) {
                    $level = $perm['level'];
                }
            }

            return $level;
        }

        // Test if user has ANY access to given component, without determining exact instance
        if ('ANY' === $instance) {
            $levels = [$level];
            foreach ($perms as $perm) {
                // component check
                if (!preg_match('=^' . $perm['component'] . '$=', $component)) {
                    continue; // component doesn't match.
                }

                // if component matches -  keep the level we found
                $levels[] = $perm['level'];

                // check that the instance matches :: or '' (nothing)
                if (preg_match('=^' . $perm['instance'] . '$=', '::') || preg_match('=^' . $perm['instance'] . '$=', '')) {
                    break; // instance matches - stop searching
                }
            }

            // select the highest level among found
            $level = max($levels);

            return $level;
        }

        // Test for generic instance
        // additional fixes by BMW [larsneo]
        // if the instance is empty, then we're looking for the per-module
        // permissions.
        if (empty($instance)) {
            // if $instance is empty, then there must be a component.
            // Looking for best permission
            foreach ($perms as $perm) {
                // component check
                if (!preg_match('=^' . $perm['component'] . '$=', $component)) {
                    continue; // component doesn't match.
                }

                // check that the instance matches :: or '' (nothing)
                if (!(preg_match('=^' . $perm['instance'] . '$=', '::') || preg_match('=^' . $perm['instance'] . '$=', ''))) {
                    continue; // instance does not match
                }

                // We have a match - set the level and quit
                $level = $perm['level'];
                break;
            }

            return $level;
        }

        // Normal permissions check
        // there *is* a $instance at this point.
        foreach ($perms as $perm) {
            // if there is a component, check that it matches
            if ('' !== $component && !preg_match('=^' . $perm['component'] . '$=', $component)) {
                // component exists, and does not match.
                continue;
            }

            // Confirm that instance matches
            if (!preg_match('=' . $perm['instance'] . '$=', $instance)) {
                // instance does not match
                continue;
            }

            // We have a match - set the level and quit looking
            $level = $perm['level'];
            break;
        }

        return $level;
    }

    /**
     * Fix security string.
     */
    private function normalizeRegexString(string $string): string
    {
        if (empty($string)) {
            $string = '.*';
        }
        if (0 === mb_strpos($string, ':')) {
            $string = '.*' . $string;
        }
        $string = str_replace('::', ':.*:', $string);
        if (mb_strrpos($string, ':') === mb_strlen($string) - 1) {
            $string .= '.*';
        }

        return $string;
    }

    public function accessLevelNames(int $level = null)
    {
        $accessNames = [
            ACCESS_INVALID => $this->translator->trans('Invalid'),
            ACCESS_NONE => $this->translator->trans('No access'),
            ACCESS_OVERVIEW => $this->translator->trans('Overview access'),
            ACCESS_READ => $this->translator->trans('Read access'),
            ACCESS_COMMENT => $this->translator->trans('Comment access'),
            ACCESS_MODERATE => $this->translator->trans('Moderate access'),
            ACCESS_EDIT => $this->translator->trans('Edit access'),
            ACCESS_ADD => $this->translator->trans('Add access'),
            ACCESS_DELETE => $this->translator->trans('Delete access'),
            ACCESS_ADMIN => $this->translator->trans('Admin access'),
        ];

        return isset($level) ? $accessNames[$level] : $accessNames;
    }

    public function resetPermissionsForUser(int $userId): void
    {
        if (!isset($userId)) {
            throw new InvalidArgumentException('User id must be set.');
        }
        $this->groupPermsByUser[$userId] = false;
    }

    /**
     * Get group permissions for one user.
     * (not an @api method)
     */
    public function getGroupPerms(int $userId): array
    {
        if (!isset($userId)) {
            throw new InvalidArgumentException('User must be set.');
        }

        return $this->groupPermsByUser[$userId] ?? [];
    }
}
