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

namespace Zikula\ZAuthModule\Api\ApiInterface;

use Symfony\Component\Validator\ConstraintViolationList;

interface CreateUsersApiInterface
{
    /**
     * Create a ZAuth user from an array.
     *
     * @param array $userArray
     *      required keys:
     *          uname (string)
     *          pass (string)
     *          email (string)
     *      allowed keys:
     *          activated (int: 0|1 default: 1)
     *          sendmail (int: 0|1 default: 1)
     *          groups (a list of int gid separated by |, defaults to Users group)
     *              does not fail on non-existent groups
     */
    public function createUser(array $userArray): void;

    /**
     * Create multiple ZAuth users from an array of arrays.
     *
     * @param array $users array of arrays, each user with keys in createUser method
     *
     * @return array array of errors (if any)
     */
    public function createUsers(array $users): array;

    /**
     * Validate an array of user data.
     *
     * @param array $user same keys as createUser method
     *
     * @return string|bool The first error text or true
     */
    public function isValidUserData(array $user);

    /**
     * Validate and array of user data arrays.
     *
     * @param array $userArrays array of arrays, each user with keys as in createUser method
     *
     * @return ConstraintViolationList|bool an ConstraintViolationList of errors or true
     */
    public function isValidUserDataArray(array $userArrays);

    /**
     * Persist all created users and mappings
     *
     * @return void
     */
    public function persist(): void;

    /**
     * Get the created users. The persisted state is unknown.
     *
     * @return array
     */
    public function getCreatedUsers(): array;

    /**
     * Get the created mappings. The persisted state is unknown.
     *
     * @return array
     */
    public function getCreatedMappings(): array;

    /**
     * Clear all the created UserEntities and AuthenticationMappingEntities that have been created.
     */
    public function clearCreated(): void;
}
