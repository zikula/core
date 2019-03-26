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

use Zikula\Common\Translator\TranslatorInterface;
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
    const ALL_USERS = -1;

    /**
     * 'all groups', includes unregistered users
     */
    const ALL_GROUPS = -1;

    /**
     * pseudo group of unregistered users.
     */
    const UNREGISTERED_USER_GROUP = 0;

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

    /**
     * PermissionApi constructor.
     * @param PermissionRepositoryInterface $permRepository Permission repository
     * @param UserRepositoryInterface $userRepository User repository
     * @param CurrentUserApiInterface $currentUserApi
     * @param TranslatorInterface $translator Translator service instance
     */
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
    public function hasPermission($component = null, $instance = null, $level = ACCESS_NONE, $user = null)
    {
        if (!is_numeric($level)) {
            throw new \InvalidArgumentException('Invalid security level');
        }
        if (isset($user) && !is_numeric($user)) {
            throw new \InvalidArgumentException('User argument must be an integer.');
        }
        if (!isset($user)) {
            $user = $this->currentUserApi->get('uid');
        }
        if (!isset($this->groupPermsByUser[$user]) || false === $this->groupPermsByUser[$user]) {
            $this->setGroupPermsForUser($user);
        }

        return (0 === count($this->groupPermsByUser[$user]))
            ? false
            : $this->getSecurityLevel($this->groupPermsByUser[$user], $component, $instance) >= $level;
    }

    /**
     * Get auth info.
     *
     * @param integer $user User Id
     */
    private function setGroupPermsForUser($user)
    {
        $user = !$user ? Constant::USER_ID_ANONYMOUS : (int)$user; // convert possible boolean to integer, ensure non-bool is also integer
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
            $groupPerms[] = ['component' => $component, 'instance' => $instance, 'level' => $level];
        }

        $this->groupPermsByUser[$user] = $groupPerms;
    }

    /**
     * Get security Level
     *
     * @param array $perms Array of permissions
     * @param string $component Component
     * @param string $instance Instance
     *
     * @return integer Matching security level
     */
    private function getSecurityLevel($perms, $component, $instance)
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
        if ((empty($component)) && (empty($instance))) {
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
                if (!preg_match("=^{$perm[component]}$=", $component)) {
                    continue; // component doestn't match.
                }

                // if component matches -  keep the level we found
                $levels[] = $perm['level'];

                // check that the instance matches :: or '' (nothing)
                if ((preg_match("=^{$perm[instance]}$=", '::') || preg_match("=^{$perm[instance]}$=", ''))) {
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
                if (!preg_match("=^{$perm[component]}$=", $component)) {
                    continue; // component doestn't match.
                }

                // check that the instance matches :: or '' (nothing)
                if (!(preg_match("=^{$perm[instance]}$=", '::') || preg_match("=^{$perm[instance]}$=", ''))) {
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
            if (('' !== $component) && (!preg_match("=^{$perm[component]}$=", $component))) {
                // component exists, and does not match.
                continue;
            }

            // Confirm that instance matches
            if (!preg_match("=^{$perm[instance]}$=", $instance)) {
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
     *
     * @param string $string String
     *
     * @return string
     */
    private function normalizeRegexString($string)
    {
        if (empty($string)) {
            $string = '.*';
        }
        if (0 === mb_strpos($string, ':')) {
            $string = '.*' . $string;
        }
        $string = str_replace('::', ':.*:', $string);
        if (mb_strrpos($string, ':') === mb_strlen($string) - 1) {
            $string = $string . '.*';
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function accessLevelNames($level = null)
    {
        if (isset($level) && !is_numeric($level)) {
            throw new \InvalidArgumentException();
        } elseif (isset($level)) {
            $level = (int) $level;
        }

        $accessNames = [
            ACCESS_INVALID => $this->translator->__('Invalid'),
            ACCESS_NONE => $this->translator->__('No access'),
            ACCESS_OVERVIEW => $this->translator->__('Overview access'),
            ACCESS_READ => $this->translator->__('Read access'),
            ACCESS_COMMENT => $this->translator->__('Comment access'),
            ACCESS_MODERATE => $this->translator->__('Moderate access'),
            ACCESS_EDIT => $this->translator->__('Edit access'),
            ACCESS_ADD => $this->translator->__('Add access'),
            ACCESS_DELETE => $this->translator->__('Delete access'),
            ACCESS_ADMIN => $this->translator->__('Admin access'),
        ];

        return isset($level) ? $accessNames[$level] : $accessNames;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPermissionsForUser($uid)
    {
        if (!is_numeric($uid)) {
            throw new \InvalidArgumentException();
        }
        $this->groupPermsByUser[$uid] = false;
    }

    /**
     * Get group permissions for one user.
     * (not an @api method)
     *
     * @param null $user
     * @return array|null
     */
    public function getGroupPerms($user)
    {
        if (!isset($user)) {
            throw new \InvalidArgumentException('User must be set.');
        }

        return (isset($this->groupPermsByUser[$user]))
            ? $this->groupPermsByUser[$user]
            : [];
    }
}
